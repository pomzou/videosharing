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
use App\Services\UrlShortenerService;

class VideoShareController extends Controller
{

    private $urlShortener;

    public function __construct(UrlShortenerService $urlShortener)
    {
        $this->urlShortener = $urlShortener;
    }

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

            // S3の署名付きURLを生成
            $signedUrl = $videoFile->generateSignedUrl($request->expires_at);

            // 短縮URLを生成
            $shortUrl = $this->urlShortener->generateShortUrl();

            // 共有リンクを作成
            $share = $videoFile->shares()->create([
                'email' => $request->email,
                'access_token' => Str::random(32),
                'expires_at' => $request->expires_at,
                'is_active' => true,
                'share_type' => 'email',
                'shared_url' => $signedUrl,
                'short_url' => $shortUrl
            ]);

            DB::commit();

            // 共有情報を最新の状態に更新
            $share->refresh();

            // メール送信時に使用するURLを生成
            $streamUrl = route('stream.video', ['shortUrl' => $share->short_url]);

            // メール送信
            try {
                $streamUrl = route('stream.video', ['shortUrl' => $share->short_url]);

                Log::info('Sending email with stream URL', [
                    'share_id' => $share->id,
                    'email' => $request->email,
                    'short_url' => $share->short_url,
                    'stream_url' => $streamUrl
                ]);

                Mail::to($request->email)->send(new VideoShared($share));

                Log::info('Email sent successfully', [
                    'share_id' => $share->id,
                    'email' => $request->email
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send email', [
                    'share_id' => $share->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
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

    public function revokeAccess(VideoShare $share)
    {
        try {
            // 所有者チェック
            if ($share->videoFile->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            DB::beginTransaction();
            try {
                // 共有を無効化
                $share->update([
                    'is_active' => false,
                    'shared_url' => null,
                    'short_url' => null
                ]);

                // アクセスログに記録
                $share->accessLogs()->create([
                    'video_file_id' => $share->video_file_id,
                    'access_email' => $share->email,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'action' => 'revoke'
                ]);

                DB::commit();

                Log::info('Share access revoked', [
                    'share_id' => $share->id,
                    'video_file_id' => $share->video_file_id,
                    'email' => $share->email
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
            Log::error('Failed to revoke share access', [
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
