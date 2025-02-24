<?php

namespace App\Http\Controllers;

use App\Models\VideoFile;
use App\Models\VideoShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use GuzzleHttp\Client;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StreamController extends Controller
{
    public function stream(string $shortUrl)
    {
        try {
            Log::info('Streaming request received', [
                'short_url' => $shortUrl
            ]);

            // VideoFileとVideoShareの両方をチェック
            $videoFile = VideoFile::where('short_url', $shortUrl)
                ->where(function ($query) {
                    $query->whereNull('url_expires_at')
                        ->orWhere('url_expires_at', '>', now());
                })
                ->first();

            $videoShare = VideoShare::where('short_url', $shortUrl)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();

            // 両方とも見つからない場合は404
            if (!$videoFile && !$videoShare) {
                throw new NotFoundHttpException('File not found');
            }

            // どちらかが見つかった方を使用
            $file = $videoFile ?? $videoShare->videoFile;
            $url = $videoFile ? $videoFile->current_signed_url : $videoShare->shared_url;

            // URLが存在しない場合は404
            if (empty($url)) {
                throw new NotFoundHttpException('File not found');
            }

            // アクセスログを記録（共有の場合のみ）
            if ($videoShare) {
                $videoShare->accessLogs()->create([
                    'video_file_id' => $videoShare->video_file_id,
                    'access_email' => $videoShare->email,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'action' => 'stream'
                ]);
            }

            // Content-Type を取得
            $contentType = $file->mime_type;

            // レスポンスヘッダーを設定
            $headers = [
                'Content-Type' => $contentType,
                'Cache-Control' => 'no-cache, private',
            ];

            Log::info('Streaming started', [
                'short_url' => $shortUrl,
                'content_type' => $contentType
            ]);

            // ストリーミングレスポンスを返す
            return new StreamedResponse(function () use ($url) {
                try {
                    $client = new Client();
                    $response = $client->get($url, ['stream' => true]);
                    $body = $response->getBody();

                    while (!$body->eof()) {
                        echo $body->read(1024 * 1024); // 1MB ずつ読み込み
                        flush();
                    }
                } catch (\Exception $e) {
                    Log::error('Streaming failed', [
                        'error' => $e->getMessage()
                    ]);
                    throw new NotFoundHttpException('File not found');
                }
            }, 200, $headers);
        } catch (NotFoundHttpException $e) {
            throw $e;  // 404 エラーをそのまま投げる
        } catch (\Exception $e) {
            Log::error('Streaming error', [
                'short_url' => $shortUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new NotFoundHttpException('File not found');
        }
    }
}
