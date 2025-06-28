<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        
            <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
