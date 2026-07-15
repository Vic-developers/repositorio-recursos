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

        $scormContentUrl = url('/scorm-file/' . $resource->uuid . '/');

        $resource->increment('view_count');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$resource->name}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
html,body{width:100%;height:100%;overflow:hidden;background:#fff}
#loader{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;background:#fff;z-index:9999;flex-direction:column;gap:16px}
#loader .spinner{width:40px;height:40px;border:4px solid #e5e7eb;border-top-color:#6366f1;border-radius:50%;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
#loader p{color:#6b7280;font-size:14px}
#scorm-frame{width:100%;height:100%;border:none;display:block}
</style>
<script>
(function(){
var _data={'cmi.core._children':'student_id,student_name,lesson_location,credit,lesson_status,entry,score,total_time,lesson_mode,exit,session_time,suspend_data,launch_data,incomplete,comments,comments_from_lms','cmi.core.student_id':'guest','cmi.core.student_name':'Invitado','cmi.core.lesson_status':'not attempted','cmi.core.lesson_mode':'normal','cmi.core.credit':'no-credit','cmi.core.entry':'ab-initio','cmi.core.score._children':'raw,min,max','cmi.core.score.raw':'','cmi.core.score.min':'0','cmi.core.score.max':'100','cmi.core.total_time':'00:00:00','cmi.core.session_time':'00:00:00','cmi.suspend_data':'','cmi.launch_data':'','cmi.comments':'','cmi.comments_from_lms':'','cmi.objectives._children':'id,score,status,description','cmi.objectives._count':'0','cmi.student_data._children':'mastery_score,max_time_allowed,time_limit_action','cmi.student_data.mastery_score':'','cmi.student_preference._children':'audio,language,speed,text','cmi.student_preference.audio':'','cmi.student_preference.language':'','cmi.student_preference.speed':'','cmi.student_preference.text':'','cmi.interactions._children':'id,objectives,time,type,correct_responses,weighting,student_response,result,latency','cmi.interactions._count':'0'},_err=0,_errs={0:'No error',101:'General exception',201:'Invalid argument error',202:'Element cannot have children',203:'Element not an array',301:'Not initialized',401:'Not implemented error',402:'Invalid set value',403:'Element is read only',404:'Element is write only',405:'Incorrect data type'};
window.API={LMSInitialize:function(){_err=0;return'true'},LMSFinish:function(){_err=0;return'true'},LMSGetValue:function(n){var r=_data[n];if(r!==void 0){_err=0;return r}_err=201;return''},LMSSetValue:function(n,v){_err=0;_data[n]=String(v);return'true'},LMSCommit:function(){_err=0;return'true'},LMSGetLastError:function(){return _err},LMSGetErrorString:function(c){return _errs[c]||'Unknown'},LMSGetDiagnostic:function(c){return _errs[c]||'Unknown'}};
window.API_1484_11={Initialize:function(){_err=0;return'true'},Terminate:function(){_err=0;return'true'},GetValue:function(n){var r=_data[n];if(r!==void 0){_err=0;return r}_err=201;return''},SetValue:function(n,v){_err=0;_data[n]=String(v);return'true'},Commit:function(){_err=0;return'true'},GetLastError:function(){return _err},GetErrorString:function(c){return _errs[c]||'Unknown'},GetDiagnostic:function(c){return _errs[c]||'Unknown'}};
try{Object.defineProperty(window,'parent',{value:window});Object.defineProperty(window,'top',{value:window})}catch(e){}
})();
</script>
</head>
<body>
<div id="loader"><div class="spinner"></div><p>Cargando recurso educativo...</p></div>
<iframe id="scorm-frame" src="{$scormContentUrl}index.html"></iframe>
<script>
var f=document.getElementById('scorm-frame'),l=document.getElementById('loader');
f.addEventListener('load',function(){l.style.display='none'});
f.addEventListener('error',function(){l.innerHTML='<p style=color:red>Error al cargar el contenido.</p>'});
setTimeout(function(){if(l.style.display!=='none')l.innerHTML='<p style=color:#ef4444>El contenido SCORM tardó demasiado. <a href="{$scormContentUrl}index.html" target=_blank style=color:#6366f1>Abrir directamente</a></p>'},15000);
</script>
</body>
</html>
HTML;

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'X-Frame-Options' => 'ALLOWALL',
            'Content-Security-Policy' => "frame-ancestors *",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
