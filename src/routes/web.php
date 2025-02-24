<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoFileController;
use App\Http\Controllers\VideoShareController;
use App\Http\Controllers\StreamController;

Route::get('/', function () {
    return view('welcome');
});

// 共有ビデオアクセス用の公開ルート
Route::get('/shared/{token}', [VideoShareController::class, 'accessVideo'])
    ->name('videos.shared');

Route::get('/stream/{shortUrl}', [StreamController::class, 'stream'])
    ->name('stream.video');

// 認証が必要なルートグループ
Route::middleware(['auth', 'verified'])->group(function () {
    // ダッシュボード
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // プロフィール関連
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ビデオファイル関連
    Route::get('/videos/create', [VideoFileController::class, 'create'])->name('videos.create');
    Route::post('/videos', [VideoFileController::class, 'store'])->name('videos.store');
    Route::get('/videos', [VideoFileController::class, 'index'])->name('videos.index');
    Route::post('/videos/{videoFile}/signed-url', [VideoFileController::class, 'generateSignedUrl'])
        ->name('videos.signed-url');
    Route::post('/videos/{videoFile}/revoke', [VideoFileController::class, 'revokeUrl'])
        ->name('videos.revoke');
    Route::delete('/videos/{videoFile}', [VideoFileController::class, 'destroy'])
        ->name('videos.destroy');

    // 共有関連
    Route::post('/videos/{videoFile}/share/confirm', [VideoShareController::class, 'confirmShare'])
        ->name('videos.share.confirm');
    Route::post('/videos/{videoFile}/share', [VideoShareController::class, 'share'])
        ->name('videos.share');
    Route::delete('/shares/{share}', [VideoShareController::class, 'revokeAccess'])
        ->name('shares.revoke');
    Route::get('/shares/{videoFile}', [VideoShareController::class, 'listShares'])
        ->name('shares.list');
});

require __DIR__ . '/auth.php';
