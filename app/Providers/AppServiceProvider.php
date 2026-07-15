<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            $publicPath = public_path('storage');
            $storagePath = storage_path('app/public');
            if (!file_exists($publicPath) && is_dir($storagePath)) {
                symlink($storagePath, $publicPath);
            }
        } catch (\Exception $e) {
            //
        }
    }
}
