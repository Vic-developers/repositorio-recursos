<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Services\ScormService;

class EmbedController extends Controller
{
    public function __invoke(string $uuid)
    {
        $resource = Resource::where('uuid', $uuid)->firstOrFail();
        
        if (!$resource->isScorm()) {
            abort(404, 'Not a SCORM resource');
        }
        
        $scormService = app(ScormService::class);
        $launchPath = $scormService->getLaunchFilePath($resource->uuid);
        $scormContentUrl = route('scorm.file', ['uuid' => $resource->uuid, 'path' => $launchPath]);
        
        // Increment view count
        $resource->increment('view_count');
        
        return view('embed.player', [
            'resource' => $resource,
            'scormContentUrl' => $scormContentUrl,
        ]);
    }
}
