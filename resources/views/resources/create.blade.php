@extends('layouts.app')

@section('title', 'Subir recurso')

@section('content')
<div class="max-w-3xl mx-auto" x-data="uploadForm()">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Subir nuevo recurso</h1>
        <p class="text-sm text-gray-500 mt-1">Completa los campos para agregar un recurso educativo al repositorio.</p>
    </div>

    <form x-on:submit.prevent="submit" class="space-y-6">
        {{-- File upload zone --}}
        <div class="bg-white rounded-xl border-2 border-dashed border-gray-300 p-8 text-center"
             x-on:dragover.prevent="$el.classList.add('border-indigo-500', 'bg-indigo-50')"
             x-on:dragleave.prevent="$el.classList.remove('border-indigo-500', 'bg-indigo-50')"
             x-on:drop.prevent="handleDrop($event); $el.classList.remove('border-indigo-500', 'bg-indigo-50')">
            <template x-if="!file">
                <div>
                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="mt-4 text-sm text-gray-600">Arrastra y suelta tu archivo aquí, o</p>
                    <label class="mt-2 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 cursor-pointer">
                        Seleccionar archivo
                        <input type="file" x-on:change="handleFileSelect($event)" class="hidden" accept=".zip,.scorm,.h5p,.pdf,.mp4,.jpg,.png,.doc,.docx,.pptx,.xlsx">
                    </label>
                    <p class="mt-2 text-xs text-gray-400">SCORM (.zip), H5P, PDF, Video, Imagen, Documento — Máx. 500MB</p>
                </div>
            </template>
            <template x-if="file">
                <div class="flex items-center justify-between bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900" x-text="file.name"></p>
                            <p class="text-xs text-gray-500" x-text="(file.size / 1024 / 1024).toFixed(2) + ' MB'"></p>
                        </div>
                    </div>
                    <button type="button" x-on:click="file = null; uploading = false" class="p-1 text-gray-400 hover:text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
            <div x-show="uploadProgress > 0 && uploadProgress < 100" class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" :style="'width: ' + uploadProgress + '%'"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1" x-text="uploadProgress + '%'"></p>
            </div>
        </div>

        {{-- Name --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del recurso *</label>
            <input type="text" x-model="form.name" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        {{-- Type --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
            <select x-model="form.type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Seleccionar tipo...</option>
                <option value="SCORM">SCORM</option>
                <option value="H5P">H5P</option>
                <option value="PDF">PDF</option>
                <option value="Video">Video</option>
                <option value="Image">Imagen</option>
                <option value="Document">Documento</option>
                <option value="Link">Enlace</option>
                <option value="Other">Otro</option>
            </select>
        </div>

        {{-- Folder --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Carpeta</label>
            <select x-model="form.folder_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Sin carpeta (raíz)</option>
                <template x-for="folder in folders" :key="folder.id">
                    <option :value="folder.id" x-text="folder.name"></option>
                </template>
            </select>
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea x-model="form.description" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            {{-- Language --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Idioma</label>
                <select x-model="form.language" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="es">Español</option>
                    <option value="en">Inglés</option>
                    <option value="fr">Francés</option>
                    <option value="pt">Portugués</option>
                </select>
            </div>

            {{-- Level --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nivel educativo</label>
                <select x-model="form.level" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Seleccionar...</option>
                    <option value="Preescolar">Preescolar</option>
                    <option value="Primaria">Primaria</option>
                    <option value="Secundaria">Secundaria</option>
                    <option value="Bachillerato">Bachillerato</option>
                    <option value="Universidad">Universidad</option>
                    <option value="Posgrado">Posgrado</option>
                </select>
            </div>

            {{-- Area --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Área de conocimiento</label>
                <input type="text" x-model="form.area" placeholder="Ej: Matemáticas, Ciencias..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            {{-- Author --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Autor</label>
                <input type="text" x-model="form.author_name" placeholder="Nombre del autor"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        {{-- Estimated time --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tiempo estimado (minutos)</label>
            <input type="number" x-model="form.estimated_time_minutes" min="0"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        {{-- Buttons --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="{{ route('resources.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">
                Cancelar
            </a>
            <button type="submit" :disabled="submitting"
                    class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                <span x-show="!submitting">Subir recurso</span>
                <span x-show="submitting">Subiendo...</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function uploadForm() {
    return {
        file: null,
        uploading: false,
        uploadProgress: 0,
        submitting: false,
        folders: [],
        form: {
            name: '',
            type: '',
            description: '',
            language: 'es',
            level: '',
            area: '',
            author_name: '',
            estimated_time_minutes: 0,
            folder_id: '',
        },

        async init() {
            try {
                const res = await fetch('/api/v1/folders', {
                    headers: {
                        'Authorization': 'Bearer {{ session('api_token') ?? '' }}',
                        'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}'
                    }
                });
                const json = await res.json();
                if (json.success) {
                    this.folders = json.data;
                }
            } catch (e) {
                console.error('Error loading folders:', e);
            }
        },

        handleDrop(event) {
            this.file = event.dataTransfer.files[0];
        },

        handleFileSelect(event) {
            this.file = event.target.files[0];
        },

        async submit() {
            if (!this.file) {
                window.showToast('Selecciona un archivo para subir.', 'error');
                return;
            }
            if (!this.form.name || !this.form.type) {
                window.showToast('Completa el nombre y el tipo del recurso.', 'error');
                return;
            }

            this.submitting = true;
            const formData = new FormData();
            formData.append('file', this.file);
            formData.append('name', this.form.name);
            formData.append('type', this.form.type);
            formData.append('description', this.form.description);
            formData.append('language', this.form.language);
            formData.append('level', this.form.level);
            formData.append('area', this.form.area);
            formData.append('author_name', this.form.author_name);
            formData.append('estimated_time_minutes', this.form.estimated_time_minutes);
            if (this.form.folder_id) {
                formData.append('folder_id', this.form.folder_id);
            }

            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '/api/v1/resources');
                xhr.setRequestHeader('Authorization', 'Bearer {{ session('api_token') ?? '' }}');
                xhr.setRequestHeader('X-Tenant', '{{ session('tenant_slug') ?? 'principal' }}');

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                    }
                };

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        window.showToast('Recurso subido correctamente.', 'success');
                        setTimeout(() => { window.location.href = '{{ route('resources.index') }}'; }, 500);
                    } else {
                        window.showToast('Error al subir el recurso. Intenta de nuevo.', 'error');
                        this.submitting = false;
                    }
                };

                xhr.onerror = () => {
                    window.showToast('Error de conexión. Intenta de nuevo.', 'error');
                    this.submitting = false;
                };

                xhr.send(formData);
            } catch (e) {
                console.error('Upload error:', e);
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
