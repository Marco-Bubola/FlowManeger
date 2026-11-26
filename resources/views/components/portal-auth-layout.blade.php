@props(['title' => 'Acesso ao Portal', 'heading' => null, 'subtitle' => null, 'store' => null])
@php
    // Loja do catálogo que o cliente abriu (cookie gravado pelo catálogo) ou ?loja= do link.
    $storeId = $store?->id ?: ((int) request()->query('loja') ?: (int) request()->cookie('portal_store'));
    $identity = $storeId ? \App\Services\Portal\StoreIdentity::for($storeId) : null;
    if ($storeId && ! $store) {
        $store = \App\Models\User::select('id', 'name')->find($storeId);
    }
    $storeName = ($identity['name'] ?? null) ?: ($store?->name ?: config('app.name'));
    $storeLogo = $identity['logo_url'] ?? null;
    // Vitrine › Tela de login: cores, cantos, fundo, Google e textos.
    $look = \App\Services\Portal\LoginLook::for($store ? (int) $store->id : null);
    $lookCss = \App\Services\Portal\LoginLook::css($look);
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $look['mode'] === 'escuro' ? '#0b0b12' : ($look['mode'] === 'cor' ? $look['c1'] : '#f6f7fb') }}">
    <title>{{ $title }} · {{ $storeName }}</title>
    @include('portal.partials.store-icons', ['owner' => $store?->id])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Mesmo visual do login do app: fundo escuro, brilho nas cores da logo e card central. */
        :root {
            @foreach($lookCss as $k => $v){{ $k }}: {!! $v !!}; @endforeach
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; min-height: 100%; background: var(--pa-bg); }
        body {
            margin: 0; min-height: 100vh; min-height: 100dvh;
            font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size: 15px; line-height: 1.45;
            color: var(--pa-text); background: transparent;
            display: flex; align-items: center; justify-content: center;
            padding: max(24px, env(safe-area-inset-top)) 16px max(24px, env(safe-area-inset-bottom));
            position: relative; overflow-x: hidden;
        }
        body::before, body::after {
            content: ''; position: fixed; width: 520px; height: 520px; border-radius: 50%;
            filter: blur(90px); opacity: var(--pa-glow); pointer-events: none; z-index: 0;
        }
        body::before { background: var(--pa-c1); top: -260px; right: -200px; }
        body::after { background: var(--pa-c2); bottom: -280px; left: -220px; }
        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }

        .pa-wrap { position: relative; z-index: 1; width: 100%; max-width: 420px; }
        .pa-brand { text-align: center; margin-bottom: 22px; }
        .pa-logo {
            width: 76px; height: 76px; margin: 0 auto 14px; border-radius: min(22px, var(--pa-rc)); background: #fff;
            display: flex; align-items: center; justify-content: center; overflow: hidden;
            box-shadow: 0 10px 40px var(--pa-shadow), 0 0 0 1px rgba(255,255,255,.08);
        }
        .pa-logo img { width: 50px; height: 50px; object-fit: contain; display: block; }
        .pa-logo.is-own { width: 96px; height: 96px; }
        .pa-logo.is-own img { width: 80px; height: 80px; }
        .pa-logo.is-free { width: auto; height: auto; max-width: 240px; background: none; box-shadow: none; border-radius: 0; }
        .pa-logo.is-free img { width: auto !important; height: auto !important; max-width: 240px; max-height: 110px; }
        .pa-store { margin: 0; font-size: 1.45rem; font-weight: 900; letter-spacing: -.03em;
            background: linear-gradient(90deg, var(--pa-c1), var(--pa-c2)); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .pa-kicker { margin: 4px 0 0; font-size: .7rem; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; color: var(--pa-muted); }

        .pa-card {
            background: var(--pa-card); border: 1px solid var(--pa-line); border-radius: var(--pa-rc);
            padding: 28px 24px; box-shadow: 0 24px 60px rgba(0,0,0,.22);
            backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
        }
        .pa-title { margin: 0; font-size: 1.5rem; font-weight: 900; letter-spacing: -.03em; }
        .pa-sub { margin: 6px 0 22px; color: var(--pa-muted); font-size: .92rem; }

        .pa-alert { display: flex; gap: 10px; align-items: flex-start; padding: 11px 13px; border-radius: 14px; font-size: .86rem; margin-bottom: 16px; }
        .pa-alert-ok { background: rgba(16,185,129,.12); border: 1px solid rgba(16,185,129,.3); color: #a7f3d0; }
        .pa-alert-err { background: rgba(244,63,94,.12); border: 1px solid rgba(244,63,94,.3); color: #fecdd3; }

        .pa-form { display: grid; gap: 16px; }
        .pa-label { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 7px;
            font-size: .7rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--pa-muted); }
        .pa-input-wrap { position: relative; }
        .pa-input-wrap > i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--pa-icon); font-size: .9rem; pointer-events: none; }
        .pa-input {
            width: 100%; height: 50px; padding: 0 16px 0 42px; border-radius: var(--pa-r);
            border: 1px solid var(--pa-line); background: var(--pa-input); color: var(--pa-text); font-size: 16px;
            outline: none; transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .pa-input::placeholder { color: var(--pa-icon); }
        .pa-input:focus { border-color: var(--pa-c1); box-shadow: 0 0 0 4px var(--pa-ring); }
        .pa-input[readonly] { opacity: .75; }
        .pa-input.pa-has-eye { padding-right: 48px; }
        .pa-input.is-invalid { border-color: rgba(244,63,94,.6); }
        .pa-eye { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; border: 0; border-radius: 10px;
            background: transparent; color: var(--pa-icon); cursor: pointer; }
        .pa-eye:hover { color: var(--pa-text); }
        .pa-error { margin: 6px 0 0; font-size: .78rem; color: #fda4af; }
        .pa-hint { margin: 6px 0 0; font-size: .78rem; color: var(--pa-muted); }
        .pa-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .pa-check { display: inline-flex; align-items: center; gap: 8px; font-size: .88rem; color: var(--pa-check); cursor: pointer; }
        .pa-check input { width: 18px; height: 18px; accent-color: var(--pa-c1); }
        .pa-link { font-size: .85rem; font-weight: 700; color: var(--pa-soft); }
        .pa-link:hover { filter: brightness(1.2); }
        .pa-grid-2 { display: grid; gap: 16px; }
        @media (min-width: 480px) { .pa-grid-2 { grid-template-columns: 1fr 1fr; } }

        .pa-btn {
            display: flex; width: 100%; height: 52px; align-items: center; justify-content: center; gap: 10px;
            border: 0; border-radius: var(--pa-r-btn); cursor: pointer; font-weight: 800; font-size: 1rem; color: #fff;
            background: var(--pa-grad); box-shadow: 0 10px 30px var(--pa-shadow);
            transition: transform .15s, filter .15s;
        }
        .pa-btn:hover { filter: brightness(1.08); transform: translateY(-1px); }
        .pa-btn:active { transform: translateY(0); }
        .pa-btn-ghost { background: transparent; color: var(--pa-text); border: 1px solid var(--pa-line); box-shadow: none; font-weight: 700; font-size: .95rem; height: 48px; }
        .pa-btn-ghost:hover { background: var(--pa-ring); }
        .pa-btn-google { background: #fff; color: #1f2937; box-shadow: none; font-weight: 700; font-size: .95rem; height: 48px; border: 1px solid rgba(15,23,42,.14); }

        .pa-divider { display: flex; align-items: center; gap: 12px; margin: 18px 0; color: var(--pa-icon);
            font-size: .68rem; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; }
        .pa-divider::before, .pa-divider::after { content: ''; flex: 1; height: 1px; background: var(--pa-line); }
        .pa-foot { margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--pa-line); text-align: center; display: grid; gap: 12px; }
        .pa-muted { color: var(--pa-muted); font-size: .88rem; margin: 0; }
        .pa-back { display: inline-flex; align-items: center; gap: 8px; justify-content: center; font-size: .85rem; font-weight: 600; color: var(--pa-muted); }
        .pa-back:hover { color: var(--pa-text); }
        .pa-copy { text-align: center; margin: 18px 0 0; font-size: .75rem; color: var(--pa-copy); }

        .pa-steps { display: flex; align-items: center; gap: 8px; margin: -6px 0 22px; padding: 0; list-style: none; font-size: .75rem; font-weight: 700; color: var(--pa-muted); }
        .pa-steps li { display: flex; align-items: center; gap: 6px; }
        .pa-steps .pa-step-line { flex: 1; height: 1px; background: var(--pa-line); }
        .pa-steps .pa-dot { width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
            border: 1px solid var(--pa-line); font-size: .7rem; }
        .pa-steps .is-on { color: var(--pa-text); }
        .pa-steps .is-on .pa-dot { color: #fff; }
        .pa-steps .is-on .pa-dot { border: 0; background: var(--pa-grad); }

        /* Fundo na cor da loja: nome e textos fora do quadro em branco. */
        .lk-cor .pa-store { background: none; color: #fff; }
        .lk-cor .pa-kicker { color: rgba(255,255,255,.85); }
        .lk-claro .pa-logo:not(.is-free) { box-shadow: 0 10px 30px rgba(15,23,42,.10), 0 0 0 1px rgba(15,23,42,.06); }
        .lk-claro .pa-card { box-shadow: 0 20px 50px rgba(15,23,42,.08); }
        .pa-tagline { margin: 8px auto 0; max-width: 340px; font-size: .9rem; color: var(--pa-muted); }
        .lk-cor .pa-tagline { color: rgba(255,255,255,.9); }

        /* Tela dividida (computador): logo e nome de um lado, quadro do outro. */
        @media (min-width: 1024px) {
            .lk-lado .pa-wrap { max-width: 980px; display: grid; grid-template-columns: 1fr 440px; gap: 64px; align-items: center; }
            .lk-lado .pa-brand { text-align: left; margin: 0; }
            .lk-lado .pa-logo { margin: 0 0 22px; }
            .lk-lado .pa-logo.is-own { width: 140px; height: 140px; border-radius: min(32px, var(--pa-rc)); }
            .lk-lado .pa-logo.is-own img { width: 116px; height: 116px; }
            .lk-lado .pa-logo.is-free { width: auto; height: auto; max-width: 360px; border-radius: 0; }
            .lk-lado .pa-logo.is-free img { max-width: 340px; max-height: 160px; }
            .lk-lado .pa-store { font-size: 2.6rem; line-height: 1.05; }
            .lk-lado .pa-kicker { margin-top: 10px; }
            .lk-lado .pa-tagline { margin: 14px 0 0; font-size: 1.05rem; }
            .lk-lado .pa-side { grid-column: 2; }
            .lk-lado .pa-copy { grid-column: 2; }
        }

        @media (min-width: 768px) {
            .pa-wrap { max-width: 440px; }
            .pa-card { padding: 34px 32px; }
        }
    </style>
</head>
<body class="lk-{{ $look['mode'] }} lk-{{ $look['layout'] }}" data-testid="portal-auth">
    <main class="pa-wrap">
        <div class="pa-brand">
            <div class="pa-logo {{ $storeLogo ? 'is-own' : '' }} {{ $storeLogo && $look['logo_box'] === 'solta' ? 'is-free' : '' }}" data-testid="auth-logo">
                <img src="{{ $storeLogo ?: Vite::asset('resources/images/icons/logo-128.png') }}" alt="{{ $storeName }}">
            </div>
            @if($look['show_name'] || ! $storeLogo)<p class="pa-store">{{ $storeName }}</p>@endif
            <p class="pa-kicker">Portal do cliente</p>
            @if(filled($identity['tagline'] ?? null) && $look['layout'] === 'lado')<p class="pa-tagline">{{ $identity['tagline'] }}</p>@endif
        </div>

        <div class="pa-side">
        <section class="pa-card">
            @if($heading)<h1 class="pa-title">{{ $heading }}</h1>@endif
            @if($subtitle)<p class="pa-sub">{{ $subtitle }}</p>@endif

            @if(session('success'))
                <div class="pa-alert pa-alert-ok"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>
            @endif
            @if(session('error'))
                <div class="pa-alert pa-alert-err"><i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span></div>
            @endif

            {{ $slot }}
        </section>

        @isset($after){{ $after }}@endisset
        </div>

        <p class="pa-copy"><i class="fas fa-lock"></i> Acesso seguro · {{ date('Y') }}</p>
    </main>

    <script>
    function paTogglePass(btn) {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('i');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.classList.toggle('fa-eye', !show);
        icon.classList.toggle('fa-eye-slash', show);
    }
    </script>
</body>
</html>
