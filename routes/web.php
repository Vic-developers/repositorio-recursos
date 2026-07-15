<?php

use App\Http\Controllers\EmbedController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Auth routes
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);

// Public routes for viewing resources (no auth needed for shared links)
Route::get('/embed/{uuid}', EmbedController::class)->name('embed.player');
Route::get('/player/{uuid}', PlayerController::class)->name('player.show');

// Serve SCORM files directly (for Cloud/deployed environments)
Route::get('/scorm-file/{uuid}/{path?}', function (string $uuid, string $path = '') {
    $basePath = storage_path('app/public/scorm/' . $uuid);
    $fullPath = $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    if (!file_exists($fullPath)) {
        abort(404);
    }
    $mime = match (pathinfo($fullPath, PATHINFO_EXTENSION)) {
        'html', 'htm' => 'text/html',
        'js' => 'application/javascript',
        'css' => 'text/css',
        'xml' => 'application/xml',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        default => mime_content_type($fullPath) ?: 'application/octet-stream',
    };
    return response()->file($fullPath, ['Content-Type' => $mime]);
})->where('path', '.*')->name('scorm.file');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    Route::get('/resources', function () {
        return view('resources.index');
    })->name('resources.index');

    Route::get('/resources/create', function () {
        return view('resources.create');
    })->name('resources.create');

    Route::get('/folders', function () {
        return view('folders.index');
    })->name('folders.index');

    Route::get('/categories', function () {
        return view('categories.index');
    })->name('categories.index');

    Route::get('/tags', function () {
        return view('tags.index');
    })->name('tags.index');

    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');
});


