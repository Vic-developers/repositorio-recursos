@extends('layouts.app')

@section('title', 'Configuración')

@section('content')
<div x-data="settingsManager()" x-init="init()" class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Configuración</h1>
            <p class="text-sm text-gray-500 mt-1">Administra la configuración general del sistema y los módulos.</p>
        </div>
    </div>

    {{-- General --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">General</h2>
        <p class="text-sm text-gray-500 mb-6">Nombre del sistema, descripción e idioma principal.</p>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del sistema *</label>
                <input type="text" x-model="general.system_name"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción del sistema</label>
                <textarea x-model="general.system_description" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Idioma predeterminado</label>
                    <select x-model="general.default_language"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="es">Español</option>
                        <option value="en">Inglés</option>
                        <option value="fr">Francés</option>
                        <option value="pt">Portugués</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zona horaria</label>
                    <select x-model="general.timezone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="America/Mexico_City">Ciudad de México</option>
                        <option value="America/Argentina/Buenos_Aires">Buenos Aires</option>
                        <option value="America/Bogota">Bogotá</option>
                        <option value="America/Lima">Lima</option>
                        <option value="America/Santiago">Santiago</option>
                        <option value="America/Madrid">Madrid</option>
                        <option value="UTC">UTC</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Logo URL</label>
                <input type="text" x-model="general.logo_url" placeholder="https://..."
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button x-on:click="saveGeneral" :disabled="saving"
                    class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                <span x-show="!saving">Guardar general</span>
                <span x-show="saving">Guardando...</span>
            </button>
        </div>
    </div>

    {{-- Modules --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Módulos</h2>
        <p class="text-sm text-gray-500 mb-6">Habilita o deshabilita los módulos del sistema.</p>

        <div class="space-y-4">
            <template x-for="(mod, name) in modules" :key="name">
                <div class="flex items-center justify-between py-3 px-4 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center" :class="mod.enabled ? 'bg-green-100' : 'bg-gray-100'">
                            <svg class="w-5 h-5" :class="mod.enabled ? 'text-green-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900" x-text="mod.label"></p>
                            <p class="text-xs text-gray-500" x-text="mod.description"></p>
                        </div>
                    </div>
                    <button x-on:click="mod.enabled = !mod.enabled"
                            class="relative w-11 h-6 rounded-full transition-colors"
                            :class="mod.enabled ? 'bg-indigo-600' : 'bg-gray-300'">
                        <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform"
                              :class="mod.enabled ? 'translate-x-5' : ''"></span>
                    </button>
                </div>
            </template>
        </div>

        <div class="mt-6 flex justify-end">
            <button x-on:click="saveModules" :disabled="saving"
                    class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                <span x-show="!saving">Guardar módulos</span>
                <span x-show="saving">Guardando...</span>
            </button>
        </div>
    </div>

    {{-- SCORM --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">SCORM</h2>
        <p class="text-sm text-gray-500 mb-6">Configuración del motor SCORM.</p>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Versión predeterminada</label>
                    <select x-model="scorm.default_version"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="1.2">SCORM 1.2</option>
                        <option value="2004">SCORM 2004</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tamaño máximo (MB)</label>
                    <input type="number" x-model="scorm.max_file_size_mb" min="1" max="2048"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button x-on:click="saveScorm" :disabled="saving"
                    class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                <span x-show="!saving">Guardar SCORM</span>
                <span x-show="saving">Guardando...</span>
            </button>
        </div>
    </div>

    {{-- Embed --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Incrustación (Embed)</h2>
        <p class="text-sm text-gray-500 mb-6">Configuración para incrustar recursos en Moodle y otros LMS.</p>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dominios permitidos (uno por línea)</label>
                <textarea x-model="embed.allowed_domains" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="moodle.escuela.edu&#10;lms.instituto.edu"></textarea>
                <p class="mt-1 text-xs text-gray-400">Deja vacío para permitir todos los dominios.</p>
            </div>
            <div class="flex items-center gap-3">
                <button x-on:click="embed.enable_frame_ancestors = !embed.enable_frame_ancestors"
                        class="relative w-11 h-6 rounded-full transition-colors shrink-0"
                        :class="embed.enable_frame_ancestors ? 'bg-indigo-600' : 'bg-gray-300'">
                    <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform"
                          :class="embed.enable_frame_ancestors ? 'translate-x-5' : ''"></span>
                </button>
                <label class="text-sm text-gray-700">Enviar cabecera <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">frame-ancestors *</code></label>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button x-on:click="saveEmbed" :disabled="saving"
                    class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                <span x-show="!saving">Guardar embed</span>
                <span x-show="saving">Guardando...</span>
            </button>
        </div>
    </div>

    {{-- Toast notification --}}
    <div x-show="toast.show" x-transition class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-lg shadow-lg text-sm font-medium text-white"
         :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'">
        <span x-text="toast.message"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
function settingsManager() {
    return {
        saving: false,
        toast: { show: false, message: '', type: 'success' },
        general: {
            system_name: '',
            system_description: '',
            default_language: 'es',
            timezone: 'America/Mexico_City',
            logo_url: '',
        },
        modules: {
            resources: { label: 'Recursos', description: 'Gestión de recursos educativos', enabled: true },
            folders: { label: 'Carpetas', description: 'Organización por carpetas', enabled: true },
            categories: { label: 'Categorías', description: 'Clasificación por categorías', enabled: true },
            tags: { label: 'Etiquetas', description: 'Etiquetado de recursos', enabled: true },
            scorm: { label: 'SCORM', description: 'Reproducción de contenido SCORM', enabled: true },
            h5p: { label: 'H5P', description: 'Contenido interactivo H5P', enabled: true },
            favorites: { label: 'Favoritos', description: 'Marcar recursos como favoritos', enabled: true },
            sharing: { label: 'Compartir', description: 'Compartir recursos por enlace', enabled: true },
            trash: { label: 'Papelera', description: 'Recuperar recursos eliminados', enabled: true },
        },
        scorm: {
            default_version: '1.2',
            max_file_size_mb: 500,
        },
        embed: {
            allowed_domains: '',
            enable_frame_ancestors: true,
        },

        async init() {
            try {
                const res = await fetch('/api/v1/settings', {
                    headers: {
                        'Authorization': 'Bearer {{ session('api_token') ?? '' }}',
                        'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}'
                    }
                });
                const json = await res.json();
                if (json.success && json.data) {
                    const s = json.data;
                    // General
                    if (s.general) {
                        if (s.general.system_name) this.general.system_name = s.general.system_name;
                        if (s.general.system_description !== undefined) this.general.system_description = s.general.system_description;
                        if (s.general.default_language) this.general.default_language = s.general.default_language;
                        if (s.general.timezone) this.general.timezone = s.general.timezone;
                        if (s.general.logo_url) this.general.logo_url = s.general.logo_url;
                    }
                    // Modules
                    if (s.modules) {
                        Object.keys(s.modules).forEach(key => {
                            if (this.modules[key]) {
                                this.modules[key].enabled = s.modules[key] === '1' || s.modules[key] === 'true';
                            }
                        });
                    }
                    // SCORM
                    if (s.scorm) {
                        if (s.scorm.default_version) this.scorm.default_version = s.scorm.default_version;
                        if (s.scorm.max_file_size_mb) this.scorm.max_file_size_mb = parseInt(s.scorm.max_file_size_mb);
                    }
                    // Embed
                    if (s.embed) {
                        if (s.embed.allowed_domains) this.embed.allowed_domains = s.embed.allowed_domains;
                        if (s.embed.enable_frame_ancestors !== undefined) {
                            this.embed.enable_frame_ancestors = s.embed.enable_frame_ancestors === '1' || s.embed.enable_frame_ancestors === 'true';
                        }
                    }
                }
            } catch (e) {
                console.error('Error loading settings:', e);
            }
        },

        async saveModule(module, data) {
            this.saving = true;
            try {
                const res = await fetch('/api/v1/settings', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer {{ session('api_token') ?? '' }}',
                        'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}'
                    },
                    body: JSON.stringify({ module, settings: data })
                });
                const json = await res.json();
                if (json.success) {
                    this.showToast('Configuración guardada correctamente', 'success');
                } else {
                    this.showToast('Error al guardar la configuración', 'error');
                }
            } catch (e) {
                console.error('Error saving settings:', e);
                this.showToast('Error de conexión', 'error');
            } finally {
                this.saving = false;
            }
        },

        saveGeneral() {
            this.saveModule('general', {
                system_name: this.general.system_name,
                system_description: this.general.system_description,
                default_language: this.general.default_language,
                timezone: this.general.timezone,
                logo_url: this.general.logo_url,
            });
        },

        saveModules() {
            const data = {};
            Object.keys(this.modules).forEach(key => {
                data[key] = this.modules[key].enabled ? '1' : '0';
            });
            this.saveModule('modules', data);
        },

        saveScorm() {
            this.saveModule('scorm', {
                default_version: this.scorm.default_version,
                max_file_size_mb: String(this.scorm.max_file_size_mb),
            });
        },

        saveEmbed() {
            this.saveModule('embed', {
                allowed_domains: this.embed.allowed_domains,
                enable_frame_ancestors: this.embed.enable_frame_ancestors ? '1' : '0',
            });
        },

        showToast(message, type) {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3000);
        }
    }
}
</script>
@endpush
