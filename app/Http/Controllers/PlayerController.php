<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Services\ScormService;
use Illuminate\Support\Facades\Storage;

class PlayerController extends Controller
{
    public function __invoke(string $uuid)
    {
        $resource = Resource::where('uuid', $uuid)->firstOrFail();

        $embedUrl = url('/embed/' . $resource->uuid);
        $contentUrl = $embedUrl;
        $fileUrl = $resource->file_path ? url('/resource-file/' . $resource->uuid . '/' . basename($resource->file_path)) : null;

        if ($resource->isScorm()) {
            $scormService = app(ScormService::class);
            if (!$scormService->ensureExtracted($resource)) {
                abort(404, 'SCORM content not found. Upload the file again.');
            }
        } elseif ($resource->type === 'H5P') {
            $embedUrl = url('/h5p-embed/' . $resource->uuid);
            $contentUrl = $embedUrl;
        } elseif ($resource->type === 'Link') {
            $embedUrl = $resource->description ?: $resource->slug;
            $contentUrl = $embedUrl;
        } else {
            $contentUrl = $fileUrl;
            $embedUrl = $fileUrl;
        }

        return response(view('player.index', [
            'resource' => $resource,
            'embedUrl' => $embedUrl,
            'contentUrl' => $contentUrl,
            'fileUrl' => $fileUrl,
        ]))->withHeaders([
            'X-Frame-Options' => 'ALLOWALL',
            'Content-Security-Policy' => "frame-ancestors *",
        ]);
    }

    public function h5pEmbed(string $uuid)
    {
        $resource = Resource::where('uuid', $uuid)->firstOrFail();
        if ($resource->type !== 'H5P') abort(404);

        $filePath = storage_path('app/public/' . $resource->file_path);

        // Restore ZIP from DB if missing
        if (!file_exists($filePath) && $resource->file_data) {
            $dir = dirname($filePath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            file_put_contents($filePath, $resource->file_data);
        }
        if (!file_exists($filePath)) abort(404);

        // H5P packages are ZIP files; extract on-the-fly and serve index.html
        $extractDir = storage_path('app/public/h5p/' . $resource->uuid);
        if (!is_dir($extractDir)) {
            @mkdir($extractDir, 0755, true);
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $zip->extractTo($extractDir);
                $zip->close();
            }
        }

        $indexFile = $extractDir . '/index.html';
        if (!file_exists($indexFile)) {
            // Find any HTML file
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($extractDir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($files as $file) {
                if (in_array(strtolower($file->getExtension()), ['html', 'htm'])) {
                    $indexFile = $file->getRealPath();
                    break;
                }
            }
        }

        if (!file_exists($indexFile)) {
            abort(404, 'H5P content not found');
        }

        $html = file_get_contents($indexFile);
        if ($html === false) {
            abort(500);
        }

        $resource->increment('view_count');

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'X-Frame-Options' => 'ALLOWALL',
            'Content-Security-Policy' => "frame-ancestors *",
            'Cache-Control' => 'no-cache, no-store',
        ]);
    }
}
