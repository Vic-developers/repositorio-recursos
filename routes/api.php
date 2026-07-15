<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FavoritesController;
use App\Http\Controllers\Api\V1\FolderController;
use App\Http\Controllers\Api\V1\ResourceController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\ShareController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\TrashController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);

    // Share access (public by token)
    Route::get('share/{token}', [ShareController::class, 'access']);

    Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
        // Auth
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Resources
        Route::get('resources', [ResourceController::class, 'index']);
        Route::post('resources', [ResourceController::class, 'store']);
        Route::get('resources/{resource}', [ResourceController::class, 'show']);
        Route::put('resources/{resource}', [ResourceController::class, 'update']);
        Route::delete('resources/{resource}', [ResourceController::class, 'destroy']);
        Route::patch('resources/{resource}/restore', [ResourceController::class, 'restore']);
        Route::delete('resources/{resource}/force', [ResourceController::class, 'forceDelete']);
        Route::post('resources/{resource}/duplicate', [ResourceController::class, 'duplicate']);
        Route::post('resources/{resource}/favorite', [ResourceController::class, 'toggleFavorite']);
        Route::post('resources/{resource}/download', [ResourceController::class, 'incrementDownload']);

        // File upload (standalone)
        Route::post('upload', [ResourceController::class, 'uploadFile']);

        // Folders
        Route::get('folders', [FolderController::class, 'index']);
        Route::post('folders', [FolderController::class, 'store']);
        Route::get('folders/{folder}', [FolderController::class, 'show']);
        Route::put('folders/{folder}', [FolderController::class, 'update']);
        Route::delete('folders/{folder}', [FolderController::class, 'destroy']);

        // Categories
        Route::get('categories', [CategoryController::class, 'index']);
        Route::post('categories', [CategoryController::class, 'store']);
        Route::get('categories/{category}', [CategoryController::class, 'show']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

        // Tags
        Route::get('tags', [TagController::class, 'index']);
        Route::post('tags', [TagController::class, 'store']);
        Route::get('tags/{tag}', [TagController::class, 'show']);
        Route::put('tags/{tag}', [TagController::class, 'update']);
        Route::delete('tags/{tag}', [TagController::class, 'destroy']);

        // Shares
        Route::get('resources/{resource}/shares', [ShareController::class, 'index']);
        Route::post('resources/{resource}/shares', [ShareController::class, 'store']);
        Route::get('resources/{resource}/shares/{share}', [ShareController::class, 'show']);
        Route::delete('resources/{resource}/shares/{share}', [ShareController::class, 'destroy']);
        Route::get('resources/{resource}/embed-code', [ShareController::class, 'generateEmbedCode']);

        // Search
        Route::get('search', [SearchController::class, 'search']);
        Route::get('search/suggestions', [SearchController::class, 'suggestions']);

        // Favorites
        Route::get('favorites', [FavoritesController::class, 'index']);

        // Trash
        Route::get('trash', [TrashController::class, 'index']);
        Route::post('trash/{resource}/restore', [TrashController::class, 'restore']);
        Route::delete('trash/{resource}', [TrashController::class, 'forceDelete']);
        Route::delete('trash/empty', [TrashController::class, 'empty']);

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Settings
        Route::get('settings', [SettingsController::class, 'index']);
        Route::get('settings/{module}', [SettingsController::class, 'show']);
        Route::put('settings', [SettingsController::class, 'update']);
    });
});
