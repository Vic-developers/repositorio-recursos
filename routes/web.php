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

// Serve SCORM files through Laravel (handles arbitrary-depth subpaths via fallback)
Route::fallback(function (\Illuminate\Http\Request $request) {
    $path = $request->path();
    if (!str_starts_with($path, 'scorm-file/')) {
        abort(404);
    }
    $parts = explode('/', $path, 3);
    $uuid = $parts[1] ?? '';
    $subPath = $parts[2] ?? '';

    $baseDir = storage_path('app/public/scorm/' . $uuid);
    if (!is_dir($baseDir)) abort(404);

    if ($subPath === '') {
        foreach (['index.html', 'launch.html', 'index.htm', 'launch.htm'] as $file) {
            if (file_exists($baseDir . '/' . $file)) {
                return response()->file($baseDir . '/' . $file, ['Content-Type' => 'text/html']);
            }
        }
        abort(404);
    }

    $fullPath = $baseDir . '/' . $subPath;
    if (!file_exists($fullPath) || is_dir($fullPath)) abort(404);

    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $mime = match ($ext) {
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
        'swf' => 'application/x-shockwave-flash',
        default => mime_content_type($fullPath) ?: 'application/octet-stream',
    };
    return response()->file($fullPath, ['Content-Type' => $mime]);
})->name('scorm.file');

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


