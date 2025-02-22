<?php

namespace App\Http\Controllers;

use App\Models\VideoFile;
use App\Models\VideoShare;
use App\Models\AccessLog;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ShareVideoRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VideoShared;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VideoShareController extends Controller
{
    public function confirmShare(ShareVideoRequest $request, VideoFile $videoFile)
    {
        // 所有者チェック
        if ($videoFile->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 確認トークンを生成
        $confirmationToken = $request->generateConfirmationToken();

        return response()->json([
            'message' => 'Please confirm the email address',
            'confirmation_token' => $confirmationToken,
            'email' => $request->email,
            'expires_at' => $request->expires_at,
            'video' => [
                'title' => $videoFile->title,
                'file_name' => $videoFile->original_name,
                'file_size' => $videoFile->file_size
            ]
        ]);
    }

    public function share(ShareVideoRequest $request, VideoFile $videoFile)
    {
        if ($videoFile->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // 有効期限チェック
            $expiresAt = new \DateTime($request->expires_at);
            $now = new \DateTime();
            $hoursDiff = ($expiresAt->getTimestamp() - $now->getTimestamp()) / 3600;

            if ($hoursDiff > 168) {
                throw new \Exception('The expiration time cannot exceed 7 days');
            }

            // 期限切れチェック
            if ($request->expires_at && $request->expires_at < now()) {
                throw new \Exception('The expiration time must be in the future.');
            }

            // 既存の共有情報を確認
            $videoFile->load('user');
            $existingShare = $videoFile->shares()->where('is_active', true)->first();
            if ($existingShare && $existingShare->isExpired()) {
                return response()->json(['error' => 'Cannot share an expired link.'], 403);
            }

            // 新しい共有リンクを作成 (URL生成時に有効期限を考慮)
            if ($request->expires_at && $request->expires_at < now()) {
                throw new \Exception('Cannot create a share link with an expired expiration date.');
            }

            // 新しい共有リンクを作成
            $share = $videoFile->shares()->create([
                'email' => $request->email,
                'access_token' => Str::random(32),
                'expires_at' => $request->expires_at,
                'is_active' => true,
                'share_type' => 'email',
                'shared_url' => $videoFile->generateSignedUrl($request->expires_at) // Store the signed URL in shared_url
            ]);

            DB::commit();

            // メール送信
            try {
                Mail::to($request->email)->send(new VideoShared($share, $share->active_shared_url));
            } catch (\Exception $e) {
                Log::error('Failed to send email', [
                    'share_id' => $share->id,
                    'error' => $e->getMessage()
                ]);
            }

            // 共有リストを最新の状態で取得
            $shares = $videoFile->shares()
                ->with('accessLogs')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'Video shared successfully',
                'shares' => $shares,
                'shares_count' => $shares->count()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to share video', [
                'video_id' => $videoFile->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to share video',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function accessVideo(string $token)
    {
        $share = VideoShare::where('access_token', $token)->firstOrFail();

        // 有効期限をチェック
        if ($share->expires_at && $share->expires_at < now()) {
            abort(404, 'The URL for this video has expired.');
        }

        // アクティブでない、または期限切れ、またはis_activeが0の場合は403
        if (!$share->is_active || $share->isExpired() || $share->is_active === 0) {
            abort(403, 'This share link has expired or is no longer active');
        }

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
            ->get()
            ->map(function ($share) {
                $share->status = $share->isEmailShare()
                    ? (!$share->is_active || $share->isExpired() ? 'Expired' : 'Active')
                    : null;
                return $share;
            });

        return response()->json(['shares' => $shares]);
    }
}
