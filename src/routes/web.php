<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoFileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
});

require __DIR__.'/auth.php';
