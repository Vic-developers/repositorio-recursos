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

// Debug: step-by-step SCORM diagnostic (REMOVE after fixing)
Route::get('/debug-scorm/{uuid}', function (string $uuid) {
    $steps = [];
    try {
        $resource = \App\Models\Resource::where('uuid', $uuid)->first();
        $steps[] = ['step' => 'find_resource', 'ok' => $resource !== null, 'type' => $resource?->type, 'file_path' => $resource?->file_path];
        if (!$resource) { return response(json_encode($steps, JSON_PRETTY_PRINT), 200, ['Content-Type' => 'text/plain']); }

        $steps[] = ['step' => 'is_scorm', 'ok' => $resource->isScorm()];

        $svc = app(\App\Services\ScormService::class);
        $extracted = $svc->ensureExtracted($resource);
        $steps[] = ['step' => 'ensure_extracted', 'ok' => $extracted];

        $baseDir = storage_path('app/public/scorm/' . $uuid);
        $steps[] = ['step' => 'base_dir_exists', 'ok' => is_dir($baseDir), 'path' => $baseDir];

        $launchFile = $svc->getLaunchFile($uuid);
        $launchPath = $baseDir . '/' . $launchFile;
        $steps[] = ['step' => 'launch_file', 'ok' => file_exists($launchPath), 'launch_file' => $launchFile, 'launch_path' => $launchPath];

        $html = @file_get_contents($launchPath);
        $steps[] = ['step' => 'read_file', 'ok' => $html !== false, 'length' => $html !== false ? strlen($html) : 0];

        if ($html !== false) {
            $html2 = @preg_replace('/<base\b[^>]*>/i', '', $html);
            $steps[] = ['step' => 'preg_base', 'ok' => $html2 !== null, 'length' => $html2 !== null ? strlen($html2) : 0];

            $launchDir = dirname($launchFile);
            $baseUrl = url('/scorm-file/' . $uuid . '/' . ($launchDir !== '.' ? $launchDir . '/' : ''));
            $scormApi = '<base href="{{BASE_URL}}"><script>window.API={};</script>';
            $inject = str_replace('{{BASE_URL}}', $baseUrl, $scormApi);
            $html3 = @preg_replace('/<head[^>]*>/i', '$0' . "\n" . $inject, $html2 ?? '');
            $steps[] = ['step' => 'preg_head', 'ok' => $html3 !== null];

            $incrementOk = true;
            try { $resource->increment('view_count'); } catch (\Throwable $e) { $incrementOk = $e->getMessage(); }
            $steps[] = ['step' => 'increment', 'ok' => $incrementOk === true, 'error' => $incrementOk !== true ? $incrementOk : null];
        }
    } catch (\Throwable $e) {
        $steps[] = ['step' => 'exception', 'ok' => false, 'error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()];
    }
    return response(json_encode($steps, JSON_PRETTY_PRINT), 200, ['Content-Type' => 'text/plain']);
});

// Serve SCORM assets (JS, CSS, images, etc.) via explicit route
use Illuminate\Support\Facades\Storage;

Route::get('/scorm-file/{uuid}/{path?}', function (string $uuid, string $path = '') {
    $baseDir = storage_path('app/public/scorm/' . $uuid);
    if (!is_dir($baseDir)) abort(404);

    if (empty($path)) {
        foreach (['index.html', 'launch.html', 'index.htm', 'launch.htm'] as $file) {
            if (file_exists($baseDir . '/' . $file)) {
                return response()->file($baseDir . '/' . $file, ['Content-Type' => 'text/html']);
            }
        }
        abort(404);
    }

    $fullPath = $baseDir . '/' . $path;
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


