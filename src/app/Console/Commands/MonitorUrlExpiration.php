<?php

namespace App\Console\Commands;

use App\Mail\UrlExpirationNotification;
use App\Models\VideoFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MonitorUrlExpiration extends Command
{
    protected $signature = 'urls:monitor
                          {--warning-hours=24 : Hours before expiration to send warning}';

    protected $description = 'Monitor URL expiration and send notifications';

    public function handle()
    {
        $warningHours = $this->option('warning-hours');
        $this->info('Starting URL expiration monitoring...');

        try {
            DB::beginTransaction();

            // 期限切れURLの処理
            $expiredFiles = VideoFile::where('url_expires_at', '<=', now())
                ->where('current_signed_url', '!=', null)
                ->get();

            foreach ($expiredFiles as $file) {
                try {
                    // URLを無効化
                    $file->update([
                        'current_signed_url' => null
                    ]);

                    // 所有者に通知
                    if ($file->user) {
                        Mail::to($file->user->email)
                            ->send(new UrlExpirationNotification($file, 0, true));
                    }

                    $this->info("Expired URL disabled for file: {$file->title}");
                    Log::info('URL expired and disabled', [
                        'file_id' => $file->id,
                        'title' => $file->title,
                        'user_id' => $file->user_id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to process expired URL', [
                        'file_id' => $file->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // 期限切れ間近のURLを検出
            $warningTime = now()->addHours($warningHours);
            $expiringFiles = VideoFile::where('url_expires_at', '<=', $warningTime)
                ->where('url_expires_at', '>', now())
                ->where('current_signed_url', '!=', null)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('access_logs')
                        ->whereColumn('video_file_id', 'video_files.id')
                        ->where('action', 'expiration_warning')
                        ->where('created_at', '>=', now()->subHours(24));
                })
                ->get();

            foreach ($expiringFiles as $file) {
                try {
                    $hoursRemaining = now()->diffInHours($file->url_expires_at);

                    // 所有者に通知
                    if ($file->user) {
                        Mail::to($file->user->email)
                            ->send(new UrlExpirationNotification($file, $hoursRemaining, false));
                    }

                    // 警告通知を記録
                    $file->accessLogs()->create([
                        'action' => 'expiration_warning',
                        'access_email' => $file->user->email
                    ]);

                    $this->info("Expiration warning sent for file: {$file->title}");
                    Log::info('Expiration warning sent', [
                        'file_id' => $file->id,
                        'title' => $file->title,
                        'hours_remaining' => $hoursRemaining
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send expiration warning', [
                        'file_id' => $file->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            $this->info('URL monitoring completed successfully.');
            $this->info("Processed {$expiredFiles->count()} expired URLs");
            $this->info("Sent {$expiringFiles->count()} expiration warnings");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to monitor URLs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error('Failed to monitor URLs: ' . $e->getMessage());
            return 1;
        }
    }
}
