<!-- Sistema de Notificações Toast -->
<div id="toast-container" class="fixed top-20 right-4 z-50 space-y-2">
    <!-- Toast de sucesso -->
    <div id="success-toast" class="hidden transform translate-x-full transition-all duration-300 ease-in-out">
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 dark:from-green-600 dark:to-emerald-700 text-white rounded-2xl shadow-2xl p-4 border border-white/20 backdrop-blur-sm max-w-sm">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 bg-white/20 rounded-xl">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-sm">Sucesso!</p>
                    <p class="text-xs opacity-90" id="success-message">Operação realizada com sucesso</p>
                </div>
                <button onclick="hideToast('success-toast')" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast de erro -->
    <div id="error-toast" class="hidden transform translate-x-full transition-all duration-300 ease-in-out">
        <div class="bg-gradient-to-r from-red-500 to-pink-600 dark:from-red-600 dark:to-pink-700 text-white rounded-2xl shadow-2xl p-4 border border-white/20 backdrop-blur-sm max-w-sm">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 bg-white/20 rounded-xl">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-sm">Erro!</p>
                    <p class="text-xs opacity-90" id="error-message">Algo deu errado</p>
                </div>
                <button onclick="hideToast('error-toast')" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast de informação -->
    <div id="info-toast" class="hidden transform translate-x-full transition-all duration-300 ease-in-out">
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 dark:from-blue-600 dark:to-purple-700 text-white rounded-2xl shadow-2xl p-4 border border-white/20 backdrop-blur-sm max-w-sm">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 bg-white/20 rounded-xl">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-sm">Informação</p>
                    <p class="text-xs opacity-90" id="info-message">Aqui está uma informação importante</p>
                </div>
                <button onclick="hideToast('info-toast')" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast de carregamento -->
    <div id="loading-toast" class="hidden transform translate-x-full transition-all duration-300 ease-in-out">
        <div class="bg-gradient-to-r from-gray-700 to-gray-800 dark:from-gray-600 dark:to-gray-700 text-white rounded-2xl shadow-2xl p-4 border border-white/20 backdrop-blur-sm max-w-sm">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 bg-white/20 rounded-xl">
                    <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-sm">Processando...</p>
                    <p class="text-xs opacity-90" id="loading-message">Aguarde um momento</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Sistema de Toast notifications
function showToast(type, message, duration = 5000) {
    const toast = document.getElementById(`${type}-toast`);
    const messageElement = document.getElementById(`${type}-message`);

    if (toast && messageElement) {
        messageElement.textContent = message;
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 10);

        if (type !== 'loading') {
            setTimeout(() => {
                hideToast(`${type}-toast`);
            }, duration);
        }
    }
}

function hideToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 300);
    }
}

// Integração com Livewire
document.addEventListener('DOMContentLoaded', function() {
    // Escutar eventos do Livewire
    Livewire.on('file-uploaded', (message) => {
        showToast('success', message || 'Arquivo enviado com sucesso!');
    });

    Livewire.on('file-error', (message) => {
        showToast('error', message || 'Erro ao enviar arquivo');
    });

    Livewire.on('processing-file', (message) => {
        showToast('loading', message || 'Processando arquivo...');
    });

    Livewire.on('transactions-confirmed', (message) => {
        hideToast('loading-toast');
        showToast('success', message || 'Transações confirmadas com sucesso!');
    });

    // Flash messages do Laravel
    @if(session('success'))
        showToast('success', '{{ session('success') }}');
    @endif

    @if(session('error'))
        showToast('error', '{{ session('error') }}');
    @endif

    @if(session('info'))
        showToast('info', '{{ session('info') }}');
    @endif
});
</script>
