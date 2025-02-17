<?php

namespace App\Http\Controllers;

use App\Models\VideoFile;
use App\Models\VideoShare;
use App\Models\AccessLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VideoShared;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VideoShareController extends Controller
{
    public function share(Request $request, VideoFile $videoFile)
    {
        try {
            // 所有者チェック
            if ($videoFile->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $request->validate([
                'email' => 'required|email',
                'expires_at' => 'required|date|after:now'
            ]);

            // トランザクション開始
            DB::beginTransaction();

            try {
                // 既存の共有設定を確認
                $existingShare = $videoFile->shares()
                    ->where('email', $request->email)
                    ->where('is_active', true)
                    ->first();

                if ($existingShare) {
                    DB::rollBack();
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

                // メール送信は別のジョブで処理することを推奨
                // Mail::to($request->email)->send(new VideoShared($share));

                DB::commit();

                Log::info('Video shared successfully', [
                    'video_id' => $videoFile->id,
                    'share_id' => $share->id,
                    'email' => $request->email
                ]);

                return response()->json([
                    'message' => 'Video shared successfully',
                    'share' => $share
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to share video', [
                'video_id' => $videoFile->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to share video',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function revokeAccess(VideoShare $share)
    {
        // 所有者チェック
        if ($share->videoFile->user_id !== Auth::id()) {
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
        if ($videoFile->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $shares = $videoFile->shares()
            ->with('accessLogs')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['shares' => $shares]);
    }
}
