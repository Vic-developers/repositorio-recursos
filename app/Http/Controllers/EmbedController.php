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
        $launchFile = $scormService->getLaunchFile($resource->uuid);
        $scormContentUrl = url('/scorm-file/' . $resource->uuid . '/' . $launchFile);
        
        // Increment view count
        $resource->increment('view_count');
        
        return response()
            ->view('embed.player', [
                'resource' => $resource,
                'scormContentUrl' => $scormContentUrl,
            ])
            ->header('X-Frame-Options', 'ALLOWALL')
            ->header('Content-Security-Policy', "frame-ancestors *");
    }
}
