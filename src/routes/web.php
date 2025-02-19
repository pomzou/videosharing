<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoFileController;
use App\Http\Controllers\VideoShareController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/shared/{token}', [VideoShareController::class, 'accessVideo'])->name('videos.shared');
Route::post('/videos/{videoFile}/revoke', [VideoFileController::class, 'revokeUrl'])
    ->name('videos.revoke');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/videos/create', [VideoFileController::class, 'create'])->name('videos.create');
    Route::post('/videos', [VideoFileController::class, 'store'])->name('videos.store');

    Route::get('/videos', [VideoFileController::class, 'index'])->name('videos.index');
    Route::get('/videos/{videoFile}/signed-url', [VideoFileController::class, 'generateSignedUrl'])
        ->name('videos.signed-url');

    Route::delete('/videos/{videoFile}', [VideoFileController::class, 'destroy'])->name('videos.destroy');

    // 共有関連のルート
    Route::post('/videos/{videoFile}/share/confirm', [VideoShareController::class, 'confirmShare'])->name('videos.share.confirm');
    Route::post('/videos/{videoFile}/share', [VideoShareController::class, 'share'])->name('videos.share');
    Route::delete('/shares/{share}', [VideoShareController::class, 'revokeAccess'])->name('shares.revoke');
    Route::get('/shares/{videoFile}', [VideoShareController::class, 'listShares'])->name('shares.list');
    Route::post('/videos/{videoFile}/signed-url', [VideoFileController::class, 'generateSignedUrl'])->name('videos.signed-url');
});

require __DIR__ . '/auth.php';
