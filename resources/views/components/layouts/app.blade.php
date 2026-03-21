<x-layouts.app.sidebar :title="$title ?? null">



  <flux:main>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    {{-- Removido Alpine.js duplicado, pois Livewire já injeta automaticamente --}}

    <script>
      window.Promise ||
        document.write(
          '<script src="https://cdn.jsdelivr.net/npm/promise-polyfill@8/dist/polyfill.min.js"><\/script>'
        )
      window.Promise ||
        document.write(
          '<script src="https://cdn.jsdelivr.net/npm/eligrey-classlist-js-polyfill@1.2.20171210/classList.min.js"><\/script>'
        )
      window.Promise ||
        document.write(
          '<script src="https://cdn.jsdelivr.net/npm/findindex_polyfill_mdn"><\/script>'
        )
    </script>


    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


    <link rel="stylesheet" href="{{ asset('assets/css/icon-category.css') }}">
    {{-- Overrides locais para ícones (garante herança de cor quando apropriado) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/icon-overrides.css') }}">
    @livewireStyles
    @stack('styles')

    {{-- ══════════════════════════════════════════════════════════════════
         SISTEMA DE LOADING GLOBAL v2 — FlowManager
         Cobre:
           • wire:loading AJAX (updates de componentes Livewire)
           • wire:navigate (navegação SPA entre páginas via sidebar)
         ══════════════════════════════════════════════════════════════════ --}}

    {{-- ① Barra superior — AJAX Livewire (component updates) --}}
    <div wire:loading.delay
         class="fixed top-0 left-0 right-0 z-[10000] pointer-events-none overflow-hidden"
         style="height:3px">
        <div class="h-full w-full fm-ajax-bar"></div>
    </div>

    {{-- ② Barra superior — Navegação wire:navigate (JS-driven) --}}
    <div id="fm-nav-bar"
         class="fixed top-0 left-0 z-[10001] pointer-events-none"
         style="height:3px;width:0%;opacity:0;
                background:linear-gradient(90deg,#f59e0b,#f97316,#ec4899,#8b5cf6,#3b82f6);
                transition:opacity .3s ease"></div>

    {{-- ③ Overlay de navegação (dim suave que bloqueia cliques duplos) --}}
    <div id="fm-nav-overlay"
         class="fixed inset-0 z-[9998] pointer-events-none"
         style="opacity:0;transition:opacity .25s ease;
                background:rgba(15,23,42,.22);backdrop-filter:blur(2px)"></div>

    {{-- ④ Toast flutuante de carregamento --}}
    <div id="fm-nav-toast"
         role="status" aria-live="polite"
         class="fixed bottom-6 right-6 z-[10002] pointer-events-none
                flex items-center gap-3 px-4 py-3 rounded-2xl
                bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl
                border border-slate-200/80 dark:border-slate-700/60
                shadow-[0_8px_40px_rgba(0,0,0,.14)] dark:shadow-[0_8px_40px_rgba(0,0,0,.45)]"
         style="opacity:0;transform:translateY(14px) scale(.96);
                transition:opacity .22s ease,transform .22s cubic-bezier(.4,0,.2,1)">
        {{-- Spinner --}}
        <div class="relative flex-shrink-0" style="width:20px;height:20px">
            <div class="absolute inset-0 rounded-full"
                 style="border:2.5px solid #f59e0b22"></div>
            <div class="absolute inset-0 rounded-full"
                 style="border:2.5px solid transparent;
                        border-top-color:#f59e0b;border-right-color:#f97316;
                        animation:fm-spin .75s linear infinite"></div>
            <div class="absolute rounded-full bg-amber-400"
                 style="inset:6px;animation:fm-dot-pulse 1.2s ease-in-out infinite"></div>
        </div>
        {{-- Texto --}}
        <div class="min-w-0">
            <p class="text-xs font-bold text-slate-700 dark:text-slate-200 leading-tight"
               id="fm-toast-label">Carregando página...</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 leading-tight">Aguarde um momento</p>
        </div>
        {{-- Barra de progresso interna --}}
        <div class="absolute bottom-0 left-0 right-0 h-0.5 overflow-hidden rounded-b-2xl">
            <div id="fm-toast-bar"
                 class="h-full"
                 style="width:0%;background:linear-gradient(90deg,#f59e0b,#f97316,#ec4899);
                        transition:width .35s ease-out"></div>
        </div>
    </div>

    <style>
        /* ── AJAX bar animação shimmer ── */
        .fm-ajax-bar {
            background: linear-gradient(90deg,#f59e0b,#f97316,#ec4899,#8b5cf6,#3b82f6,#f59e0b);
            background-size: 300% 100%;
            animation: fm-bar-shimmer 1.4s linear infinite;
        }

        /* ── Keyframes globais ── */
        @keyframes fm-bar-shimmer {
            0%   { background-position: 100% 0; }
            100% { background-position: -100% 0; }
        }
        @keyframes fm-spin {
            to { transform: rotate(360deg); }
        }
        @keyframes fm-dot-pulse {
            0%,100% { transform: scale(.7); opacity: .4; }
            50%      { transform: scale(1.1); opacity: 1; }
        }
        @keyframes fm-page-in {
            from { opacity: 0; transform: translateY(9px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Classe de entrada de página ── */
        .fm-page-in {
            animation: fm-page-in .38s cubic-bezier(.4,0,.2,1) both;
        }

        /* ── Cursor de espera durante navegação ── */
        body.fm-navigating,
        body.fm-navigating a,
        body.fm-navigating button { cursor: progress !important; }
    </style>

    <script>
    (function () {
        'use strict';
        var bar      = document.getElementById('fm-nav-bar');
        var overlay  = document.getElementById('fm-nav-overlay');
        var toast    = document.getElementById('fm-nav-toast');
        var toastBar = document.getElementById('fm-toast-bar');
        var pct = 0, ticker = null;

        /* ─── Inicia animação de navegação ─── */
        function navStart() {
            pct = 0;
            /* Reset barra sem transição */
            bar.style.transition = 'opacity .3s ease';
            bar.style.width  = '0%';
            bar.style.opacity = '1';

            /* Overlay + toast aparecem */
            overlay.style.opacity = '1';
            overlay.style.pointerEvents = 'all'; /* bloqueia cliques duplos */
            toast.style.opacity   = '1';
            toast.style.transform = 'translateY(0) scale(1)';
            toastBar.style.width  = '0%';

            document.body.classList.add('fm-navigating');

            /* Progresso incremental (simula NProgress) */
            clearInterval(ticker);
            ticker = setInterval(function () {
                pct = pct + (88 - pct) * 0.11;
                bar.style.transition = 'width .38s ease-out';
                bar.style.width = pct + '%';
                toastBar.style.width = pct + '%';
            }, 140);
        }

        /* ─── Finaliza animação de navegação ─── */
        function navEnd() {
            clearInterval(ticker);
            document.body.classList.remove('fm-navigating');

            /* Dispara para 100% e faz desaparecer */
            bar.style.transition = 'width .16s ease';
            bar.style.width = '100%';
            toastBar.style.width = '100%';

            overlay.style.opacity = '0';
            overlay.style.pointerEvents = 'none';
            toast.style.opacity   = '0';
            toast.style.transform = 'translateY(14px) scale(.96)';

            setTimeout(function () {
                bar.style.transition = 'opacity .28s ease';
                bar.style.opacity = '0';
                setTimeout(function () {
                    bar.style.width = '0%';
                    toastBar.style.width = '0%';
                }, 300);
            }, 160);

            /* ─── Anima o conteúdo da nova página ─── */
            setTimeout(function () {
                /* Busca o root div do componente Livewire recém-carregado */
                var page = document.querySelector('[class*="-page"]')
                         || document.querySelector('[wire\\:id]')
                         || document.querySelector('main > div');
                if (page) {
                    page.classList.remove('fm-page-in');
                    void page.offsetWidth; /* force reflow */
                    page.classList.add('fm-page-in');
                }
            }, 30);
        }

        document.addEventListener('livewire:navigating', navStart);
        document.addEventListener('livewire:navigated',  navEnd);
    })();
    </script>

    {{-- Conteúdo da página --}}
    {{ $slot }}

    <!-- Componente de Notificação de Download -->
    @include('components.download-notification')

    <!-- Sistema de Notificações Global -->
    @livewire('components.notifications')

    <!-- Sistema de Notificações de Achievements -->
    @livewire('components.achievement-notifier')

    @livewireScripts

    <!-- Sistema de Notificações Global -->
    <script>
        // Sistema de Notificações Global
        window.notifications = {
            // Função principal para mostrar notificações
            show: function(type, message, duration = 5000) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type, message, duration }
                }));
            },

            // Funções de conveniência
            success: function(message, duration = 5000) {
                this.show('success', message, duration);
            },

            error: function(message, duration = 7000) {
                this.show('error', message, duration);
            },

            warning: function(message, duration = 6000) {
                this.show('warning', message, duration);
            },

            info: function(message, duration = 5000) {
                this.show('info', message, duration);
            }
        };

        // Aliases globais para facilitar o uso
        window.notifySuccess = window.notifications.success.bind(window.notifications);
        window.notifyError = window.notifications.error.bind(window.notifications);
        window.notifyWarning = window.notifications.warning.bind(window.notifications);
        window.notifyInfo = window.notifications.info.bind(window.notifications);

        // Função global para redirecionamento após delay
        window.redirectAfterDelay = function(url, delay = 1500) {
            setTimeout(() => {
                window.location.href = url;
            }, delay);
        };

        // Escuta eventos do Livewire para redirecionamento
        document.addEventListener('DOMContentLoaded', function() {
            // Listener para redirecionamento após delay vindo do Livewire
            if (window.Livewire) {
                document.addEventListener('livewire:init', () => {
                    Livewire.on('redirect-after-delay', (event) => {
                        window.redirectAfterDelay(event.url, event.delay);
                    });
                });
            }
        });
    </script>

    <!-- Script para interceptar cliques em botões de exportar PDF -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Interceptar cliques em botões de exportar PDF
            document.addEventListener('click', function(e) {
                // Verificar se é um botão de exportar PDF
                if (e.target.closest('[wire\\:click*="exportPdf"]')) {
                    // Disparar evento imediatamente
                    window.dispatchEvent(new CustomEvent('download-started', {
                        detail: {
                            message: 'Preparando geração do PDF...'
                        }
                    }));
                }
            });
        });
    </script>

    @yield('scripts')
  </flux:main>
</x-layouts.app.sidebar>
