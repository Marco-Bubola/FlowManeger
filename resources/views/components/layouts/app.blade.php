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
    
    @livewireScripts
    
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