<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resource->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; overflow: hidden; background: #fff; font-family: system-ui, sans-serif; }
        #loader { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; background: #fff; z-index: 9999; flex-direction: column; gap: 16px; }
        #loader .spinner { width: 48px; height: 48px; border: 4px solid #e5e7eb; border-top-color: #6366f1; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        #loader p { color: #6b7280; font-size: 14px; }
        #content { display: none; width: 100%; height: 100%; }
        #content iframe { width: 100%; height: 100%; border: none; }
        .error { display: none; align-items: center; justify-content: center; height: 100%; color: #ef4444; flex-direction: column; gap: 12px; }
        .error h2 { font-size: 18px; }
        .error p { color: #6b7280; }
    </style>
</head>
<body>
    <div id="loader">
        <div class="spinner"></div>
        <p>Cargando recurso educativo...</p>
    </div>
    <div id="content">
        <iframe id="scormFrame" src="{{ $scormContentUrl }}" allowfullscreen></iframe>
    </div>
    <div class="error" id="errorContainer">
        <h2>Error al cargar el recurso</h2>
        <p>El contenido no pudo cargarse correctamente.</p>
    </div>

    <script>
    (function() {
        var scormData = {};
        var lastError = 0;
        var errorStrings = {
            0: 'No error', 101: 'General exception', 201: 'Invalid argument error',
            202: 'Element cannot have children', 203: 'Element not an array - cannot have children',
            301: 'Not initialized', 401: 'Not implemented error', 402: 'Invalid set value, element is a keyword',
            403: 'Element is read only', 404: 'Element is write only', 405: 'Incorrect data type'
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
            LMSSetValue: function(name, value) { lastError = 0; scormData[name] = value; return 'true'; },
            LMSCommit: function() { lastError = 0; return 'true'; },
            LMSGetLastError: function() { return lastError; },
            LMSGetErrorString: function(code) { return errorStrings[code] || 'Unknown error'; },
            LMSGetDiagnostic: function(code) { return errorStrings[code] || 'Unknown error'; }
        };

        window.API_1484_11 = {
            Initialize: function() { lastError = 0; return 'true'; },
            Terminate: function() { lastError = 0; return 'true'; },
            GetValue: function(name) {
                lastError = 0;
                if (scormData[name] !== undefined) return scormData[name];
                lastError = 201;
                return '';
            },
            SetValue: function(name, value) { lastError = 0; scormData[name] = value; return 'true'; },
            Commit: function() { lastError = 0; return 'true'; },
            GetLastError: function() { return lastError; },
            GetErrorString: function(code) { return errorStrings[code] || 'Unknown error'; },
            GetDiagnostic: function(code) { return errorStrings[code] || 'Unknown error'; }
        };
    })();

    document.addEventListener('DOMContentLoaded', function() {
        var iframe = document.getElementById('scormFrame');
        var loader = document.getElementById('loader');
        var content = document.getElementById('content');
        var errorContainer = document.getElementById('errorContainer');
        var loaded = false;

        function showContent() {
            if (loaded) return;
            loaded = true;
            loader.style.display = 'none';
            content.style.display = 'block';
        }

        function showError() {
            if (loaded) return;
            loaded = true;
            loader.style.display = 'none';
            errorContainer.style.display = 'flex';
        }

        iframe.addEventListener('load', showContent);
        iframe.addEventListener('error', showError);

        // Timeout: if content doesn't load within 30s, show error
        setTimeout(function() {
            if (!loaded) {
                errorContainer.querySelector('p').textContent = 'El contenido SCORM tardó demasiado en cargar. Verifica que el archivo SCORM sea válido y esté correctamente subido.';
                showError();
            }
        }, 30000);
    });
    </script>
</body>
</html>
