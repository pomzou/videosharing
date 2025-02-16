<?php

namespace App\Http\Controllers;

use App\Models\VideoFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Aws\S3\S3Client;

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
}
