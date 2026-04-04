<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
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

<!-- Color theme init: aplica variáveis de cor antes do render para evitar flash -->
<script>
(function() {
    try {
        var THEMES = {
            purple:  { a:'#9333ea', d:'#7c3aed', l:'#d8b4fe', f:'#faf5ff', r:'147,51,234' },
            indigo:  { a:'#4f46e5', d:'#3730a3', l:'#a5b4fc', f:'#eef2ff', r:'79,70,229' },
            blue:    { a:'#2563eb', d:'#1d4ed8', l:'#93c5fd', f:'#eff6ff', r:'37,99,235' },
            teal:    { a:'#0d9488', d:'#0f766e', l:'#5eead4', f:'#f0fdfa', r:'13,148,136' },
            emerald: { a:'#059669', d:'#047857', l:'#6ee7b7', f:'#ecfdf5', r:'5,150,105' },
            rose:    { a:'#e11d48', d:'#be123c', l:'#fda4af', f:'#fff1f2', r:'225,29,72' },
            orange:  { a:'#ea580c', d:'#c2410c', l:'#fdba74', f:'#fff7ed', r:'234,88,12' },
            amber:   { a:'#d97706', d:'#b45309', l:'#fcd34d', f:'#fffbeb', r:'217,119,6' }
        };
        var colorTheme = localStorage.getItem('flowmanager:color-theme');
        var customColor = localStorage.getItem('flowmanager:color-custom');
        var root = document.documentElement;
        if (colorTheme === 'custom' && customColor) {
            root.style.setProperty('--s-accent', customColor);
        } else {
            var t = THEMES[colorTheme] || THEMES.purple;
            root.style.setProperty('--s-accent', t.a);
            root.style.setProperty('--s-accent-dark', t.d);
            root.style.setProperty('--s-accent-light', t.l);
            root.style.setProperty('--s-accent-faint', t.f);
            root.style.setProperty('--s-accent-rgb', t.r);
        }
        var fontSize = localStorage.getItem('flowmanager:font-size');
        if (fontSize) root.style.setProperty('--s-font-size-factor', (fontSize / 100).toString());
        if (localStorage.getItem('flowmanager:compact') === '1') root.classList.add('compact-mode');
        if (localStorage.getItem('flowmanager:no-animations') === '1') root.classList.add('no-animations');
    } catch(e) {}
})();
</script>

<!-- Critical CSS: sidebar oculta no mobile ANTES do body renderizar (evita flash no F5) -->
<style>
    [x-cloak] { display: none !important; }
    /* Mobile e iPad portrait: oculta sidebar com mobile-sidebar-closed */
    @media (max-width: 1023.98px) {
        #modernSidebar.mobile-sidebar-closed {
            transform: translateX(-100%) !important;
            visibility: hidden;
        }
        #modernSidebar:not(.mobile-sidebar-closed) {
            transform: translateX(0) !important;
            visibility: visible;
        }
    }
    /* Desktop sem classe: sidebar sempre visível (override classe residual) */
    @media (min-width: 1366px) {
        body:not(.tablet-nav-tabbar):not(.tablet-nav-sidebar) #modernSidebar.mobile-sidebar-closed {
            transform: translateX(0) !important;
            visibility: visible !important;
        }
    }
    .mobile-action-sheet .mobile-sheet-panel { transform: translateY(100%); }
    .mobile-action-sheet .mobile-sheet-backdrop { opacity: 0; }
</style>

@stack('styles')

<!-- Sidebar & Tab Bar CSS (extraídos do inline) -->
<link rel="stylesheet" href="{{ asset('assets/css/responsive/sidebar.css') }}?v={{ filemtime(public_path('assets/css/responsive/sidebar.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/tabbar.css') }}?v={{ filemtime(public_path('assets/css/responsive/tabbar.css')) }}">

<!-- Responsive CSS separado por dispositivo -->
<link rel="stylesheet" href="{{ asset('assets/css/responsive/responsive-mobile.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/responsive-iphone.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/responsive-ipad.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/responsive-notebook.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/responsive-fullhd.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive/responsive-ultrawide.css') }}">
