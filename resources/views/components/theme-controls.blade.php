<!-- Theme toggle removed as requested -->

<!-- Upload status removed as requested -->

<!-- Componente de Estatísticas em Tempo Real -->
<!-- Estatísticas removidas conforme solicitado -->

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
