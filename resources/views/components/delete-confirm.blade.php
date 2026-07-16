<div x-data="deleteConfirm()" x-on:open-delete.window="open($event.detail)" x-show="show" x-cloak
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
             class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">

            <div class="px-6 pt-5 pb-4">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Eliminar recurso</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            ¿Estás seguro de que deseas eliminar <strong x-text="resource?.name"></strong>?
                            Se moverá a la papelera y podrás restaurarlo después.
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
                <button x-on:click="show = false" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">
                    Cancelar
                </button>
                <button x-on:click="deleteResource()" :disabled="deleting"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
                    <span x-show="!deleting">Eliminar</span>
                    <span x-show="deleting">Eliminando...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function deleteConfirm() {
    return {
        show: false,
        resource: null,
        deleting: false,

        open(resource) {
            this.resource = resource;
            this.show = true;
        },

        async deleteResource() {
            if (!this.resource) return;
            this.deleting = true;
            try {
                const response = await fetch('/api/v1/resources/' + this.resource.id + '/delete', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer {{ session('api_token') ?? '' }}', 'X-Tenant': '{{ session('tenant_slug') ?? 'principal' }}', 'Accept': 'application/json', 'Content-Type': 'application/json' }
                });
                const json = await response.json().catch(() => ({}));
                if (response.ok) {
                    this.show = false;
                    window.showToast('Recurso enviado a la papelera.', 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    window.showToast(json.message || 'Error al eliminar. Intenta de nuevo.', 'error');
                }
            } catch (e) {
                console.error('Error deleting resource:', e);
                window.showToast('Error de conexión. Intenta de nuevo.', 'error');
            } finally {
                this.deleting = false;
            }
        }
    }
}
</script>
