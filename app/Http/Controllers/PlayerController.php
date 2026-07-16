<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Services\ScormService;

class PlayerController extends Controller
{
    public function __invoke(string $uuid)
    {
        $resource = Resource::where('uuid', $uuid)->firstOrFail();
        
        if (!$resource->isScorm()) {
            abort(404, 'Not a SCORM resource');
        }
        
        $scormService = app(ScormService::class);
        
        if (!$scormService->ensureExtracted($resource)) {
            abort(404, 'SCORM content not found. Upload the file again.');
        }
        
        $launchFile = $scormService->getLaunchFile($resource->uuid);
        $scormContentUrl = url('/scorm-file/' . $resource->uuid . '/' . $launchFile);
        $embedUrl = url('/embed/' . $resource->uuid);
        
        return response(view('player.index', [
            'resource' => $resource,
            'scormContentUrl' => $scormContentUrl,
            'embedUrl' => $embedUrl,
        ]))->withHeaders([
            'X-Frame-Options' => 'ALLOWALL',
            'Content-Security-Policy' => "frame-ancestors *",
        ]);
    }
}
