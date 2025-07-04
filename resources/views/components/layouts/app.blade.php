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
    @livewireScripts
    @yield('scripts')
  </flux:main>
</x-layouts.app.sidebar>