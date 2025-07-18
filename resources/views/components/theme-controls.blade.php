<!-- Componente de Theme Toggle -->
<div class="fixed top-4 right-4 z-50">
    <button
        data-theme-toggle
        class="group relative flex items-center justify-center w-14 h-14 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110 border-2 border-white/50 dark:border-gray-600/50 backdrop-blur-sm"
        title="Alternar tema"
    >
        <!-- Ícone do sol (modo claro) -->
        <svg class="w-6 h-6 text-yellow-500 dark:text-gray-400 transition-all duration-300 absolute dark:opacity-0 dark:rotate-180 opacity-100 rotate-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>

        <!-- Ícone da lua (modo escuro) -->
        <svg class="w-6 h-6 text-blue-400 dark:text-blue-300 transition-all duration-300 absolute dark:opacity-100 dark:rotate-0 opacity-0 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
        </svg>

        <!-- Efeito de brilho -->
        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-white/20 via-transparent to-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

        <!-- Indicador animado -->
        <div class="absolute -top-1 -right-1 w-4 h-4 bg-gradient-to-r from-green-400 to-blue-500 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300 animate-pulse"></div>
    </button>
</div>

<!-- Componente de Status de Upload -->
<div id="upload-status" class="fixed bottom-4 right-4 z-50 transform translate-y-full transition-transform duration-300">
    <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-2xl p-4 text-white shadow-lg backdrop-blur-sm border border-white/20">
        <div class="flex items-center space-x-3">
            <div class="animate-spin">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
            <span class="font-medium">Processando arquivo...</span>
        </div>
    </div>
</div>

<!-- Componente de Estatísticas em Tempo Real -->
<div class="fixed bottom-4 left-4 z-40">
    <div class="bg-gradient-to-r from-white/90 to-blue-50/90 dark:from-gray-800/90 dark:to-blue-900/90 rounded-2xl p-4 shadow-lg backdrop-blur-sm border border-white/50 dark:border-gray-700/50">
        <div class="flex items-center space-x-4">
            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Uploads hoje</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">0</p>
            </div>
        </div>
    </div>
</div>

<!-- Tooltip Component -->
<div id="custom-tooltip" class="fixed z-50 px-3 py-2 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg shadow-lg opacity-0 pointer-events-none transition-all duration-200">
    <div class="absolute w-2 h-2 bg-gray-900 dark:bg-gray-700 transform rotate-45 -bottom-1 left-1/2 -translate-x-1/2"></div>
</div>

<script>
// Inicializar tooltips personalizados
document.addEventListener('DOMContentLoaded', function() {
    const tooltip = document.getElementById('custom-tooltip');

    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const text = this.getAttribute('data-tooltip');
            tooltip.textContent = text;
            tooltip.style.opacity = '1';

            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
        });

        element.addEventListener('mouseleave', function() {
            tooltip.style.opacity = '0';
        });
    });
});
</script>
