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
        $launchPath = $scormService->getLaunchFilePath($resource->uuid);
        $scormContentUrl = url('/storage/scorm/' . $launchPath);
        $embedUrl = url('/embed/' . $resource->uuid);
        
        return view('player.index', [
            'resource' => $resource,
            'scormContentUrl' => $scormContentUrl,
            'embedUrl' => $embedUrl,
        ]);
    }
}
