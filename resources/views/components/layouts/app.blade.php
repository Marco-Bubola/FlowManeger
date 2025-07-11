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
    @livewireStyles
    {{ $slot }}
    
    <!-- Componente de Notificação de Download -->
    @include('components.download-notification')
    
    <!-- Sistema de Notificações Global -->
    @livewire('components.notifications')
    
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