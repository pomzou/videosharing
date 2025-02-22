<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VideoShare;
use Illuminate\Support\Facades\Log;

class ExpireVideoShares extends Command
{
    protected $signature = 'videos:expire-shares';
    protected $description = 'Mark or delete expired video shares';

    public function handle()
    {
        $expiredShares = VideoShare::where('expires_at', '<', now())
            ->where('is_active', true)
            ->get();

        foreach ($expiredShares as $share) {
            // Mark the share as inactive
            $share->is_active = false;
            $share->save();

            // Log the expiration
            Log::info('Expired video share marked as inactive', [
                'share_id' => $share->id,
                'video_file_id' => $share->video_file_id,
                'expires_at' => $share->expires_at,
            ]);
        }

        $this->info('Expired video shares have been processed.');
    }
}
