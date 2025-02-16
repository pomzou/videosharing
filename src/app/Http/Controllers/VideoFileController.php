<?php

namespace App\Http\Controllers;

use App\Models\VideoFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;

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
            'video' => 'required|file|mimes:mp4,avi,mov|max:102400', // 100MB制限
            'privacy' => 'required|in:public,private',
        ]);

        try {
            // ファイルの取得
            $file = $request->file('video');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . $file->hashName();

            // S3にアップロード
            $s3Path = 'videos/' . $fileName;
            Storage::disk('s3')->put($s3Path, file_get_contents($file));

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
                ->with('success', 'Video uploaded successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload video. ' . $e->getMessage());
        }
    }

    public function generateSignedUrl(VideoFile $videoFile)
    {
        if ($videoFile->user_id !== Auth::id() && $videoFile->privacy === 'private') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ]);

            $expiresAt = now()->addHours(24);

            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $videoFile->s3_path
            ]);

            $request = $s3Client->createPresignedRequest($cmd, '+24 hours');
            $signedUrl = (string) $request->getUri();

            $videoFile->update([
                'url_expires_at' => $expiresAt
            ]);

            return response()->json([
                'url' => $signedUrl,
                'expires_at' => $expiresAt->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index()
    {
        $videos = VideoFile::where('user_id', Auth::id())
                          ->orderBy('created_at', 'desc')
                          ->get()
                          ->map(function ($video) {
                              // 有効な署名付きURLがある場合は更新
                              if ($video->url_expires_at && now()->lt($video->url_expires_at)) {
                                  $this->refreshSignedUrl($video);
                              }
                              return $video;
                          });

        return view('videos.index', compact('videos'));
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
}
