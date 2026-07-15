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
        $baseDir = storage_path('app/public/scorm/' . $resource->uuid);
        $launchFile = $scormService->getLaunchFile($resource->uuid);
        $launchPath = $baseDir . '/' . $launchFile;

        if (!file_exists($launchPath)) {
            abort(404, 'SCORM content not found. Upload the file again.');
        }

        // Read the SCORM launch file and inject the SCORM API
        $html = file_get_contents($launchPath);

        $apiScript = <<<'JS'
<script>
(function(){
var _data={},_err=0,_errs={0:'No error',101:'General exception',201:'Invalid argument error',202:'Element cannot have children',203:'Element not an array',301:'Not initialized',401:'Not implemented error',402:'Invalid set value',403:'Element is read only',404:'Element is write only',405:'Incorrect data type'};
window.API={LMSInitialize:function(){_err=0;return'true'},LMSFinish:function(){_err=0;return'true'},LMSGetValue:function(n){_err=_data[n]!==undefined?0:201;return _data[n]||''},LMSSetValue:function(n,v){_err=0;_data[n]=v;return'true'},LMSCommit:function(){_err=0;return'true'},LMSGetLastError:function(){return _err},LMSGetErrorString:function(c){return _errs[c]||'Unknown'},LMSGetDiagnostic:function(c){return _errs[c]||'Unknown'}};
window.API_1484_11={Initialize:function(){_err=0;return'true'},Terminate:function(){_err=0;return'true'},GetValue:function(n){_err=_data[n]!==undefined?0:201;return _data[n]||''},SetValue:function(n,v){_err=0;_data[n]=v;return'true'},Commit:function(){_err=0;return'true'},GetLastError:function(){return _err},GetErrorString:function(c){return _errs[c]||'Unknown'},GetDiagnostic:function(c){return _errs[c]||'Unknown'}};
})();
</script>
JS;

        // Inject API script before </head>
        $html = str_replace('</head>', $apiScript . "\n</head>", $html);

        // Use <base> tag so all relative URLs resolve to the SCORM directory
        $baseUrl = url('/scorm-file/' . $resource->uuid . '/');

        // Remove any existing <base> tag to avoid conflicts
        $html = preg_replace('/<base\b[^>]*>/i', '', $html);
        $html = str_replace('<head>', '<head>' . "\n" . '<base href="' . $baseUrl . '">', $html);

        // Increment view count
        $resource->increment('view_count');

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'X-Frame-Options' => 'ALLOWALL',
            'Content-Security-Policy' => "frame-ancestors *",
        ]);
    }
}
