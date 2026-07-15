@extends('layouts.app')

@section('title', 'Carpetas')

@section('content')
<div x-data="foldersManager()" x-init="loadFolders()">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Carpetas</h1>
        <button x-on:click="showCreateForm = true" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva carpeta
        </button>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="flex justify-center py-12">
        <div class="w-8 h-8 border-4 border-gray-200 border-t-indigo-500 rounded-full animate-spin"></div>
    </div>

    {{-- Folders grid --}}
    <div x-show="!loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <template x-for="folder in folders" :key="folder.id">
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-indigo-200 transition-all group">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 truncate" x-text="folder.name"></h3>
                            <p class="text-xs text-gray-500" x-text="folder.resources_count + ' recursos'"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0 ml-2 opacity-0 group-hover:opacity-100 transition-all">
                        <button x-on:click="confirmEdit(folder)" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Editar carpeta">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button x-on:click="confirmDelete(folder)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Eliminar carpeta">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty state --}}
    <div x-show="!loading && folders.length === 0" class="text-center py-16">
        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No hay carpetas</h3>
        <p class="mt-1 text-sm text-gray-500">Crea carpetas para organizar tus recursos.</p>
    </div>

    {{-- Create modal --}}
    <div x-show="showCreateForm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" x-on:click="showCreateForm = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold mb-4">Nueva carpeta</h3>
            <form x-on:submit.prevent="createFolder">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" x-model="createForm.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea x-model="createForm.description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" x-on:click="showCreateForm = false" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Crear</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit modal --}}
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" x-on:click="showEditModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold mb-4">Editar carpeta</h3>
            <form x-on:submit.prevent="updateFolder">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" x-model="editForm.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea x-model="editForm.description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" x-on:click="showEditModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete confirmation modal --}}
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" x-on:click="showDeleteModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Eliminar carpeta</h3>
            <p class="text-sm text-gray-600 mb-1">¿Estás seguro de eliminar la carpeta <strong x-text="deleteTarget?.name"></strong>?</p>
            <p class="text-xs text-gray-500 mb-6" x-show="deleteTarget?.resources_count > 0">Tiene <span x-text="deleteTarget?.resources_count"></span> recurso(s). No se puede eliminar si contiene recursos.</p>
            <p class="text-xs text-gray-500 mb-6" x-show="!deleteTarget?.resources_count || deleteTarget?.resources_count == 0">Los recursos se moverán a la raíz.</p>
            <div class="flex justify-end gap-3">
                <button x-on:click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">Cancelar</button>
                <button x-on:click="deleteFolder()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Eliminar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function foldersManager() {
    return {
        folders: [],
        loading: true,
        showCreateForm: false,
        showEditModal: false,
        showDeleteModal: false,
        deleteTarget: null,
        editTarget: null,
        createForm: { name: '', description: '' },
        editForm: { name: '', description: '' },

        async loadFolders() {
            try {
                const res = await fetch('/api/v1/folders', {
                    headers: { 'Authorization': 'Bearer {{ session('api_token') ?? '' }}', 'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}' }
                });
                const json = await res.json();
                if (json.success) this.folders = json.data;
            } catch (e) {
                console.error('Error loading folders:', e);
            } finally {
                this.loading = false;
            }
        },

        async createFolder() {
            if (!this.createForm.name.trim()) {
                window.showToast('El nombre es obligatorio.', 'error');
                return;
            }
            try {
                const res = await fetch('/api/v1/folders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer {{ session('api_token') ?? '' }}',
                        'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}'
                    },
                    body: JSON.stringify(this.createForm)
                });
                const json = await res.json();
                if (json.success) {
                    this.showCreateForm = false;
                    this.createForm = { name: '', description: '' };
                    window.showToast('Carpeta creada correctamente.', 'success');
                    this.loadFolders();
                } else {
                    window.showToast(json.message || 'Error al crear la carpeta.', 'error');
                }
            } catch (e) {
                console.error('Error creating folder:', e);
                window.showToast('Error de conexión. Intenta de nuevo.', 'error');
            }
        },

        confirmEdit(folder) {
            this.editTarget = folder;
            this.editForm = { name: folder.name, description: folder.description || '' };
            this.showEditModal = true;
        },

        async updateFolder() {
            if (!this.editForm.name.trim()) {
                window.showToast('El nombre es obligatorio.', 'error');
                return;
            }
            try {
                const res = await fetch('/api/v1/folders/' + this.editTarget.id, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer {{ session('api_token') ?? '' }}',
                        'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}'
                    },
                    body: JSON.stringify(this.editForm)
                });
                const json = await res.json();
                if (json.success) {
                    this.showEditModal = false;
                    this.editTarget = null;
                    window.showToast('Carpeta actualizada correctamente.', 'success');
                    this.loadFolders();
                } else {
                    window.showToast(json.message || 'Error al editar la carpeta.', 'error');
                }
            } catch (e) {
                console.error('Error updating folder:', e);
                window.showToast('Error de conexión. Intenta de nuevo.', 'error');
            }
        },

        confirmDelete(folder) {
            this.deleteTarget = folder;
            this.showDeleteModal = true;
        },

        async deleteFolder() {
            if (!this.deleteTarget) return;
            try {
                const res = await fetch('/api/v1/folders/' + this.deleteTarget.id, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer {{ session('api_token') ?? '' }}',
                        'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}'
                    }
                });
                const json = await res.json();
                if (json.success) {
                    this.showDeleteModal = false;
                    this.deleteTarget = null;
                    window.showToast('Carpeta eliminada correctamente.', 'success');
                    this.loadFolders();
                } else {
                    window.showToast(json.message || 'Error al eliminar la carpeta.', 'error');
                }
            } catch (e) {
                console.error('Error deleting folder:', e);
                window.showToast('Error de conexión. Intenta de nuevo.', 'error');
            }
        }
    }
}
</script>
@endpush
