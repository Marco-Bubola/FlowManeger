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

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<!-- Theme overrides CSS file (moved from inline to prevent visual rendering) -->
<link rel="stylesheet" href="{{ asset('assets/css/flow-theme.css') }}">

@stack('styles')
