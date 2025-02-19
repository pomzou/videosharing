<?php

namespace App\Http\Controllers;

use App\Models\VideoFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VideoFileController extends Controller
{
    public function create()
    {
        return view('videos.create');
    }

    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video' => [
                'required',
                'file',
                'max:102400', // 100MB制限
                'mimes:' . implode(',', [
                    // 動画
                    'mp4',
                    'avi',
                    'mov',
                    'mkv',
                    'webm',
                    'flv',
                    // 画像
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                    'bmp',
                    'tiff',
                    'webp',
                    // 音声
                    'mp3',
                    'wav',
                    'ogg',
                    'flac',
                    // 文書
                    'pdf',
                    'doc',
                    'docx',
                    'xls',
                    'xlsx',
                    'ppt',
                    'pptx',
                    'odt',
                    'ods',
                    'rtf',
                    // 圧縮ファイル
                    'zip',
                    'rar',
                    'tar',
                    'gz',
                    '7z',
                    // コード・スクリプト
                    'html',
                    'css',
                    'js',
                    'php',
                    'py',
                    'java',
                    'cpp',
                    // テキスト
                    'txt',
                    'csv'
                ])
            ],
            'privacy' => 'required|in:public,private',
        ]);

        try {
            // ファイルの取得
            $file = $request->file('video');  // 'video' から変更が必要な場合は、フォームのname属性も変更する必要があります
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . $file->hashName();

            // S3にアップロード
            $s3Path = 'files/' . $fileName;  // 'videos' から 'files' に変更して汎用的に
            Storage::disk('s3')->putFileAs(
                dirname($s3Path),
                $file,
                basename($s3Path)
            );

            // データベースに保存
            $videoFile = VideoFile::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'file_name' => $fileName,
                'original_name' => $originalName,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                's3_path' => $s3Path,
                'privacy' => $request->privacy,
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'File uploaded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload file. ' . $e->getMessage());
        }
    }

    public function generateSignedUrl(Request $request, VideoFile $videoFile)
    {
        if ($videoFile->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'expires_at' => [
                    'required',
                    'date',
                    'after:now',
                    function ($attribute, $value, $fail) {
                        $hours = now()->diffInHours(new \DateTime($value));
                        if ($hours > 168) { // 7日間 = 168時間
                            $fail('The expiry time cannot exceed 7 days.');
                        }
                    },
                ]
            ]);

            // 日本時間で期限を設定
            $expiresAt = new \DateTime($request->expires_at, new \DateTimeZone('Asia/Tokyo'));
            $hours = now()->diffInHours($expiresAt);

            $credentials = [
                'version' => 'latest',
                'region'  => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ];

            // 12時間以上の場合はIAM認証情報を使用
            if ($hours >= 12) {
                if (!config('filesystems.disks.s3.key') || !config('filesystems.disks.s3.secret')) {
                    throw new \Exception('IAM credentials are required for URLs valid longer than 12 hours.');
                }
            }

            $s3Client = new S3Client($credentials);

            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $videoFile->s3_path
            ]);

            $request = $s3Client->createPresignedRequest($cmd, $request->expires_at);
            $signedUrl = (string) $request->getUri();

            $videoFile->update([
                'url_expires_at' => $expiresAt,
                'current_signed_url' => $signedUrl
            ]);

            Log::info('Signed URL generated', [
                'video_id' => $videoFile->id,
                'expires_in_hours' => $hours
            ]);

            return response()->json([
                'url' => $signedUrl,
                'expires_at' => $expiresAt->format('c')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate signed URL', [
                'video_id' => $videoFile->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $videos = VideoFile::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($videos as $video) {
                try {
                    $s3Client = new S3Client([
                        'version' => 'latest',
                        'region'  => config('filesystems.disks.s3.region'),
                        'credentials' => [
                            'key'    => config('filesystems.disks.s3.key'),
                            'secret' => config('filesystems.disks.s3.secret'),
                        ],
                    ]);

                    $cmd = $s3Client->getCommand('GetObject', [
                        'Bucket' => config('filesystems.disks.s3.bucket'),
                        'Key'    => $video->s3_path
                    ]);

                    // 所有者は常にプレビュー可能
                    if ($video->isOwner() && in_array($video->getFileType(), ['video', 'image', 'audio', 'pdf', 'text'])) {
                        $video->preview_url = (string) $s3Client->createPresignedRequest($cmd, '+1 hour')->getUri();
                    }

                    // 共有URLは期限切れチェック
                    if ($video->url_expires_at && $video->url_expires_at->isPast()) {
                        $video->current_signed_url = null;
                    }
                } catch (\Exception $e) {
                    Log::error('Error generating URL', [
                        'video_id' => $video->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return view('videos.index', compact('videos'));
        } catch (\Exception $e) {
            Log::error('Error in video index', [
                'error' => $e->getMessage()
            ]);
            return view('videos.index')->with('error', 'Failed to load videos');
        }
    }

    private function refreshSignedUrl($video)
    {
        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ]);

            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $video->s3_path
            ]);

            $request = $s3Client->createPresignedRequest($cmd, '+24 hours');
            $video->current_signed_url = (string) $request->getUri();

            return $video;
        } catch (\Exception $e) {
            $log = Log::error('Failed to refresh signed URL', ['error' => $e->getMessage()]);
            return $video;
        }
    }

    public function destroy(VideoFile $videoFile)
    {
        try {
            // 所有者チェック
            if ($videoFile->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // トランザクション開始
            DB::beginTransaction();

            try {
                // 関連する共有情報とアクセスログを削除
                $videoFile->shares()->delete();
                $videoFile->accessLogs()->delete();

                // S3から動画を削除
                Storage::disk('s3')->delete($videoFile->s3_path);

                // データベースからレコードを削除
                $videoFile->delete();

                DB::commit();

                return response()->json([
                    'message' => 'Video deleted successfully'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            $log = Log::error('Failed to delete video', [
                'video_id' => $videoFile->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to delete video'], 500);
        }
    }

    public function revokeUrl(VideoFile $videoFile)
    {
        try {
            if ($videoFile->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            DB::beginTransaction();
            try {
                $videoFile->update([
                    'url_expires_at' => null,
                    'current_signed_url' => null
                ]);
                DB::commit();

                Log::info('URL access revoked', [
                    'video_id' => $videoFile->id
                ]);

                return response()->json([
                    'message' => 'Access revoked successfully',
                    'status' => 'success'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to revoke URL access', [
                'video_id' => $videoFile->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to revoke access'], 500);
        }
    }
}
