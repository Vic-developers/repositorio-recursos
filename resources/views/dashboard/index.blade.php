@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div x-data="dashboard()" x-init="load()">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <a href="{{ route('resources.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/></svg>
            Nuevo recurso
        </a>
    </div>

    <div x-show="loading" class="flex justify-center py-12">
        <div class="w-8 h-8 border-4 border-gray-200 border-t-indigo-500 rounded-full animate-spin"></div>
    </div>

    <template x-if="!loading">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

            {{-- Total recursos --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-indigo-200 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate" x-text="data.counts.total_resources"></p>
                        <p class="text-xs text-gray-500">Total recursos</p>
                    </div>
                </div>
            </div>

            {{-- Mis recursos --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-emerald-200 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate" x-text="data.counts.my_resources"></p>
                        <p class="text-xs text-gray-500">Mis recursos</p>
                    </div>
                </div>
            </div>

            {{-- Favoritos --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-rose-200 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate" x-text="data.counts.favorites"></p>
                        <p class="text-xs text-gray-500">Favoritos</p>
                    </div>
                </div>
            </div>

            {{-- Papelera --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-amber-200 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate" x-text="data.counts.trashed"></p>
                        <p class="text-xs text-gray-500">Papelera</p>
                    </div>
                </div>
            </div>

            {{-- Visitas --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-blue-200 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate" x-text="data.usage.total_views"></p>
                        <p class="text-xs text-gray-500">Visitas</p>
                    </div>
                </div>
            </div>

            {{-- Descargas --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-purple-200 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate" x-text="data.usage.total_downloads"></p>
                        <p class="text-xs text-gray-500">Descargas</p>
                    </div>
                </div>
            </div>

            {{-- Almacenamiento --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-cyan-200 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-cyan-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-cyan-600" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4z"/><path d="M3 10a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z"/><path d="M3 16a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate" x-text="data.usage.total_storage_display"></p>
                        <p class="text-xs text-gray-500">Almacenamiento</p>
                    </div>
                </div>
            </div>

            {{-- Tipos de recurso --}}
            <template x-for="item in data.by_type" :key="item.type">
                <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-indigo-200 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                             :class="{'bg-indigo-100': item.type === 'scorm', 'bg-emerald-100': item.type === 'h5p', 'bg-blue-100': item.type === 'pdf', 'bg-violet-100': item.type === 'video'}">
                            <template x-if="item.type === 'scorm'">
                                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                            </template>
                            <template x-if="item.type === 'h5p'">
                                <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                            </template>
                            <template x-if="item.type === 'pdf'">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                            </template>
                            <template x-if="item.type === 'video'">
                                <svg class="w-5 h-5 text-violet-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                            </template>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate capitalize" x-text="item.type"></p>
                            <p class="text-xs text-gray-500" x-text="item.count + ' recursos'"></p>
                        </div>
                    </div>
                </div>
            </template>

        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function dashboard() {
    return {
        data: { counts: {}, usage: {}, by_type: [], recent_resources: [], popular_resources: [] },
        loading: true,
        async load() {
            try {
                const res = await fetch('/api/v1/dashboard', {
                    headers: { 'Authorization': 'Bearer {{ session('api_token') ?? '' }}', 'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}', 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (json.success) this.data = json.data;
            } catch (e) {
                console.error('Error loading dashboard:', e);
            } finally { this.loading = false; }
        }
    }
}
</script>
@endpush
