<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<!-- Theme initialization: aplica classe `dark` antes do carregamento do CSS para evitar flash -->
<script>
	(function() {
		try {
			var theme = localStorage.getItem('flowmanager:theme');
			if (!theme) {
				// segue preferência do sistema
				theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
			}
			if (theme === 'dark') {
				document.documentElement.classList.add('dark');
			} else {
				document.documentElement.classList.remove('dark');
			}
		} catch (e) {
			// falha silenciosa
			console.error('Theme init error', e);
		}
	})();
</script>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Flatpickr - Calendar Date Picker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
<link rel="stylesheet" href="{{ asset('assets/css/flatpickr-custom.css') }}">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

<!-- html2canvas - Para exportar cards como imagem -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<!-- Theme overrides CSS file (moved from inline to prevent visual rendering) -->
<link rel="stylesheet" href="{{ asset('assets/css/flow-theme.css') }}">

<!-- Critical CSS: sidebar oculta no mobile ANTES do body renderizar (evita flash no F5) -->
<style>
    [x-cloak] { display: none !important; }
    @media (max-width: 1024px) {
        #modernSidebar.mobile-sidebar-closed {
            transform: translateX(-100%) !important;
            visibility: hidden;
        }
        #modernSidebar:not(.mobile-sidebar-closed) {
            transform: translateX(0) !important;
            visibility: visible;
        }
    }
    .mobile-action-sheet .mobile-sheet-panel { transform: translateY(100%); }
    .mobile-action-sheet .mobile-sheet-backdrop { opacity: 0; }
</style>

@stack('styles')

<!-- Responsive CSS separado por dispositivo -->
<link rel="stylesheet" href="{{ asset('assets/css/responsive/responsive-mobile.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/responsive-iphone.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/responsive-ipad.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/responsive-notebook.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/responsive-fullhd.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/responsive-ultrawide.css') }}">
