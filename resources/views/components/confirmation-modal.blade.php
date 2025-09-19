@props(['id', 'title' => 'Confirmar ação', 'message' => 'Tem certeza que deseja continuar?', 'confirmText' => 'Confirmar', 'cancelText' => 'Cancelar', 'confirmAction' => '', 'confirmClass' => 'bg-red-600 hover:bg-red-700'])

<!-- Modal de Confirmação -->
<div id="{{ $id }}"
     class="fixed inset-0 z-50 hidden overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">

    <!-- Overlay com Desfoque Moderno -->
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black/20 dark:bg-black/40 backdrop-blur-sm transition-all duration-300" onclick="closeModal('{{ $id }}')"></div>

        <!-- Centralizador -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal com Glassmorphism -->
        <div class="relative inline-block align-bottom bg-white/95 dark:bg-zinc-800/95 backdrop-blur-xl rounded-3xl px-6 pt-6 pb-4 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white/20 dark:border-zinc-700/50">

            <!-- Gradiente de Fundo Sutil -->
            <div class="absolute inset-0 bg-gradient-to-br from-white/50 via-blue-50/30 to-indigo-50/30 dark:from-zinc-800/50 dark:via-zinc-700/30 dark:to-zinc-900/30 -z-10"></div>

            <!-- Ícone e Conteúdo -->
            <div class="sm:flex sm:items-start">
                <!-- Ícone Moderno -->
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-red-100 to-rose-100 dark:from-red-900/40 dark:to-rose-900/40 sm:mx-0 sm:h-14 sm:w-14 mb-4 sm:mb-0 shadow-lg border border-red-200/50 dark:border-red-800/50">
                    <i class="bi bi-exclamation-triangle-fill text-red-600 dark:text-red-400 text-2xl sm:text-xl"></i>
                </div>

                <!-- Conteúdo -->
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3" id="modal-title">
                        {{ $title }}
                    </h3>
                    <div class="mt-2">
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ $message }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botões Modernos -->
            <div class="mt-6 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                <!-- Botão Confirmar com Gradiente -->
                <button type="button"
                        onclick="{{ $confirmAction }}; closeModal('{{ $id }}')"
                        class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-lg px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-base font-bold text-white hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-all duration-300 backdrop-blur-sm">
                    <i class="bi bi-trash-fill mr-2"></i>
                    {{ $confirmText }}
                </button>

                <!-- Botão Cancelar com Glassmorphism -->
                <button type="button"
                        onclick="closeModal('{{ $id }}')"
                        class="mt-3 w-full inline-flex justify-center rounded-2xl border border-gray-200/50 dark:border-zinc-600/50 shadow-lg px-6 py-3 bg-white/80 dark:bg-zinc-700/80 backdrop-blur-sm text-base font-bold text-gray-700 dark:text-gray-300 hover:bg-white/90 dark:hover:bg-zinc-600/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-all duration-300">
                    <i class="bi bi-x-lg mr-2"></i>
                    {{ $cancelText }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Função para abrir modal
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// Função para fechar modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('[role="dialog"]');
        modals.forEach(modal => {
            if (!modal.classList.contains('hidden')) {
                closeModal(modal.id);
            }
        });
    }
});
</script>
