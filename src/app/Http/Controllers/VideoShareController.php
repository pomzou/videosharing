<?php

namespace App\Http\Controllers;

use App\Models\VideoFile;
use App\Models\VideoShare;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoShareController extends Controller
{
    public function share(Request $request, VideoFile $videoFile)
    {
        // 所有者チェック
        if ($videoFile->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'email' => 'required|email',
            'expires_at' => 'required|date|after:now'
        ]);

        // 既存の共有設定を確認
        $existingShare = $videoFile->shares()
            ->where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if ($existingShare) {
            return response()->json([
                'message' => 'Already shared with this email',
                'share' => $existingShare
            ]);
        }

        // 新しい共有設定を作成
        $share = $videoFile->shares()->create([
            'email' => $request->email,
            'access_token' => Str::random(32),
            'expires_at' => $request->expires_at,
            'is_active' => true
        ]);

        // ここで後ほどメール通知を実装

        return response()->json([
            'message' => 'Video shared successfully',
            'share' => $share
        ]);
    }

    public function revokeAccess(VideoShare $share)
    {
        // 所有者チェック
        if ($share->videoFile->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $share->update(['is_active' => false]);

        return response()->json([
            'message' => 'Access revoked successfully'
        ]);
    }

    public function accessVideo(string $token)
    {
        $share = VideoShare::where('access_token', $token)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // アクセスログを記録
        AccessLog::create([
            'video_file_id' => $share->video_file_id,
            'video_share_id' => $share->id,
            'access_email' => $share->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'action' => 'view'
        ]);

        return view('videos.shared', [
            'video' => $share->videoFile,
            'share' => $share
        ]);
    }

    public function listShares(VideoFile $videoFile)
    {
        if ($videoFile->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $shares = $videoFile->shares()
            ->with('accessLogs')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['shares' => $shares]);
    }
}
