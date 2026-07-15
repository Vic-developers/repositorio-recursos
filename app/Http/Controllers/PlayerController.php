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
        $launchFile = $scormService->getLaunchFile($resource->uuid);
        $scormContentUrl = route('scorm.file', ['uuid' => $resource->uuid, 'path' => $launchFile]);
        $embedUrl = url('/embed/' . $resource->uuid);
        
        return view('player.index', [
            'resource' => $resource,
            'scormContentUrl' => $scormContentUrl,
            'embedUrl' => $embedUrl,
        ]);
    }
}
