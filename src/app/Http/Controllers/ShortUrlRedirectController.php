<?php

namespace App\Http\Controllers;

use App\Models\VideoFile;
use App\Models\VideoShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShortUrlRedirectController extends Controller
{
    /**
     * 短縮URLから元のURLにリダイレクトする
     *
     * @param string $shortUrl
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(string $shortUrl)
    {
        try {
            // VideoFilesテーブルで検索
            $videoFile = VideoFile::where('short_url', $shortUrl)
                ->where(function ($query) {
                    $query->whereNull('url_expires_at')
                        ->orWhere('url_expires_at', '>', now());
                })
                ->first();

            if ($videoFile && $videoFile->current_signed_url) {
                // アクセスログを記録
                Log::info('Redirecting to video file URL', [
                    'short_url' => $shortUrl,
                    'video_file_id' => $videoFile->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return redirect()->away($videoFile->current_signed_url);
            }

            // VideoSharesテーブルで検索
            $videoShare = VideoShare::where('short_url', $shortUrl)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($videoShare && $videoShare->shared_url) {
                // アクセスログを記録
                Log::info('Redirecting to shared video URL', [
                    'short_url' => $shortUrl,
                    'video_share_id' => $videoShare->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                // アクセスログをデータベースに記録
                $videoShare->accessLogs()->create([
                    'video_file_id' => $videoShare->video_file_id,
                    'access_email' => $videoShare->email,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'action' => 'redirect'
                ]);

                return redirect()->away($videoShare->shared_url);
            }

            // URLが見つからないか、有効期限切れの場合
            Log::warning('Invalid or expired short URL accessed', [
                'short_url' => $shortUrl,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            abort(404, 'このURLは無効であるか、有効期限が切れています。');
        } catch (\Exception $e) {
            Log::error('Error in URL redirect', [
                'short_url' => $shortUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'URLのリダイレクト処理中にエラーが発生しました。');
        }
    }
}
