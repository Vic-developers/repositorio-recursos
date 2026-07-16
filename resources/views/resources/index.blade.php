@extends('layouts.app')

@section('title', 'Recursos Educativos')

@section('header-actions')
    <div class="flex-1 flex items-center gap-3" x-data="searchBar()">
        <div class="relative flex-1 max-w-md">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
            <input type="text" x-model="query"
                   x-on:input.debounce.300ms="search"
                   placeholder="Buscar recursos..."
                   class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-gray-50 focus:bg-white transition-colors">
        </div>
        <div x-show="query.length > 0" x-cloak>
            <button x-on:click="query = ''; search()" class="text-xs text-gray-500 hover:text-gray-700">Limpiar</button>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <select id="folderFilter" x-on:change="filterChanged()"
                class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
            <option value="">Todas las carpetas</option>
        </select>
        <select id="typeFilter" x-on:change="filterChanged()"
                class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
            <option value="">Todos los tipos</option>
            @foreach(['SCORM','H5P','PDF','Video','Image','Document','Link'] as $t)
                <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </div>
@endsection

@section('content')
<div class="flex gap-6" x-data="resourcesGrid()" x-init="init()">
    {{-- Folder tree sidebar --}}
    <aside class="w-60 shrink-0 hidden lg:block">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden sticky top-24 shadow-sm">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Carpetas</h3>
                <a href="{{ route('folders.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">Admin</a>
            </div>
            <div class="p-2 max-h-[calc(100vh-12rem)] overflow-y-auto">
                <button x-on:click="browseFolder('')"
                        class="w-full flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg transition-all duration-150 text-left"
                        :class="!currentFolderId ? 'bg-indigo-50 text-indigo-700 font-medium shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'">
                    <svg class="w-4 h-4 shrink-0" :class="!currentFolderId ? 'text-indigo-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"/><path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                    <span>Todos</span>
                </button>
                <template x-for="folder in folderTree" :key="folder.id">
                    <div>
                        <button x-on:click="browseFolder(folder.id)"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg transition-all duration-150 text-left mt-0.5"
                                :class="currentFolderId === folder.id ? 'bg-indigo-50 text-indigo-700 font-medium shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'">
                            <svg class="w-4 h-4 shrink-0" :class="currentFolderId === folder.id ? 'text-amber-500' : 'text-amber-400'" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                            <span class="truncate flex-1" x-text="folder.name"></span>
                            <span class="text-xs px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-500" x-text="folder.resources_count"></span>
                        </button>
                        <template x-if="folder.children && folder.children.length">
                            <div class="ml-3 border-l-2 border-indigo-100">
                                <template x-for="child in folder.children" :key="child.id">
                                    <button x-on:click="browseFolder(child.id)"
                                            class="w-full flex items-center gap-2 px-3 py-1.5 text-sm rounded-lg transition-all duration-150 text-left mt-0.5 ml-2"
                                            :class="currentFolderId === child.id ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'">
                                        <svg class="w-3.5 h-3.5 shrink-0 text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                                        <span class="truncate flex-1" x-text="child.name"></span>
                                        <span class="text-xs px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-500" x-text="child.resources_count"></span>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5">
                <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse" x-show="loading"></div>
                <p class="text-sm text-gray-500" x-text="totalCount > 0 ? totalCount + ' recurso' + (totalCount !== 1 ? 's' : '') : (loading ? 'Cargando...' : '')"></p>
                <template x-if="currentFolderName">
                    <span class="text-sm text-gray-400">en <span class="font-medium text-gray-700" x-text="currentFolderName"></span></span>
                </template>
            </div>
            <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-lg p-0.5 shadow-sm">
                <button x-on:click="viewMode = 'grid'; localStorage.setItem('viewMode', 'grid')" class="p-1.5 rounded-md transition-colors"
                        :class="viewMode === 'grid' ? 'bg-indigo-100 text-indigo-700 shadow-sm' : 'text-gray-400 hover:text-gray-600'">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.25 2A2.25 2.25 0 002 4.25v2.5A2.25 2.25 0 004.25 9h2.5A2.25 2.25 0 009 6.75v-2.5A2.25 2.25 0 006.75 2h-2.5zm0 9A2.25 2.25 0 002 13.25v2.5A2.25 2.25 0 004.25 18h2.5A2.25 2.25 0 009 15.75v-2.5A2.25 2.25 0 006.75 11h-2.5zm9-9A2.25 2.25 0 0011 4.25v2.5A2.25 2.25 0 0013.25 9h2.5A2.25 2.25 0 0018 6.75v-2.5A2.25 2.25 0 0015.75 2h-2.5zm0 9A2.25 2.25 0 0011 13.25v2.5A2.25 2.25 0 0013.25 18h2.5A2.25 2.25 0 0018 15.75v-2.5A2.25 2.25 0 0015.75 11h-2.5z" clip-rule="evenodd"/></svg>
                </button>
                <button x-on:click="viewMode = 'list'; localStorage.setItem('viewMode', 'list')" class="p-1.5 rounded-md transition-colors"
                        :class="viewMode === 'list' ? 'bg-indigo-100 text-indigo-700 shadow-sm' : 'text-gray-400 hover:text-gray-600'">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2 3.75A.75.75 0 012.75 3h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 3.75zm0 4.167a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75zm0 4.166a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75zm0 4.167a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75z" clip-rule="evenodd"/></svg>
                </button>
            </div>
        </div>

        {{-- Loading skeleton --}}
        <div x-show="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="i in 8" :key="i">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden animate-pulse">
                    <div class="h-36 bg-gray-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-200" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="h-3 w-16 bg-gray-200 rounded-full"></div>
                        <div class="h-4 w-3/4 bg-gray-200 rounded"></div>
                        <div class="h-3 w-full bg-gray-100 rounded"></div>
                        <div class="flex gap-3">
                            <div class="h-3 w-12 bg-gray-100 rounded"></div>
                            <div class="h-3 w-16 bg-gray-100 rounded"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Grid View --}}
        <div x-show="!loading && viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="resource in resources" :key="resource.id">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-indigo-200 transition-all duration-200 group"
                     draggable="true"
                     x-on:dragstart="dragStart(resource, $event)"
                     x-on:dragend="dragEnd()">
                    <a :href="'{{ url('/player') }}/' + resource.uuid" class="block">
                        <div class="h-36 flex items-center justify-center relative overflow-hidden"
                             :style="'background:' + bgGrad(resource.type)">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-white/90 shadow-sm" x-html="icon(resource.type)"></div>
                            <div class="absolute top-2 left-2">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full"
                                      :class="badgeClass(resource.type)"
                                      x-text="resource.type"></span>
                            </div>
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-black/40 text-white backdrop-blur-sm">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                                    <span x-text="resource.view_count"></span>
                                </span>
                            </div>
                        </div>
                    </a>
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <a :href="'{{ url('/player') }}/' + resource.uuid" class="text-sm font-semibold text-gray-900 truncate block group-hover:text-indigo-600 transition-colors" x-text="resource.name"></a>
                            </div>
                            <div class="relative shrink-0" x-data="{ open: false }">
                                <button x-on:click.stop="open = !open" class="p-1 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 opacity-0 group-hover:opacity-100 transition-all">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
                                </button>
                                <div x-show="open" x-on:click.outside="open = false" class="absolute right-0 top-8 w-52 bg-white rounded-xl shadow-lg border border-gray-200 py-1.5 z-50" x-cloak x-transition>
                                    <a :href="'{{ url('/player') }}/' + resource.uuid" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10z" clip-rule="evenodd"/></svg>
                                        Ver
                                    </a>
                                    <a :href="'{{ url('/embed') }}/' + resource.uuid" target="_blank" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>
                                        Copiar enlace Moodle
                                    </a>
                                    <button x-on:click="shareResource(resource); open = false" class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.504 2.132a.75.75 0 01.992 0l4.5 3.75a.75.75 0 01-.496 1.368h-2.76l-.001 8.25a.75.75 0 01-1.5 0l.001-8.25H5.5a.75.75 0 01-.496-1.368l4.5-3.75z" clip-rule="evenodd"/></svg>
                                        Compartir
                                    </button>
                                    <hr class="my-1 border-gray-100">
                                    <button x-on:click="confirmDelete(resource); open = false" class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c-.84 0-1.673.025-2.5.075V3.75c0-.69.56-1.25 1.25-1.25h2.5c.69 0 1.25.56 1.25 1.25v.325C11.673 4.025 10.84 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd"/></svg>
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500 line-clamp-2 leading-relaxed" x-text="resource.description || 'Sin descripción'"></p>
                        <div class="mt-3 flex items-center gap-3 text-xs text-gray-400">
                            <span x-show="resource.folder" class="flex items-center gap-1 bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                                <span x-text="resource.folder.name"></span>
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                                <span x-text="resource.file_size_display || '—'"></span>
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10z" clip-rule="evenodd"/></svg>
                                <span x-text="resource.view_count + ' vistas'"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- List View --}}
        <div x-show="!loading && viewMode === 'list'" class="space-y-2">
            <template x-for="resource in resources" :key="resource.id">
                <div class="bg-white rounded-lg border border-gray-200 px-4 py-3 flex items-center gap-4 hover:bg-gray-50 hover:border-gray-300 transition-all group">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm bg-white border border-gray-100" x-html="icon(resource.type)"></div>
                    <div class="flex-1 min-w-0">
                        <a :href="'{{ url('/player') }}/' + resource.uuid" class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate block transition-colors" x-text="resource.name"></a>
                        <p class="text-xs text-gray-500 truncate mt-0.5" x-text="resource.description || 'Sin descripción'"></p>
                    </div>
                    <span x-show="resource.folder" class="text-xs text-gray-400 flex items-center gap-1 shrink-0">
                        <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                        <span x-text="resource.folder.name"></span>
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full shrink-0" :class="badgeClass(resource.type)" x-text="resource.type"></span>
                    <span class="text-xs text-gray-400 shrink-0 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10z" clip-rule="evenodd"/></svg>
                        <span x-text="resource.view_count"></span>
                    </span>
                    <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-all">
                        <a :href="'{{ url('/player') }}/' + resource.uuid" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Ver">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10z" clip-rule="evenodd"/></svg>
                        </a>
                        <button x-on:click="confirmDelete(resource)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c-.84 0-1.673.025-2.5.075V3.75c0-.69.56-1.25 1.25-1.25h2.5c.69 0 1.25.56 1.25 1.25v.325C11.673 4.025 10.84 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty state --}}
        <div x-show="!loading && resources.length === 0" class="text-center py-20">
            <div class="w-20 h-20 mx-auto bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"/><path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">No hay recursos</h3>
            <p class="mt-1 text-sm text-gray-500 max-w-sm mx-auto">Aún no hay recursos en esta ubicación. Subí tu primer recurso para empezar.</p>
            <a href="{{ route('resources.create') }}" class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-sm hover:shadow-md">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/></svg>
                Subir recurso
            </a>
        </div>

        {{-- Pagination --}}
        <div x-show="!loading && lastPage > 1" class="mt-8 flex items-center justify-center gap-1.5">
            <button x-on:click="goToPage(currentPage - 1)" x-show="currentPage > 1"
                    class="px-3 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                Anterior
            </button>
            <template x-for="page in lastPage" :key="page">
                <button x-on:click="goToPage(page)"
                        class="w-9 h-9 text-sm rounded-lg transition-all"
                        :class="page === currentPage ? 'bg-indigo-600 text-white shadow-sm' : 'border border-gray-300 text-gray-700 hover:bg-gray-100'"
                        x-text="page"></button>
            </template>
            <button x-on:click="goToPage(currentPage + 1)" x-show="currentPage < lastPage"
                    class="px-3 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                Siguiente
            </button>
        </div>
    </div>
</div>

@include('components.share-dialog')
@include('components.delete-confirm')

{{-- Trash drop zone --}}
<div id="trash-zone" style="display:none"
     class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 transition-all duration-300"
     ondragover="event.preventDefault(); this.querySelector('div').className='bg-red-50 border-2 border-red-500 bg-red-100 rounded-2xl px-8 py-4 flex items-center gap-3 shadow-lg scale-110'"
     ondragleave="event.preventDefault(); this.querySelector('div').className='bg-red-50 border-2 border-dashed border-red-300 rounded-2xl px-8 py-4 flex items-center gap-3 shadow-lg'"
     ondrop="event.preventDefault(); dropOnTrash(event); this.style.display='none'">
    <div class="bg-red-50 border-2 border-dashed border-red-300 rounded-2xl px-8 py-4 flex items-center gap-3 shadow-lg">
        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
        <span class="text-sm font-medium text-red-700">Arrastra un recurso aquí para eliminar</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
const TYPE_STYLES = {
    SCORM: { bg: 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)', badge: 'bg-blue-100 text-blue-800', iconClr: 'text-blue-600' },
    H5P: { bg: 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)', badge: 'bg-emerald-100 text-emerald-800', iconClr: 'text-emerald-600' },
    PDF: { bg: 'linear-gradient(135deg, #fef2f2 0%, #fecaca 100%)', badge: 'bg-red-100 text-red-800', iconClr: 'text-red-600' },
    Video: { bg: 'linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%)', badge: 'bg-purple-100 text-purple-800', iconClr: 'text-purple-600' },
    Image: { bg: 'linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%)', badge: 'bg-pink-100 text-pink-800', iconClr: 'text-pink-600' },
    Document: { bg: 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)', badge: 'bg-amber-100 text-amber-800', iconClr: 'text-amber-600' },
    Link: { bg: 'linear-gradient(135deg, #ecfeff 0%, #cffafe 100%)', badge: 'bg-cyan-100 text-cyan-800', iconClr: 'text-cyan-600' },
};

const TYPE_ICONS = {
    SCORM: '<svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>',
    H5P: '<svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>',
    PDF: '<svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>',
    Video: '<svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>',
    Image: '<svg class="w-6 h-6 text-pink-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>',
    Document: '<svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>',
    Link: '<svg class="w-6 h-6 text-cyan-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>',
};

function resourcesGrid() {
    return {
        resources: [],
        folderTree: [],
        loading: true,
        viewMode: localStorage.getItem('viewMode') || 'grid',
        currentPage: 1,
        lastPage: 1,
        totalCount: 0,
        currentFolderId: '{{ request('folder_id') }}',
        currentFolderName: '',

        icon(type) { return TYPE_ICONS[type] || TYPE_ICONS.Document; },
        bgGrad(type) { return (TYPE_STYLES[type] || TYPE_STYLES.Document).bg; },
        badgeClass(type) { return (TYPE_STYLES[type] || TYPE_STYLES.Document).badge; },

        async init() {
            await this.loadFolderTree();
            await this.loadResources();
        },

        async loadFolderTree() {
            try {
                const res = await fetch('/api/v1/folders?tree=true', {
                    headers: { 'Authorization': 'Bearer {{ session('api_token') ?? '' }}', 'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}', 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (json.success) {
                    this.folderTree = json.data;
                    if (this.currentFolderId) {
                        const found = this.findFolder(this.folderTree, this.currentFolderId);
                        if (found) this.currentFolderName = found.name;
                    }
                }
            } catch (e) {
                console.error('Error loading folder tree:', e);
            }
        },

        findFolder(tree, id) {
            for (const f of tree) {
                if (f.id === id) return f;
                if (f.children) {
                    const found = this.findFolder(f.children, id);
                    if (found) return found;
                }
            }
            return null;
        },

        browseFolder(folderId) {
            const url = new URL(window.location);
            if (folderId) url.searchParams.set('folder_id', folderId);
            else url.searchParams.delete('folder_id');
            url.searchParams.delete('page');
            window.location = url.toString();
        },

        async loadResources() {
            this.loading = true;
            try {
                const params = new URLSearchParams(window.location.search);
                params.set('page', this.currentPage);
                const response = await fetch('/api/v1/resources?' + params.toString(), {
                    headers: { 'Authorization': 'Bearer {{ session('api_token') ?? '' }}', 'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}', 'Accept': 'application/json' }
                });
                const json = await response.json();
                if (json.success) {
                    this.resources = json.data.data;
                    this.currentPage = json.data.current_page;
                    this.lastPage = json.data.last_page;
                    this.totalCount = json.data.total;
                }
            } catch (e) {
                console.error('Error loading resources:', e);
            } finally {
                this.loading = false;
            }
        },

        goToPage(page) {
            this.currentPage = page;
            this.loadResources();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
}

function searchBar() {
    return {
        query: '{{ request('search') }}',
        async init() {
            await this.loadFolders();
        },
        async search() {
            if (this.query.length >= 2 || this.query.length === 0) {
                const url = new URL(window.location);
                if (this.query) url.searchParams.set('search', this.query);
                else url.searchParams.delete('search');
                url.searchParams.delete('page');
                window.location = url.toString();
            }
        },
        async loadFolders() {
            try {
                const res = await fetch('/api/v1/folders', {
                    headers: { 'Authorization': 'Bearer {{ session('api_token') ?? '' }}', 'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}', 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (json.success) {
                    const select = document.getElementById('folderFilter');
                    json.data.forEach(f => {
                        const opt = document.createElement('option');
                        opt.value = f.id;
                        opt.textContent = f.name;
                        if ('{{ request('folder_id') }}' === f.id) opt.selected = true;
                        select.appendChild(opt);
                    });
                }
            } catch (e) {
                console.error('Error loading folders:', e);
            }
        }
    }
}

function filterChanged() {
    const url = new URL(window.location);
    const folder = document.getElementById('folderFilter').value;
    const type = document.getElementById('typeFilter').value;
    if (folder) url.searchParams.set('folder_id', folder); else url.searchParams.delete('folder_id');
    if (type) url.searchParams.set('type', type); else url.searchParams.delete('type');
    url.searchParams.delete('page');
    window.location = url.toString();
}

function shareResource(resource) {
    const event = new CustomEvent('open-share', { detail: resource });
    window.dispatchEvent(event);
}

function confirmDelete(resource) {
    const event = new CustomEvent('open-delete', { detail: resource });
    window.dispatchEvent(event);
}

// Drag to trash
let draggedResource = null;

function dragStart(resource, event) {
    draggedResource = resource;
    event.dataTransfer.effectAllowed = 'move';
    // Show trash zone
    const zone = document.getElementById('trash-zone');
    if (zone) zone.removeAttribute('x-cloak');
}

function dragEnd() {
    draggedResource = null;
    const zone = document.getElementById('trash-zone');
    if (zone) zone.setAttribute('x-cloak', '');
}

function dropOnTrash(event) {
    if (draggedResource) {
        confirmDelete(draggedResource);
    }
    draggedResource = null;
    document.getElementById('trash-zone')?.setAttribute('x-cloak', '');
}

document.addEventListener('dragenter', function(e) {
    const zone = document.getElementById('trash-zone');
    if (zone && zone.getAttribute('x-cloak') !== null) {
        zone.removeAttribute('x-cloak');
    }
});
</script>
@endpush
