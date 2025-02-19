<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 毎日深夜1時にアクセスログのクリーンアップを実行
        // --archive オプションを付けて実行し、削除前にCSVにエクスポート
        $schedule->command('logs:cleanup --archive')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/cleanup.log'));

        // 毎週日曜日の深夜2時にストレージのクリーンアップを実行
        // --dry-run オプションを付けて、削除前に確認用のログを生成
        $schedule->command('storage:cleanup --dry-run')
            ->weeklyOn(0, '02:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/storage_cleanup.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
