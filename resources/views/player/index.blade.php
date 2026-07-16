<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resource->name }} — Repositorio Educativo</title>
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box }
        html, body { width:100%; height:100%; overflow:hidden; background:#000; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif }
        iframe#scorm-frame { width:100%; height:100%; border:none; display:block }
        #overlay {
            position:fixed; top:0; left:0; right:0; z-index:999;
            background:linear-gradient(180deg,rgba(0,0,0,.85) 0%,rgba(0,0,0,.65) 50%,transparent 100%);
            opacity:0; transition:opacity .25s ease;
            pointer-events:none; padding:12px 16px 32px
        }
        #overlay.visible { opacity:1; pointer-events:auto }
        #overlay .row { display:flex; align-items:center; justify-content:space-between; gap:12px }
        #overlay .left { display:flex; align-items:center; gap:12px; min-width:0 }
        #overlay .title { color:#fff; font-size:15px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
        #overlay .badge { padding:2px 10px; border-radius:999px; font-size:11px; font-weight:600; background:rgba(255,255,255,.15); color:#fff; flex-shrink:0 }
        #overlay .actions { display:flex; align-items:center; gap:6px; flex-shrink:0 }
        .btn {
            display:inline-flex; align-items:center; justify-content:center; gap:6px;
            padding:7px 14px; border-radius:8px; font-size:13px; font-weight:500;
            cursor:pointer; border:none; background:rgba(255,255,255,.12); color:#fff;
            text-decoration:none; transition:background .15s; white-space:nowrap
        }
        .btn:hover { background:rgba(255,255,255,.22) }
        .btn-icon { width:36px; height:36px; padding:0; border-radius:8px }
        #loader {
            position:fixed; inset:0; display:flex; flex-direction:column;
            align-items:center; justify-content:center; background:#0f172a;
            z-index:998; transition:opacity .4s ease
        }
        #loader.hidden { opacity:0; pointer-events:none }
        .spinner { width:44px; height:44px; border:3px solid rgba(255,255,255,.1); border-top-color:#3b82f6; border-radius:50%; animation:spin .7s linear infinite }
        @keyframes spin { to{transform:rotate(360deg)} }
        #loader p { margin-top:14px; color:#94a3b8; font-size:14px }
        #share-modal {
            position:fixed; inset:0; z-index:1000;
            display:none; align-items:center; justify-content:center;
            background:rgba(0,0,0,.7); backdrop-filter:blur(4px)
        }
        #share-modal.open { display:flex }
        #share-modal .panel {
            background:#1e293b; border-radius:16px; padding:28px 32px;
            max-width:520px; width:90%; max-height:90vh; overflow-y:auto;
            box-shadow:0 25px 50px rgba(0,0,0,.5)
        }
        #share-modal h2 { color:#fff; font-size:20px; margin-bottom:4px }
        #share-modal p { color:#94a3b8; font-size:13px; margin-bottom:20px }
        .share-option { margin-bottom:12px }
        .share-option label { display:block; color:#cbd5e1; font-size:12px; font-weight:600; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px }
        .share-option .copy-row { display:flex; gap:6px }
        .share-option .copy-row input, .share-option .copy-row textarea {
            flex:1; background:#0f172a; border:1px solid #334155; border-radius:8px;
            padding:10px 12px; color:#e2e8f0; font-size:13px; font-family:monospace;
            outline:none
        }
        .share-option .copy-row textarea { height:56px; resize:none }
        .share-option .copy-row button {
            padding:8px 16px; border-radius:8px; background:#3b82f6; color:#fff;
            border:none; cursor:pointer; font-size:13px; font-weight:600; white-space:nowrap;
            transition:background .15s
        }
        .share-option .copy-row button:hover { background:#2563eb }
        .share-option .copy-row button.copied { background:#22c55e }
        .lms-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:16px }
        .lms-btn {
            display:flex; align-items:center; gap:8px; padding:10px 12px;
            border-radius:10px; background:#0f172a; border:1px solid #334155;
            color:#e2e8f0; cursor:pointer; font-size:13px; transition:all .15s;
            text-decoration:none
        }
        .lms-btn:hover { background:#1e293b; border-color:#3b82f6 }
        .lms-btn .icon { width:24px; height:24px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0 }
        .lms-btn .lbl { font-weight:500 }
        .lms-btn .sub { font-size:11px; color:#64748b }
        #close-share { margin-top:16px; width:100%; padding:10px; border-radius:10px; background:#334155; color:#fff; border:none; cursor:pointer; font-size:14px }
        #close-share:hover { background:#475569 }
        .toast {
            position:fixed; bottom:24px; left:50%; transform:translateX(-50%);
            padding:10px 20px; background:#1e293b; color:#fff; border-radius:10px;
            font-size:14px; opacity:0; transition:opacity .3s; pointer-events:none; z-index:9999;
            box-shadow:0 4px 12px rgba(0,0,0,.3)
        }
        .toast.show { opacity:1 }
    </style>
</head>
<body>
    <div id="loader">
        <div class="spinner"></div>
        <p>Cargando recurso...</p>
    </div>

    <div id="overlay" class="visible">
        <div class="row">
            <div class="left">
                <button onclick="toggleOverlay()" class="btn btn-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                </button>
                <span class="title">{{ $resource->name }}</span>
                <span class="badge">{{ $resource->type }}</span>
            </div>
            <div class="actions">
                <button onclick="openShare()" class="btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                    Compartir
                </button>
                <button onclick="copyEmbedUrl()" class="btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                </button>
                <button onclick="toggleFullscreen()" class="btn btn-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="fs-icon"><path d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"/></svg>
                </button>
            </div>
        </div>
    </div>

    <iframe id="scorm-frame" src="{{ $scormContentUrl }}" allow="autoplay *; fullscreen *" allowfullscreen
        onload="document.getElementById('loader').classList.add('hidden')"></iframe>

    <div id="toast" class="toast"></div>

    <div id="share-modal">
        <div class="panel">
            <h2>Compartir recurso</h2>
            <p>Comparte este recurso en cualquier plataforma LMS</p>

            <div class="lms-grid">
                <a href="#" onclick="return copyLms('moodle')" class="lms-btn">
                    <div class="icon" style="background:#f97316;color:#fff">M</div>
                    <div><div class="lbl">Moodle</div><div class="sub">Actividad URL o iframe</div></div>
                </a>
                <a href="#" onclick="return copyLms('canvas')" class="lms-btn">
                    <div class="icon" style="background:#e11d48;color:#fff">C</div>
                    <div><div class="lbl">Canvas</div><div class="sub">Enlace externo</div></div>
                </a>
                <a href="#" onclick="return copyLms('blackboard')" class="lms-btn">
                    <div class="icon" style="background:#2563eb;color:#fff">B</div>
                    <div><div class="lbl">Blackboard</div><div class="sub">Web Link / iframe</div></div>
                </a>
                <a href="#" onclick="return copyLms('google-classroom')" class="lms-btn">
                    <div class="icon" style="background:#22c55e;color:#fff">G</div>
                    <div><div class="lbl">Classroom</div><div class="sub">Enlace de material</div></div>
                </a>
            </div>

            <div class="share-option">
                <label>Enlace embed (Moodle)</label>
                <div class="copy-row">
                    <input type="text" value="{{ $embedUrl }}" readonly onclick="this.select()">
                    <button onclick="copyText(this,'{{ $embedUrl }}')">Copiar</button>
                </div>
            </div>

            <div class="share-option">
                <label>Código iframe</label>
                <div class="copy-row">
                    <textarea readonly onclick="this.select()"><iframe src="{{ $embedUrl }}" width="100%" height="600" frameborder="0" allowfullscreen></iframe></textarea>
                    <button onclick="copyText(this,'<iframe src=\'{{ $embedUrl }}\' width=\'100%\' height=\'600\' frameborder=\'0\' allowfullscreen></iframe>')">Copiar</button>
                </div>
            </div>

            <div class="share-option">
                <label>Enlace directo al reproductor</label>
                <div class="copy-row">
                    <input type="text" value="{{ url('/player/' . $resource->uuid) }}" readonly onclick="this.select()">
                    <button onclick="copyText(this,'{{ url('/player/' . $resource->uuid) }}')">Copiar</button>
                </div>
            </div>

            <button id="close-share" onclick="closeShare()">Cerrar</button>
        </div>
    </div>

    <script>
    // SCORM API stubs — el SCORM busca window.parent.API desde el iframe
    (function(){
    var _data={'cmi.core._children':'student_id,student_name,lesson_location,credit,lesson_status,entry,score,total_time,lesson_mode,exit,session_time,suspend_data,launch_data,incomplete,comments,comments_from_lms','cmi.core.student_id':'guest','cmi.core.student_name':'Invitado','cmi.core.lesson_status':'not attempted','cmi.core.lesson_mode':'normal','cmi.core.credit':'no-credit','cmi.core.entry':'ab-initio','cmi.core.score._children':'raw,min,max','cmi.core.score.raw':'','cmi.core.score.min':'0','cmi.core.score.max':'100','cmi.core.total_time':'00:00:00','cmi.core.session_time':'00:00:00','cmi.suspend_data':'','cmi.launch_data':'','cmi.comments':'','cmi.comments_from_lms':'','cmi.objectives._children':'id,score,status,description','cmi.objectives._count':'0','cmi.student_data._children':'mastery_score,max_time_allowed,time_limit_action','cmi.student_data.mastery_score':'','cmi.student_preference._children':'audio,language,speed,text','cmi.student_preference.audio':'','cmi.student_preference.language':'','cmi.student_preference.speed':'','cmi.student_preference.text':'','cmi.interactions._children':'id,objectives,time,type,correct_responses,weighting,student_response,result,latency','cmi.interactions._count':'0'},_err=0,_errs={0:'No error',101:'General exception',201:'Invalid argument error',202:'Element cannot have children',203:'Element not an array',301:'Not initialized',401:'Not implemented error',402:'Invalid set value',403:'Element is read only',404:'Element is write only',405:'Incorrect data type'};
    window.API={LMSInitialize:function(){_err=0;return'true'},LMSFinish:function(){_err=0;return'true'},LMSGetValue:function(n){var r=_data[n];if(r!==void 0){_err=0;return r}_err=201;return''},LMSSetValue:function(n,v){_err=0;_data[n]=String(v);return'true'},LMSCommit:function(){_err=0;return'true'},LMSGetLastError:function(){return _err},LMSGetErrorString:function(c){return _errs[c]||'Unknown'},LMSGetDiagnostic:function(c){return _errs[c]||'Unknown'}};
    window.API_1484_11={Initialize:function(){_err=0;return'true'},Terminate:function(){_err=0;return'true'},GetValue:function(n){var r=_data[n];if(r!==void 0){_err=0;return r}_err=201;return''},SetValue:function(n,v){_err=0;_data[n]=String(v);return'true'},Commit:function(){_err=0;return'true'},GetLastError:function(){return _err},GetErrorString:function(c){return _errs[c]||'Unknown'},GetDiagnostic:function(c){return _errs[c]||'Unknown'}};
    })();

    let overlayTimer;
    document.addEventListener('mousemove',function(){clearTimeout(overlayTimer);document.getElementById('overlay').classList.add('visible');overlayTimer=setTimeout(function(){document.getElementById('overlay').classList.remove('visible')},3000)});
    document.getElementById('overlay').addEventListener('mouseenter',function(){clearTimeout(overlayTimer)});
    document.addEventListener('keydown',function(e){if(e.key==='Escape')document.getElementById('overlay').classList.toggle('visible')});

    function toggleOverlay(){document.getElementById('overlay').classList.toggle('visible')}

    function toggleFullscreen(){
        if(!document.fullscreenElement){document.documentElement.requestFullscreen()
            document.getElementById('fs-icon').innerHTML='<path d="M8 3v3a2 2 0 01-2 2H3m18 0h-3a2 2 0 01-2-2V3m0 18v-3a2 2 0 012-2h3M3 16h3a2 2 0 012 2v3"/>'
        }else{document.exitFullscreen()
            document.getElementById('fs-icon').innerHTML='<path d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"/>'}
    }

    function showToast(m){var t=document.getElementById('toast');t.textContent=m;t.classList.add('show');setTimeout(function(){t.classList.remove('show')},2500)}

    function copyEmbedUrl(){
        var u='{{ $embedUrl }}';
        if(navigator.clipboard){navigator.clipboard.writeText(u).then(function(){showToast('Enlace copiado')}).catch(function(){fallbackCopy(u)})}else{fallbackCopy(u)}
    }
    function fallbackCopy(t){var ta=document.createElement('textarea');ta.value=t;document.body.appendChild(ta);ta.select();document.execCommand('copy');ta.remove();showToast('Copiado al portapapeles')}

    function copyText(btn,text){
        if(navigator.clipboard){navigator.clipboard.writeText(text).then(function(){
            btn.textContent='Copiado!';btn.classList.add('copied');setTimeout(function(){btn.textContent='Copiar';btn.classList.remove('copied')},2000)
        }).catch(function(){fallbackCopy(text);btn.textContent='Copiado!';btn.classList.add('copied');setTimeout(function(){btn.textContent='Copiar';btn.classList.remove('copied')},2000)})}else{fallbackCopy(text);btn.textContent='Copiado!';btn.classList.add('copied');setTimeout(function(){btn.textContent='Copiar';btn.classList.remove('copied')},2000)}
    }

    function openShare(){document.getElementById('share-modal').classList.add('open')}
    function closeShare(){document.getElementById('share-modal').classList.remove('open')}
    document.getElementById('share-modal').addEventListener('click',function(e){if(e.target===this)closeShare()})

    function copyLms(lms){
        var u='{{ $embedUrl }}';
        var labels={moodle:'Moodle',canvas:'Canvas',blackboard:'Blackboard','google-classroom':'Google Classroom'};
        var instructions={moodle:'📘 Cómo añadir en MOODLE:\n━━━━━━━━━━━━━━━━━━━━\n\n📌 OPCIÓN 1 — Actividad "URL"\n1. Activar edición → Agregar actividad → "URL"\n2. Pegar este enlace:\n'+u+'\n3. Apariencia: "Incrustar" o "Abrir"\n\n📌 OPCIÓN 2 — iframe en Página/Etiqueta\n<iframe src="'+u+'" width="100%" height="600" frameborder="0" allowfullscreen></iframe>',
            canvas:'Enlace externo para Canvas:\n'+u+'\n\nPégalo como URL de herramienta externa.',
            blackboard:'Enlace para Blackboard:\n'+u+'\n\nUsa "Web Link" o pega el iframe en un item de contenido.',
            'google-classroom':'Enlace para Google Classroom:\n'+u+'\n\nAgrega como "Enlace" en material de clase.'};
        var text=labels[lms]+'\n\n'+instructions[lms];
        if(navigator.clipboard){navigator.clipboard.writeText(text).then(function(){showToast('Instrucciones para '+labels[lms]+' copiadas')}).catch(function(){fallbackCopy(text);showToast('Instrucciones copiadas')})}else{fallbackCopy(text);showToast('Instrucciones copiadas')}
        return false
    }

    document.addEventListener('keydown',function(e){if(e.key==='Escape'&&document.getElementById('share-modal').classList.contains('open'))closeShare()});
    </script>
</body>
</html>