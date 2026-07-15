<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resource->name }} - Reproductor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex flex-col h-screen">
        {{-- Header Bar --}}
        <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <a href="{{ url('/resources') }}" class="text-gray-500 hover:text-gray-700 transition-colors p-1 rounded hover:bg-gray-100" title="Volver">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold text-gray-900 truncate max-w-md">{{ $resource->name }}</h1>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="copyEmbedLink()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Copiar enlace Moodle
                </button>
                <button onclick="shareResource()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    Compartir
                </button>
            </div>
        </header>

        {{-- Main Content --}}
        <div class="flex flex-1 overflow-hidden">
            {{-- Sidebar --}}
            <aside class="w-80 bg-white border-r border-gray-200 overflow-y-auto shrink-0 hidden lg:block">
                <div class="p-5 space-y-6">
                    <div>
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Metadatos</h3>
                        <dl class="space-y-3">
                            @if($resource->type)
                            <div>
                                <dt class="text-xs text-gray-500">Tipo</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $resource->type }}</dd>
                            </div>
                            @endif
                            @if($resource->size)
                            <div>
                                <dt class="text-xs text-gray-500">Tamaño</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ number_format($resource->size / 1024, 1) }} MB</dd>
                            </div>
                            @endif
                            @if($resource->language)
                            <div>
                                <dt class="text-xs text-gray-500">Idioma</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $resource->language }}</dd>
                            </div>
                            @endif
                            @if($resource->level)
                            <div>
                                <dt class="text-xs text-gray-500">Nivel</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $resource->level }}</dd>
                            </div>
                            @endif
                            @if($resource->view_count !== null)
                            <div>
                                <dt class="text-xs text-gray-500">Visitas</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ number_format($resource->view_count) }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    @if($resource->description)
                    <div>
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Descripción</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $resource->description }}</p>
                    </div>
                    @endif
                </div>
            </aside>

            {{-- Player Area --}}
            <main class="flex-1 flex flex-col bg-gray-100 relative">
                <div id="loader" class="absolute inset-0 flex items-center justify-center bg-gray-100 z-10 flex-col gap-4">
                    <div class="w-12 h-12 border-4 border-gray-200 border-t-indigo-500 rounded-full animate-spin"></div>
                    <p class="text-sm text-gray-500">Cargando contenido educativo...</p>
                </div>
                <iframe id="scormFrame" src="{{ $scormContentUrl }}" class="flex-1 w-full border-0" allowfullscreen onload="document.getElementById('loader').style.display='none'"></iframe>
            </main>
        </div>
    </div>

    {{-- Toast container --}}
    <div id="toast" class="fixed bottom-6 right-6 bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg text-sm transition-all duration-300 opacity-0 translate-y-2 pointer-events-none"></div>

    <script>
    // ===== SCORM 1.2 API =====
    (function() {
        var scormData = {};
        var lastError = 0;
        var errorStrings = {
            0: 'No error',
            101: 'General exception',
            201: 'Invalid argument error',
            202: 'Element cannot have children',
            203: 'Element not an array - cannot have children',
            301: 'Not initialized',
            401: 'Not implemented error',
            402: 'Invalid set value, element is a keyword',
            403: 'Element is read only',
            404: 'Element is write only',
            405: 'Incorrect data type'
        };

        window.API = {
            LMSInitialize: function() { lastError = 0; return 'true'; },
            LMSFinish: function() { lastError = 0; return 'true'; },
            LMSGetValue: function(name) {
                lastError = 0;
                if (scormData[name] !== undefined) return scormData[name];
                lastError = 201;
                return '';
            },
            LMSSetValue: function(name, value) {
                lastError = 0;
                scormData[name] = value;
                return 'true';
            },
            LMSCommit: function() { lastError = 0; return 'true'; },
            LMSGetLastError: function() { return lastError; },
            LMSGetErrorString: function(code) { return errorStrings[code] || 'Unknown error'; },
            LMSGetDiagnostic: function(code) { return errorStrings[code] || 'Unknown error'; }
        };

        // ===== SCORM 2004 API =====
        window.API_1484_11 = {
            Initialize: function() { lastError = 0; return 'true'; },
            Terminate: function() { lastError = 0; return 'true'; },
            GetValue: function(name) {
                lastError = 0;
                if (scormData[name] !== undefined) return scormData[name];
                lastError = 201;
                return '';
            },
            SetValue: function(name, value) {
                lastError = 0;
                scormData[name] = value;
                return 'true';
            },
            Commit: function() { lastError = 0; return 'true'; },
            GetLastError: function() { return lastError; },
            GetErrorString: function(code) { return errorStrings[code] || 'Unknown error'; },
            GetDiagnostic: function(code) { return errorStrings[code] || 'Unknown error'; }
        };
    })();

    function copyEmbedLink() {
        navigator.clipboard.writeText('{{ $embedUrl }}').then(function() {
            showToast('Enlace copiado al portapapeles');
        }).catch(function() {
            showToast('Error al copiar el enlace');
        });
    }

    function shareResource() {
        if (navigator.share) {
            navigator.share({
                title: '{{ $resource->name }}',
                url: window.location.href
            }).catch(function() {});
        } else {
            copyEmbedLink();
        }
    }

    function showToast(message) {
        var toast = document.getElementById('toast');
        toast.textContent = message;
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
        toast.style.pointerEvents = 'auto';
        setTimeout(function() {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(2px)';
            toast.style.pointerEvents = 'none';
        }, 3000);
    }
</script>
</body>
</html>
