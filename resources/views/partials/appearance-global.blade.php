{{-- Aparência (fonte, compacto, animações, cor de destaque) no app inteiro.
     Fica inline porque public/assets não vai no deploy automático. --}}
<style>
/*
 * Configurações de Aparência valendo no app inteiro (antes só em Configurações).
 * As variáveis e classes são aplicadas no <html> pelo script em partials/head.
 */

/* Tamanho da fonte: o Tailwind usa rem, então escalar o html escala tudo */
html { font-size: calc(100% * var(--fm-font-scale, 1)); }

/* Cor de destaque: controles nativos, seleção de texto e barra de rolagem */
:root { accent-color: var(--s-accent, #9333ea); }
::selection { background: rgba(var(--s-accent-rgb, 147, 51, 234), 0.25); }
* { scrollbar-color: rgba(var(--s-accent-rgb, 147, 51, 234), 0.45) transparent; }
input:focus-visible, select:focus-visible, textarea:focus-visible, button:focus-visible, a:focus-visible {
    outline-color: var(--s-accent, #9333ea);
}

/* Reduzir animações */
html.no-animations *,
html.no-animations *::before,
html.no-animations *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
}

/* Modo compacto: menos espaço nos cartões, formulários e listas */
html.compact-mode .p-8 { padding: 1.25rem !important; }
html.compact-mode .p-6 { padding: 1rem !important; }
html.compact-mode .p-5 { padding: 0.875rem !important; }
html.compact-mode .px-6 { padding-left: 1rem !important; padding-right: 1rem !important; }
html.compact-mode .py-6 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
html.compact-mode .py-5 { padding-top: 0.875rem !important; padding-bottom: 0.875rem !important; }
html.compact-mode .py-4 { padding-top: 0.625rem !important; padding-bottom: 0.625rem !important; }
html.compact-mode .py-3 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
html.compact-mode .gap-6 { gap: 1rem !important; }
html.compact-mode .gap-8 { gap: 1.25rem !important; }
html.compact-mode .gap-10 { gap: 1.5rem !important; }
html.compact-mode .space-y-6 > :not([hidden]) ~ :not([hidden]) { margin-top: 1rem !important; }
html.compact-mode .space-y-4 > :not([hidden]) ~ :not([hidden]) { margin-top: 0.625rem !important; }
html.compact-mode .mb-8 { margin-bottom: 1.25rem !important; }
html.compact-mode .mb-6 { margin-bottom: 1rem !important; }
</style>
