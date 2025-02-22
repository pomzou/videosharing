<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VideoFile;
use App\Models\VideoShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class VideoShareExpirationTest extends TestCase
{
    use RefreshDatabase;

    public function testVideoShareExpiration()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();
        $this->actingAs($user);

        // S3モックを設定
        Storage::fake('s3');

        // テスト用の動画ファイルを作成
        $videoFile = VideoFile::create([
            'user_id' => $user->id,
            'title' => 'Test Video',
            'file_name' => 'test_video.mp4',
            'original_name' => 'test_video.mp4',
            'mime_type' => 'video/mp4',
            'file_size' => 1024,
            's3_path' => 'files/test_video.mp4'
        ]);

        // 1分後に期限切れになる共有リンクを作成
        $expiresAt = now()->addMinute();
        $share = $videoFile->shares()->create([
            'email' => 'test@example.com',
            'access_token' => Str::random(32),
            'expires_at' => $expiresAt,
            'is_active' => true,
            'share_type' => 'email'
        ]);

        // 1分以上待機（テスト環境では実際の時間経過をシミュレート）
        $this->travel(2)->minutes();

        // 共有リンクにアクセスし、403エラーが返されることを確認
        $response = $this->get(route('access.video', ['token' => $share->access_token]));
        $response->assertStatus(403);
        $response->assertSee('This share link has expired or is no longer active');

        // 共有が非アクティブになっていることを確認
        $share->refresh();
        $this->assertFalse($share->is_active);
    }

    public function testCacheInvalidation()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();
        $this->actingAs($user);

        // S3モックを設定
        Storage::fake('s3');

        // テスト用の動画ファイルを作成
        $videoFile = VideoFile::create([
            'user_id' => $user->id,
            'title' => 'Test Video',
            'file_name' => 'test_video.mp4',
            'original_name' => 'test_video.mp4',
            'mime_type' => 'video/mp4',
            'file_size' => 1024,
            's3_path' => 'files/test_video.mp4'
        ]);

        // 共有リンクを作成
        $expiresAt = now()->addMinute();
        $share = $videoFile->shares()->create([
            'email' => 'test@example.com',
            'access_token' => Str::random(32),
            'expires_at' => $expiresAt,
            'is_active' => true,
            'share_type' => 'email'
        ]);

        // ここでCDNキャッシュがクリアされたことを確認するロジックを追加
        // 例えば、モックを使ってAPI呼び出しを確認するなど

        // 共有リンクにアクセスし、403エラーが返されることを確認
        $response = $this->get(route('access.video', ['token' => $share->access_token]));
        $response->assertStatus(403);
        $response->assertSee('This share link has expired or is no longer active');
    }
}
