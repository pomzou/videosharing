<?php

namespace App\Console\Commands;

use App\Models\VideoFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;

class CleanupStorageFiles extends Command
{
    protected $signature = 'storage:cleanup
                          {--dry-run : Show what would be deleted without actually deleting}
                          {--batch=100 : Number of files to process in each batch}';

    protected $description = 'Clean up unused files from storage';

    private $s3Client;
    private $bucket;

    public function __construct()
    {
        parent::__construct();

        // S3クライアントの初期化
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => config('filesystems.disks.s3.region'),
            'credentials' => [
                'key'    => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
        $this->bucket = config('filesystems.disks.s3.bucket');
    }

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $batchSize = $this->option('batch');

        $this->info('Starting storage cleanup...');
        if ($isDryRun) {
            $this->info('DRY RUN MODE - No files will be deleted');
        }

        try {
            // S3上のすべてのファイルを取得
            $s3Files = $this->listS3Files();
            $this->info(sprintf('Found %d files in S3', count($s3Files)));

            // データベースに登録されているファイルパスを取得
            $dbFiles = VideoFile::pluck('s3_path')->toArray();
            $this->info(sprintf('Found %d files in database', count($dbFiles)));

            // 未使用ファイルを特定
            $unusedFiles = array_diff($s3Files, $dbFiles);
            $this->info(sprintf('Found %d unused files', count($unusedFiles)));

            if (empty($unusedFiles)) {
                $this->info('No unused files to clean up');
                return 0;
            }

            // 削除対象ファイルの詳細をログに記録
            $logPath = storage_path('logs/storage_cleanup_' . now()->format('Y-m-d') . '.log');
            file_put_contents($logPath, "Unused files:\n" . implode("\n", $unusedFiles) . "\n");

            // バッチ処理で削除
            $chunks = array_chunk($unusedFiles, $batchSize);
            $deletedCount = 0;
            $failedCount = 0;

            foreach ($chunks as $chunk) {
                if (!$isDryRun) {
                    try {
                        // S3から削除
                        $objects = array_map(function ($key) {
                            return ['Key' => $key];
                        }, $chunk);

                        $this->s3Client->deleteObjects([
                            'Bucket' => $this->bucket,
                            'Delete' => [
                                'Objects' => $objects,
                                'Quiet' => true
                            ]
                        ]);

                        $deletedCount += count($chunk);
                        $this->info(sprintf('Deleted %d files...', $deletedCount));
                    } catch (\Exception $e) {
                        $failedCount += count($chunk);
                        Log::error('Failed to delete files', [
                            'error' => $e->getMessage(),
                            'files' => $chunk
                        ]);
                    }
                } else {
                    // Dry run mode
                    $this->info('Would delete: ' . implode(', ', $chunk));
                    $deletedCount += count($chunk);
                }
            }

            // 結果をログに記録
            Log::info('Storage cleanup completed', [
                'total_s3_files' => count($s3Files),
                'total_db_files' => count($dbFiles),
                'unused_files' => count($unusedFiles),
                'deleted_files' => $deletedCount,
                'failed_files' => $failedCount,
                'dry_run' => $isDryRun
            ]);

            $this->info('Cleanup completed successfully.');
            $this->info(sprintf('Total files processed: %d', count($unusedFiles)));
            $this->info(sprintf('Files deleted: %d', $deletedCount));
            if ($failedCount > 0) {
                $this->warn(sprintf('Failed to delete %d files', $failedCount));
            }
            $this->info(sprintf('Details logged to: %s', $logPath));

            return 0;
        } catch (\Exception $e) {
            Log::error('Failed to cleanup storage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error('Failed to cleanup storage: ' . $e->getMessage());
            return 1;
        }
    }

    private function listS3Files(): array
    {
        $files = [];
        $params = ['Bucket' => $this->bucket];

        do {
            $result = $this->s3Client->listObjectsV2($params);

            foreach ($result['Contents'] ?? [] as $object) {
                $files[] = $object['Key'];
            }

            $params['ContinuationToken'] = $result['NextContinuationToken'] ?? null;
        } while ($params['ContinuationToken']);

        return $files;
    }
}
