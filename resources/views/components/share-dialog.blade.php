<div x-data="shareDialog()" x-on:open-share.window="open($event.detail)" x-show="show" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500/75 transition-opacity" x-on:click="show = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" x-text="'Compartir: ' + (resource?.name || '')"></h3>
                <button x-on:click="show = false" class="p-1 text-gray-400 hover:text-gray-600 rounded hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-4">
                {{-- Tabs --}}
                <div class="flex border-b border-gray-200 mb-4">
                    <template x-for="tab in tabs" :key="tab.id">
                        <button x-on:click="activeTab = tab.id"
                                class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
                                :class="activeTab === tab.id ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                            <span x-text="tab.label"></span>
                        </button>
                    </template>
                </div>

                {{-- Tab: Enlace Moodle --}}
                <div x-show="activeTab === 'moodle'">
                    <p class="text-sm text-gray-600 mb-3">Copia este enlace para insertarlo en Moodle:</p>
                    <div class="flex gap-2">
                        <input type="text" :value="embedUrl" readonly
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50">
                        <button x-on:click="copyToClipboard(embedUrl)"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shrink-0">
                            Copiar
                        </button>
                    </div>
                </div>

                {{-- Tab: iframe --}}
                <div x-show="activeTab === 'iframe'">
                    <p class="text-sm text-gray-600 mb-3">Copia este código iframe para insertar en cualquier página:</p>
                    <div class="flex gap-2">
                        <input type="text" :value="iframeCode" readonly
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 font-mono">
                        <button x-on:click="copyToClipboard(iframeCode)"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shrink-0">
                            Copiar
                        </button>
                    </div>
                </div>

                {{-- Tab: HTML --}}
                <div x-show="activeTab === 'html'">
                    <p class="text-sm text-gray-600 mb-3">Código HTML completo responsive:</p>
                    <div class="flex gap-2">
                        <input type="text" :value="htmlCode" readonly
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50 font-mono">
                        <button x-on:click="copyToClipboard(htmlCode)"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shrink-0">
                            Copiar
                        </button>
                    </div>
                </div>

                {{-- Tab: LMS Instructions --}}
                <div x-show="activeTab === 'lms'">
                    <p class="text-sm text-gray-600 mb-3">Selecciona tu LMS para ver las instrucciones:</p>
                    <select x-model="selectedLms" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg mb-4">
                        <option value="moodle">Moodle</option>
                        <option value="canvas">Canvas</option>
                        <option value="classroom">Google Classroom</option>
                        <option value="teams">Microsoft Teams</option>
                        <option value="schoology">Schoology</option>
                    </select>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                            <template x-for="(step, index) in lmsInstructions[selectedLms]?.steps || []" :key="index">
                                <li>
                                    <span x-text="step"></span>
                                    <template x-if="step.startsWith('http')">
                                        <div class="mt-1 flex gap-2">
                                            <input type="text" :value="step" readonly class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-white font-mono">
                                            <button x-on:click="copyToClipboard(step)" class="px-2 py-1 text-xs text-indigo-600 hover:text-indigo-800 shrink-0">Copiar</button>
                                        </div>
                                    </template>
                                </li>
                            </template>
                        </ol>
                    </div>
                </div>

                {{-- Tab: QR --}}
                <div x-show="activeTab === 'qr'">
                    <p class="text-sm text-gray-600 mb-3">Escanea este código QR para acceder desde un dispositivo móvil:</p>
                    <div class="flex justify-center">
                        <img :src="qrCodeUrl" alt="QR Code" class="w-48 h-48">
                    </div>
                    <div class="flex gap-2 mt-4">
                        <input type="text" :value="embedUrl" readonly
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-gray-50">
                        <button x-on:click="copyToClipboard(embedUrl)"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shrink-0">
                            Copiar
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-2">
                <span x-text="'Copiado!'" x-show="copied" x-cloak class="text-sm text-green-600"></span>
                <button x-on:click="show = false" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function shareDialog() {
    return {
        show: false,
        resource: null,
        activeTab: 'moodle',
        selectedLms: 'moodle',
        copied: false,

        tabs: [
            { id: 'moodle', label: 'Enlace Moodle' },
            { id: 'iframe', label: 'iframe' },
            { id: 'html', label: 'HTML' },
            { id: 'lms', label: 'LMS' },
            { id: 'qr', label: 'QR' },
        ],

        lmsInstructions: {
            moodle: { label: 'Moodle', steps: ['Inicia sesión en tu curso de Moodle.', 'Activa la edición (botón "Activar edición" arriba a la derecha).', 'Haz clic en "Agregar una actividad o recurso".', 'Selecciona "Página" o "URL".', 'Pega el siguiente enlace en el campo URL:', null, 'Guarda los cambios.'] },
            canvas: { label: 'Canvas', steps: ['Ve a tu curso en Canvas.', 'Haz clic en "Módulos" o "Páginas".', 'Crea un nuevo ítem o página.', 'En el editor de contenido, haz clic en "Insertar/Editar" y luego en "Embed".', 'Pega el código iframe:', null, 'Guarda los cambios.'] },
            classroom: { label: 'Google Classroom', steps: ['Ve a Google Classroom y selecciona tu clase.', 'Haz clic en "Trabajo en clase" y luego en "Crear" > "Material".', 'Pega el siguiente enlace en la descripción:', null, 'Haz clic en "Enviar".'] },
            teams: { label: 'Microsoft Teams', steps: ['Ve a tu equipo y canal en Microsoft Teams.', 'Haz clic en "Agregar una pestaña" (+).', 'Selecciona "Sitio web".', 'Pega el siguiente enlace:', null, 'Asigna un nombre a la pestaña y guarda.'] },
            schoology: { label: 'Schoology', steps: ['Ve a tu curso en Schoology.', 'Haz clic en "Agregar materiales" > "Agregar archivo/enlace/URL".', 'Pega el siguiente enlace:', null, 'Haz clic en "Agregar".'] },
        },

        get embedUrl() {
            return this.resource ? '{{ url('/embed') }}/' + this.resource.uuid : '';
        },
        get iframeCode() {
            return this.embedUrl ? '<iframe src="' + this.embedUrl + '" width="100%" height="600" frameborder="0" allowfullscreen></iframe>' : '';
        },
        get htmlCode() {
            return this.embedUrl ? '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;"><iframe src="' + this.embedUrl + '" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allowfullscreen></iframe></div>' : '';
        },
        get qrCodeUrl() {
            return this.embedUrl ? 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(this.embedUrl) : '';
        },

        open(resource) {
            this.resource = resource;
            this.activeTab = 'moodle';
            this.selectedLms = 'moodle';
            this.show = true;
        },

        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            } catch (e) {
                window.showToast('Error al copiar. Selecciona el texto manualmente.', 'error');
            }
        }
    }
}
</script>
