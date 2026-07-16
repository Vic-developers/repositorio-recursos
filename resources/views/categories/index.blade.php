@extends('layouts.app')

@section('title', 'Categorías')

@section('content')
<div x-data="categoriesManager()" x-init="loadCategories()">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Categorías</h1>
        <button x-on:click="openCreate()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva categoría
        </button>
    </div>

    <div x-show="loading" class="flex justify-center py-12">
        <div class="w-8 h-8 border-4 border-gray-200 border-t-indigo-500 rounded-full animate-spin"></div>
    </div>

    {{-- Tree --}}
    <div x-show="!loading" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        <template x-if="categories.length === 0">
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No hay categorías</h3>
                <p class="mt-1 text-sm text-gray-500">Crea categorías para clasificar tus recursos.</p>
            </div>
        </template>
        <template x-for="cat in categories" :key="cat.id">
            <div>
                <div class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50 group">
                    <button x-on:click="toggleExpand(cat.id)" class="p-0.5 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4 transition-transform" :class="expanded[cat.id] ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                         :style="'background-color: ' + (cat.color || '#e0e7ff')">
                        <span class="text-sm font-bold" :style="'color: ' + (cat.color || '#6366f1')" x-text="cat.name.charAt(0).toUpperCase()"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="text-sm font-medium text-gray-900" x-text="cat.name"></span>
                        <span class="text-xs text-gray-400 ml-2" x-text="'(' + cat.resources_count + ')'"></span>
                    </div>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all">
                        <button x-on:click="openCreate(cat.id)" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Agregar subcategoría">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                        <button x-on:click="openEdit(cat)" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button x-on:click="confirmDelete(cat)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Eliminar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                {{-- Children (recursive nested) --}}
                <template x-if="expanded[cat.id] && cat.children && cat.children.length">
                    <div class="border-l-2 border-indigo-100 ml-6">
                        <template x-for="child in cat.children" :key="child.id">
                            <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 group">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 ml-4"
                                     :style="'background-color: ' + (child.color || '#e0e7ff')">
                                    <span class="text-xs font-bold" :style="'color: ' + (child.color || '#6366f1')" x-text="child.name.charAt(0).toUpperCase()"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm text-gray-900" x-text="child.name"></span>
                                    <span class="text-xs text-gray-400 ml-2" x-text="'(' + child.resources_count + ')'"></span>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all">
                                    <button x-on:click="openEdit(child)" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Editar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button x-on:click="confirmDelete(child)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Eliminar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Create/Edit modal --}}
    <div x-show="showForm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" x-on:click="showForm = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold mb-4" x-text="isEditing ? 'Editar categoría' : 'Nueva categoría'"></h3>
            <form x-on:submit.prevent="saveCategory">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" x-model="form.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoría padre</label>
                    <select x-model="form.parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Ninguna (raíz)</option>
                        <template x-for="cat in allCategories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name" :disabled="isEditing && cat.id === editTarget?.id"></option>
                        </template>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <input type="color" x-model="form.color" class="w-full h-10 rounded-lg border border-gray-300 cursor-pointer">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" x-on:click="showForm = false" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700" x-text="isEditing ? 'Guardar' : 'Crear'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete modal --}}
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" x-on:click="showDeleteModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Eliminar categoría</h3>
            <p class="text-sm text-gray-600 mb-6">¿Estás seguro de eliminar <strong x-text="deleteTarget?.name"></strong>?</p>
            <div class="flex justify-end gap-3">
                <button x-on:click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">Cancelar</button>
                <button x-on:click="deleteCategory()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Eliminar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function categoriesManager() {
    return {
        categories: [],
        allCategories: [],
        loading: true,
        showForm: false,
        showDeleteModal: false,
        isEditing: false,
        editTarget: null,
        deleteTarget: null,
        expanded: {},
        form: { name: '', parent_id: '', color: '#6366f1' },

        async loadCategories() {
            try {
                const res = await fetch('/api/v1/categories?tree=true', {
                    headers: { 'Authorization': 'Bearer {{ session('api_token') ?? '' }}', 'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}', 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (json.success) {
                    this.categories = json.data;
                    this.loadAllCategories();
                }
            } catch (e) {
                console.error('Error loading categories:', e);
            } finally {
                this.loading = false;
            }
        },

        async loadAllCategories() {
            try {
                const res = await fetch('/api/v1/categories', {
                    headers: { 'Authorization': 'Bearer {{ session('api_token') ?? '' }}', 'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}', 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (json.success) this.allCategories = json.data;
            } catch (e) {
                console.error('Error loading all categories:', e);
            }
        },

        toggleExpand(id) {
            this.expanded[id] = !this.expanded[id];
        },

        openCreate(parentId) {
            this.isEditing = false;
            this.editTarget = null;
            this.form = { name: '', parent_id: parentId || '', color: '#6366f1' };
            this.showForm = true;
        },

        openEdit(cat) {
            this.isEditing = true;
            this.editTarget = cat;
            this.form = { name: cat.name, parent_id: cat.parent_id || '', color: cat.color || '#6366f1' };
            this.showForm = true;
        },

        async saveCategory() {
            if (!this.form.name.trim()) {
                window.showToast('El nombre es obligatorio.', 'error');
                return;
            }
            try {
                const url = this.isEditing ? '/api/v1/categories/' + this.editTarget.id : '/api/v1/categories';
                const method = this.isEditing ? 'PUT' : 'POST';
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer {{ session('api_token') ?? '' }}',
                        'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}', 'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                const json = await res.json();
                if (json.success) {
                    this.showForm = false;
                    window.showToast(this.isEditing ? 'Categoría actualizada correctamente.' : 'Categoría creada correctamente.', 'success');
                    this.loadCategories();
                } else {
                    window.showToast(json.message || 'Error al guardar la categoría.', 'error');
                }
            } catch (e) {
                console.error('Error saving category:', e);
                window.showToast('Error de conexión. Intenta de nuevo.', 'error');
            }
        },

        confirmDelete(cat) {
            this.deleteTarget = cat;
            this.showDeleteModal = true;
        },

        async deleteCategory() {
            if (!this.deleteTarget) return;
            try {
                const res = await fetch('/api/v1/categories/' + this.deleteTarget.id, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer {{ session('api_token') ?? '' }}',
                        'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}', 'Accept': 'application/json'
                    }
                });
                const json = await res.json();
                if (json.success) {
                    this.showDeleteModal = false;
                    this.deleteTarget = null;
                    window.showToast('Categoría eliminada correctamente.', 'success');
                    this.loadCategories();
                } else {
                    window.showToast(json.message || 'Error al eliminar la categoría.', 'error');
                }
            } catch (e) {
                console.error('Error deleting category:', e);
                window.showToast('Error de conexión. Intenta de nuevo.', 'error');
            }
        }
    }
}
</script>
@endpush
