<?php

namespace App\Console\Commands;

use App\Models\AccessLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupAccessLogs extends Command
{
    protected $signature = 'logs:cleanup
                          {--days=30 : Number of days to keep logs}
                          {--batch=1000 : Number of records to delete in each batch}
                          {--archive : Archive logs before deletion}';

    protected $description = 'Clean up old access logs';

    public function handle()
    {
        $days = $this->option('days');
        $batchSize = $this->option('batch');
        $shouldArchive = $this->option('archive');
        $cutoffDate = now()->subDays($days);

        $this->info("Starting cleanup of access logs older than {$days} days...");

        try {
            // トランザクション開始
            DB::beginTransaction();

            // 削除対象のレコード数を取得
            $totalCount = AccessLog::where('created_at', '<', $cutoffDate)->count();

            if ($totalCount === 0) {
                $this->info('No logs to clean up.');
                return 0;
            }

            $this->info("Found {$totalCount} logs to clean up.");

            // アーカイブが必要な場合
            if ($shouldArchive) {
                $this->info('Archiving logs...');
                $archiveDate = now()->format('Y-m-d');
                $archivePath = storage_path("logs/access_logs_{$archiveDate}.csv");

                // 削除対象のログをCSVにエクスポート
                $query = AccessLog::where('created_at', '<', $cutoffDate)
                    ->select([
                        'id',
                        'video_file_id',
                        'video_share_id',
                        'access_email',
                        'ip_address',
                        'user_agent',
                        'action',
                        'created_at',
                        'updated_at'
                    ]);

                // CSVヘッダーを書き込み
                $headers = [
                    'id',
                    'video_file_id',
                    'video_share_id',
                    'access_email',
                    'ip_address',
                    'user_agent',
                    'action',
                    'created_at',
                    'updated_at'
                ];
                file_put_contents($archivePath, implode(',', $headers) . "\n");

                // バッチ処理でCSVに書き込み
                $query->chunk($batchSize, function ($logs) use ($archivePath) {
                    $data = $logs->map(function ($log) {
                        return implode(',', [
                            $log->id,
                            $log->video_file_id,
                            $log->video_share_id ?? 'NULL',
                            $log->access_email ?? 'NULL',
                            $log->ip_address ?? 'NULL',
                            str_replace(',', ' ', $log->user_agent) ?? 'NULL',
                            $log->action,
                            $log->created_at,
                            $log->updated_at
                        ]);
                    })->implode("\n");
                    file_put_contents($archivePath, $data . "\n", FILE_APPEND);
                });

                $this->info("Logs archived to: {$archivePath}");
            }

            // バッチ処理で古いログを削除
            $deletedCount = 0;
            do {
                $affected = AccessLog::where('created_at', '<', $cutoffDate)
                    ->limit($batchSize)
                    ->delete();

                $deletedCount += $affected;
                $this->info("Deleted {$deletedCount} of {$totalCount} logs...");
            } while ($affected > 0);

            DB::commit();

            // ログに記録
            Log::info('Access logs cleanup completed', [
                'deleted_count' => $deletedCount,
                'days_kept' => $days,
                'archive_created' => $shouldArchive
            ]);

            $this->info('Cleanup completed successfully.');
            $this->info("Total deleted records: {$deletedCount}");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cleanup access logs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error('Failed to cleanup logs: ' . $e->getMessage());
            return 1;
        }
    }
}
