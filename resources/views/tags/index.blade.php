@extends('layouts.app')

@section('title', 'Etiquetas')

@section('content')
<div x-data="tagsManager()" x-init="loadTags()">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Etiquetas</h1>
        <button x-on:click="showCreateForm = true" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva etiqueta
        </button>
    </div>

    <div x-show="loading" class="flex justify-center py-12">
        <div class="w-8 h-8 border-4 border-gray-200 border-t-indigo-500 rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading" class="flex flex-wrap gap-3">
        <template x-for="tag in tags" :key="tag.id">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                <span x-text="tag.name"></span>
                <span class="text-xs text-gray-400" x-text="'(' + tag.resources_count + ')'"></span>
            </div>
        </template>
    </div>

    <div x-show="!loading && tags.length === 0" class="text-center py-16">
        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No hay etiquetas</h3>
        <p class="mt-1 text-sm text-gray-500">Crea etiquetas para organizar tus recursos.</p>
    </div>

    {{-- Create modal --}}
    <div x-show="showCreateForm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" x-on:click="showCreateForm = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold mb-4">Nueva etiqueta</h3>
            <form x-on:submit.prevent="createTag">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" x-model="createForm.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" x-on:click="showCreateForm = false" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function tagsManager() {
    return {
        tags: [],
        loading: true,
        showCreateForm: false,
        createForm: { name: '' },

        async loadTags() {
            try {
                const res = await fetch('/api/v1/tags', {
                    headers: { 'Authorization': 'Bearer {{ session('api_token') ?? '' }}', 'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}' }
                });
                const json = await res.json();
                if (json.success) this.tags = json.data;
            } catch (e) {
                console.error('Error loading tags:', e);
            } finally {
                this.loading = false;
            }
        },

        async createTag() {
            try {
                const res = await fetch('/api/v1/tags', {
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
                    this.createForm = { name: '' };
                    this.loadTags();
                }
            } catch (e) {
                console.error('Error creating tag:', e);
            }
        }
    }
}
</script>
@endpush
