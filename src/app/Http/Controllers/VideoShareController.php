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
        // 所有者チェック
        if ($videoFile->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // 有効期限をパース
            $expiresAt = new \DateTime($request->expires_at);
            $now = new \DateTime();
            $hoursDiff = ($expiresAt->getTimestamp() - $now->getTimestamp()) / 3600;

            // 有効期限のチェック（最大7日間）
            if ($hoursDiff > 168) {
                throw new \Exception('The expiration time cannot exceed 7 days');
            }

            // ユーザー情報をロード
            $videoFile->load('user');

            // 署名付きURLを生成
            $signedUrl = $videoFile->generateSignedUrl($request->expires_at);

            // 新しい共有設定を作成
            $share = $videoFile->shares()->create([
                'email' => $request->email,
                'access_token' => Str::random(32),
                'expires_at' => $request->expires_at,
                'is_active' => true,
                'share_type' => 'email'  // メール共有として作成
            ]);

            // リレーションをロード
            $share->load(['videoFile.user']);

            // デバッグログ
            Log::info('Share created with relations', [
                'share_id' => $share->id,
                'video_file_id' => $videoFile->id,
                'user_name' => $videoFile->user->name ?? 'Not loaded',
                'relations' => [
                    'has_video_file' => $share->videoFile !== null,
                    'has_user' => $share->videoFile?->user !== null
                ]
            ]);

            // URLを保存
            $videoFile->update([
                'current_signed_url' => $signedUrl,
                'url_expires_at' => $request->expires_at
            ]);

            // アクセスログに記録
            $share->accessLogs()->create([
                'video_file_id' => $videoFile->id,
                'access_email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'action' => 'share_created'
            ]);

            DB::commit();

            // トランザクション外でメール送信を試行
            try {
                Mail::to($request->email)->send(new VideoShared($share, $signedUrl));
            } catch (\Exception $e) {
                Log::error('Failed to send email', [
                    'share_id' => $share->id,
                    'error' => $e->getMessage()
                ]);
                // メール送信失敗でも共有自体は成功とする
                return response()->json([
                    'message' => 'Video shared successfully but email notification failed',
                    'share' => $share,
                    'warning' => 'Email notification could not be sent'
                ]);
            }

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
        try {
            // 所有者チェック
            if ($share->videoFile->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            DB::beginTransaction();

            // 共有設定を無効化
            $share->update(['is_active' => false]);

            // アクセスログに記録
            $share->accessLogs()->create([
                'video_file_id' => $share->video_file_id,
                'access_email' => $share->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'action' => 'access_revoked'
            ]);

            DB::commit();

            Log::info('Access revoked successfully', [
                'share_id' => $share->id,
                'video_id' => $share->video_file_id,
                'email' => $share->email
            ]);

            return response()->json([
                'message' => 'Access revoked successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to revoke access', [
                'share_id' => $share->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to revoke access',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function accessVideo(string $token)
    {
        $share = VideoShare::where('access_token', $token)->firstOrFail();

        // アクティブでない、または期限切れの場合は403
        if (!$share->is_active || $share->isExpired()) {
            abort(403, 'This share link is no longer active');
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
