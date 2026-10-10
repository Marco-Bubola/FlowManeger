@props(['title' => 'Acesso ao Portal', 'heading' => null, 'subtitle' => null, 'store' => null])
@php
    // Loja do catálogo que o cliente abriu (cookie gravado pelo catálogo).
    if (! $store && ($storeId = (int) request()->cookie('portal_store'))) {
        $store = \App\Models\User::select('id', 'name')->find($storeId);
    }
    $storeName = $store?->name ?: config('app.name');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b0b12">
    <title>{{ $title }} · {{ $storeName }}</title>
    @include('partials.favicons')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Mesmo visual do login do app: fundo escuro, brilho nas cores da logo e card central. */
        :root {
            --pa-bg: #0b0b12;
            --pa-card: rgba(20, 20, 28, .86);
            --pa-line: rgba(255, 255, 255, .09);
            --pa-input: rgba(255, 255, 255, .045);
            --pa-text: #f8fafc;
            --pa-muted: #94a3b8;
            --pa-soft: #c4b5fd;
            --pa-grad: linear-gradient(90deg, #ec4899 0%, #a855f7 52%, #3b82f6 100%);
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            margin: 0; min-height: 100vh; min-height: 100dvh;
            font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size: 15px; line-height: 1.45;
            color: var(--pa-text); background: var(--pa-bg);
            display: flex; align-items: center; justify-content: center;
            padding: max(24px, env(safe-area-inset-top)) 16px max(24px, env(safe-area-inset-bottom));
            position: relative; overflow-x: hidden;
        }
        body::before, body::after {
            content: ''; position: fixed; width: 520px; height: 520px; border-radius: 50%;
            filter: blur(90px); opacity: .28; pointer-events: none; z-index: 0;
        }
        body::before { background: #ec4899; top: -260px; right: -200px; }
        body::after { background: #3b82f6; bottom: -280px; left: -220px; opacity: .22; }
        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }

        .pa-wrap { position: relative; z-index: 1; width: 100%; max-width: 420px; }
        .pa-brand { text-align: center; margin-bottom: 22px; }
        .pa-logo {
            width: 76px; height: 76px; margin: 0 auto 14px; border-radius: 22px; background: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 10px 40px rgba(168, 85, 247, .35), 0 0 0 1px rgba(255,255,255,.08);
        }
        .pa-logo img { width: 50px; height: 50px; object-fit: contain; display: block; }
        .pa-store { margin: 0; font-size: 1.45rem; font-weight: 900; letter-spacing: -.03em;
            background: var(--pa-grad); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .pa-kicker { margin: 4px 0 0; font-size: .7rem; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; color: var(--pa-muted); }

        .pa-card {
            background: var(--pa-card); border: 1px solid var(--pa-line); border-radius: 24px;
            padding: 28px 24px; box-shadow: 0 24px 60px rgba(0,0,0,.45);
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
        .pa-input-wrap > i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: .9rem; pointer-events: none; }
        .pa-input {
            width: 100%; height: 50px; padding: 0 16px 0 42px; border-radius: 14px;
            border: 1px solid var(--pa-line); background: var(--pa-input); color: var(--pa-text); font-size: 16px;
            outline: none; transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .pa-input::placeholder { color: #64748b; }
        .pa-input:focus { border-color: #a855f7; background: rgba(168,85,247,.06); box-shadow: 0 0 0 4px rgba(168,85,247,.16); }
        .pa-input[readonly] { opacity: .75; }
        .pa-input.pa-has-eye { padding-right: 48px; }
        .pa-input.is-invalid { border-color: rgba(244,63,94,.6); }
        .pa-eye { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; border: 0; border-radius: 10px;
            background: transparent; color: #94a3b8; cursor: pointer; }
        .pa-eye:hover { color: #fff; }
        .pa-error { margin: 6px 0 0; font-size: .78rem; color: #fda4af; }
        .pa-hint { margin: 6px 0 0; font-size: .78rem; color: var(--pa-muted); }
        .pa-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .pa-check { display: inline-flex; align-items: center; gap: 8px; font-size: .88rem; color: #cbd5e1; cursor: pointer; }
        .pa-check input { width: 18px; height: 18px; accent-color: #a855f7; }
        .pa-link { font-size: .85rem; font-weight: 700; color: var(--pa-soft); }
        .pa-link:hover { color: #fff; }
        .pa-grid-2 { display: grid; gap: 16px; }
        @media (min-width: 480px) { .pa-grid-2 { grid-template-columns: 1fr 1fr; } }

        .pa-btn {
            display: flex; width: 100%; height: 52px; align-items: center; justify-content: center; gap: 10px;
            border: 0; border-radius: 14px; cursor: pointer; font-weight: 800; font-size: 1rem; color: #fff;
            background: var(--pa-grad); box-shadow: 0 10px 30px rgba(168,85,247,.35);
            transition: transform .15s, filter .15s;
        }
        .pa-btn:hover { filter: brightness(1.08); transform: translateY(-1px); }
        .pa-btn:active { transform: translateY(0); }
        .pa-btn-ghost { background: transparent; border: 1px solid var(--pa-line); box-shadow: none; font-weight: 700; font-size: .95rem; height: 48px; }
        .pa-btn-ghost:hover { background: rgba(255,255,255,.05); }
        .pa-btn-google { background: #fff; color: #1f2937; box-shadow: none; font-weight: 700; font-size: .95rem; height: 48px; }

        .pa-divider { display: flex; align-items: center; gap: 12px; margin: 18px 0; color: #64748b;
            font-size: .68rem; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; }
        .pa-divider::before, .pa-divider::after { content: ''; flex: 1; height: 1px; background: var(--pa-line); }
        .pa-foot { margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--pa-line); text-align: center; display: grid; gap: 12px; }
        .pa-muted { color: var(--pa-muted); font-size: .88rem; margin: 0; }
        .pa-back { display: inline-flex; align-items: center; gap: 8px; justify-content: center; font-size: .85rem; font-weight: 600; color: var(--pa-muted); }
        .pa-back:hover { color: #fff; }
        .pa-copy { text-align: center; margin: 18px 0 0; font-size: .75rem; color: #475569; }

        .pa-steps { display: flex; align-items: center; gap: 8px; margin: -6px 0 22px; padding: 0; list-style: none; font-size: .75rem; font-weight: 700; color: var(--pa-muted); }
        .pa-steps li { display: flex; align-items: center; gap: 6px; }
        .pa-steps .pa-step-line { flex: 1; height: 1px; background: var(--pa-line); }
        .pa-steps .pa-dot { width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
            border: 1px solid rgba(255,255,255,.2); font-size: .7rem; }
        .pa-steps .is-on { color: #fff; }
        .pa-steps .is-on .pa-dot { border: 0; background: var(--pa-grad); }

        @media (min-width: 768px) {
            .pa-wrap { max-width: 440px; }
            .pa-card { padding: 34px 32px; }
        }
    </style>
</head>
<body>
    <main class="pa-wrap">
        <div class="pa-brand">
            <div class="pa-logo"><img src="{{ Vite::asset('resources/images/icons/logo-128.png') }}" alt="{{ $storeName }}"></div>
            <p class="pa-store">{{ $storeName }}</p>
            <p class="pa-kicker">Portal do cliente</p>
        </div>

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
