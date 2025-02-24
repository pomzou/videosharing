<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\VideoFile;
use App\Models\VideoShare;
use Illuminate\Support\Facades\Log;

class UrlShortenerService
{
    /**
     * 短縮URLの長さ
     */
    private const URL_LENGTH = 8;

    /**
     * 短縮URLを生成する
     *
     * @return string
     */
    public function generateShortUrl(): string
    {
        $attempts = 0;
        $maxAttempts = 5;

        do {
            $shortUrl = Str::random(self::URL_LENGTH);

            // 既存のURLとの重複チェック
            $videoFileExists = VideoFile::where('short_url', $shortUrl)->exists();
            $videoShareExists = VideoShare::where('short_url', $shortUrl)->exists();

            $attempts++;

            // 最大試行回数を超えた場合はURLの長さを1文字増やす
            if ($attempts >= $maxAttempts) {
                $attempts = 0;
                $urlLength = self::URL_LENGTH + floor($attempts / $maxAttempts);
                $shortUrl = Str::random($urlLength);
            }
        } while ($videoFileExists || $videoShareExists);

        return $shortUrl;
    }

    /**
     * VideoFileモデルの署名付きURLを短縮URLに変換
     *
     * @param VideoFile $videoFile
     * @param string $signedUrl
     * @return void
     */
    public function shortenVideoFileUrl(VideoFile $videoFile, string $signedUrl): void
    {
        try {
            $shortUrl = $this->generateShortUrl();

            $videoFile->update([
                'current_signed_url' => $signedUrl,
                'short_url' => $shortUrl
            ]);

            Log::info('Created short URL for video file', [
                'video_file_id' => $videoFile->id,
                'short_url' => $shortUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create short URL for video file', [
                'video_file_id' => $videoFile->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * VideoShareモデルの共有URLを短縮URLに変換
     *
     * @param VideoShare $videoShare
     * @param string $sharedUrl
     * @return void
     */
    public function shortenVideoShareUrl(VideoShare $videoShare, string $sharedUrl): void
    {
        try {
            $shortUrl = $this->generateShortUrl();

            $videoShare->update([
                'shared_url' => $sharedUrl,
                'short_url' => $shortUrl
            ]);

            Log::info('Created short URL for video share', [
                'video_share_id' => $videoShare->id,
                'short_url' => $shortUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create short URL for video share', [
                'video_share_id' => $videoShare->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * 指定された短縮URLが有効かどうかを確認
     *
     * @param string $shortUrl
     * @return bool
     */
    public function isValidShortUrl(string $shortUrl): bool
    {
        // VideoFilesテーブルでの確認
        $videoFileExists = VideoFile::where('short_url', $shortUrl)
            ->where(function ($query) {
                $query->whereNull('url_expires_at')
                    ->orWhere('url_expires_at', '>', now());
            })
            ->exists();

        if ($videoFileExists) {
            return true;
        }

        // VideoSharesテーブルでの確認
        $videoShareExists = VideoShare::where('short_url', $shortUrl)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        return $videoShareExists;
    }
}
