{{-- Monte seu kit: tipo → produtos → enfeites → cartão → revisar. Sem login até pedir; o kit fica salvo no aparelho. --}}
@php
    $logged = (bool) $client;
    $loginUrl = route('portal.login', ['redirect' => '/portal/catalogo/' . $ownerId . '/monte-seu-kit']);
    $registerUrl = route('portal.register', ['redirect' => '/portal/catalogo/' . $ownerId . '/monte-seu-kit', 'loja' => $ownerId]);
    $cfg = [
        'owner' => $ownerId, 'types' => $types, 'extras' => $extras, 'products' => $products, 'cats' => $cats,
        'groups' => collect($groups)->map(fn ($g, $k) => ['key' => $k, 'name' => $g[0], 'emoji' => $g[1]])->values(),
        'checkout' => $checkout, 'logged' => $logged, 'start' => $start, 'hasErrors' => $errors->any(),
        'old' => old('kit'),
    ];
    $msgIdeas = ['Feliz aniversário! 🎉', 'Eu te amo ❤️', 'Para a melhor mãe do mundo 💐', 'Obrigado por tudo!', 'Você merece esse carinho ✨', 'Feliz Natal! 🎄'];
@endphp
<x-portal-catalog-layout title="Monte seu kit" :store="$store" :owner-id="$ownerId"
    :cart-href="$logged ? route('portal.quotes.create') : route('portal.login', ['redirect' => 'cart'])">

@push('styles')
<style>
    .kb { max-width: 1240px; margin: 0 auto; padding: 14px 12px 120px; color: var(--ml-page-text, #222); --k1: #ec4899; --k2: #8b5cf6; }
    .kb * { box-sizing: border-box; }
    @media (min-width: 1800px) { .kb { max-width: 1520px; } }
    .kb-hero { position: relative; overflow: hidden; border-radius: 22px; padding: 22px 20px; color: #fff; background: linear-gradient(135deg, var(--k1), var(--k2)); box-shadow: 0 12px 30px -12px var(--k1); transition: background .4s; }
    .kb-hero h1 { margin: 0; font-size: clamp(22px, 4vw, 34px); font-weight: 800; letter-spacing: -.02em; }
    .kb-hero p { margin: 6px 0 0; max-width: 560px; font-size: 14px; opacity: .92; }
    .kb-hero-deco { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: clamp(54px, 9vw, 96px); line-height: 1; display: flex; gap: 6px; opacity: .95; pointer-events: none; }
    .kb-hero-deco span { display: inline-block; animation: kbBob 3.2s ease-in-out infinite; }
    .kb-hero-deco span:nth-child(2) { animation-delay: .6s; font-size: .7em; }
    .kb-hero-deco span:nth-child(3) { animation-delay: 1.2s; font-size: .55em; }
    @keyframes kbBob { 0%, 100% { transform: translateY(0) rotate(-4deg); } 50% { transform: translateY(-8px) rotate(4deg); } }
    @media (max-width: 640px) { .kb-hero { padding: 18px 16px 70px; } .kb-hero-deco { top: auto; bottom: 8px; right: 14px; transform: none; font-size: 46px; } }

    .kb-steps { display: flex; gap: 6px; margin: 14px 0; overflow-x: auto; scrollbar-width: none; padding: 2px; }
    .kb-steps::-webkit-scrollbar { display: none; }
    .kb-step { flex: 1 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 9px 14px; border-radius: 999px; border: 1px solid var(--ml-line, #e5e5e5); background: var(--ml-card, #fff); color: var(--ml-muted, #777); font-size: 13px; font-weight: 700; cursor: pointer; white-space: nowrap; transition: .2s; }
    .kb-step b { width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; background: var(--ml-chip, #f2f2f2); color: inherit; }
    .kb-step.on { color: #fff; border-color: transparent; background: linear-gradient(135deg, var(--k1), var(--k2)); box-shadow: 0 6px 16px -8px var(--k1); }
    .kb-step.on b { background: rgba(255,255,255,.25); }
    .kb-step.done { color: var(--ml-text, #333); }
    .kb-step.done b { background: #10b981; color: #fff; }
    .kb-step:disabled { opacity: .45; cursor: default; }

    .kb-grid { display: grid; gap: 16px; align-items: start; }
    @media (min-width: 1024px) { .kb-grid { grid-template-columns: minmax(0, 1fr) 360px; } }
    @media (min-width: 1600px) { .kb-grid { grid-template-columns: minmax(0, 1fr) 400px; } }
    .kb-box { background: var(--ml-card, #fff); border-radius: 20px; box-shadow: 0 1px 2px rgba(0,0,0,.06), 0 8px 24px -16px rgba(0,0,0,.18); padding: 18px; min-width: 0; }
    .kb-h2 { margin: 0 0 4px; font-size: 19px; font-weight: 800; color: var(--ml-text, #222); }
    .kb-sub { margin: 0 0 14px; font-size: 13px; color: var(--ml-muted, #777); }

    /* Tipos */
    .kb-types { display: grid; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); }
    .kb-type { position: relative; text-align: left; border: 0; padding: 0; border-radius: 18px; overflow: hidden; background: var(--ml-card, #fff); box-shadow: 0 0 0 1px var(--ml-line, #eee); cursor: pointer; transition: transform .2s, box-shadow .2s; display: flex; flex-direction: column; color: inherit; font: inherit; }
    .kb-type:hover { transform: translateY(-3px); box-shadow: 0 14px 30px -14px rgba(0,0,0,.35), 0 0 0 1px var(--ml-line, #eee); }
    .kb-type.on { box-shadow: 0 0 0 3px var(--k1), 0 14px 30px -14px var(--k1); }
    .kb-type-cover { position: relative; aspect-ratio: 16 / 10; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .kb-type-cover img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .kb-type-cover .em { font-size: 64px; filter: drop-shadow(0 8px 14px rgba(0,0,0,.25)); transition: transform .3s; }
    .kb-type:hover .em { transform: scale(1.1) rotate(-4deg); }
    .kb-type-cover .sp { position: absolute; font-size: 20px; opacity: .55; }
    .kb-type-badge { position: absolute; left: 10px; bottom: 10px; padding: 4px 10px; border-radius: 999px; background: rgba(255,255,255,.92); color: #222; font-size: 11px; font-weight: 800; }
    .kb-type-check { position: absolute; right: 10px; top: 10px; width: 28px; height: 28px; border-radius: 50%; background: #fff; color: var(--k1); display: flex; align-items: center; justify-content: center; font-size: 14px; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
    .kb-type-body { padding: 12px 14px 14px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
    .kb-type-body strong { font-size: 15px; color: var(--ml-text, #222); }
    .kb-type-body p { margin: 0; font-size: 12.5px; color: var(--ml-muted, #777); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .kb-type-meta { display: flex; flex-wrap: wrap; gap: 6px; margin-top: auto; padding-top: 4px; }
    .kb-pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 700; background: var(--ml-chip, #f2f2f2); color: var(--ml-text, #444); }
    .kb-pill.hot { background: color-mix(in srgb, var(--k1) 14%, transparent); color: var(--k1); }

    /* Barra de busca e filtros */
    .kb-tools { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 12px; }
    .kb-search { flex: 1 1 220px; position: relative; }
    .kb-search input { width: 100%; height: 44px; border-radius: 12px; border: 1px solid var(--ml-line, #e5e5e5); background: var(--ml-card, #fff); color: var(--ml-text, #222); padding: 0 12px 0 38px; font-size: 14px; outline: none; }
    .kb-search input:focus { border-color: var(--k1); box-shadow: 0 0 0 3px color-mix(in srgb, var(--k1) 18%, transparent); }
    .kb-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--ml-muted, #999); }
    .kb-chips { display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; padding: 2px 0 6px; margin-bottom: 8px; }
    .kb-chips::-webkit-scrollbar { display: none; }
    .kb-chip { flex-shrink: 0; padding: 7px 13px; border-radius: 999px; border: 1px solid var(--ml-line, #e5e5e5); background: var(--ml-card, #fff); color: var(--ml-text, #444); font-size: 12.5px; font-weight: 700; cursor: pointer; }
    .kb-chip.on { background: var(--ml-text, #222); color: var(--ml-card, #fff); border-color: transparent; }
    .kb-meter { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; padding: 10px 12px; border-radius: 14px; background: var(--ml-chip, #f5f5f5); font-size: 13px; color: var(--ml-text, #333); }
    .kb-meter-bar { flex: 1; height: 8px; border-radius: 99px; background: rgba(0,0,0,.08); overflow: hidden; }
    .kb-meter-bar i { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--k1), var(--k2)); transition: width .3s; }

    /* Produtos e enfeites */
    .kb-items { display: grid; gap: 10px; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
    @media (min-width: 1600px) { .kb-items { grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); } }
    .kb-item { position: relative; display: flex; flex-direction: column; border-radius: 16px; overflow: hidden; background: var(--ml-card, #fff); box-shadow: 0 0 0 1px var(--ml-line, #eee); transition: box-shadow .2s, transform .2s; min-width: 0; }
    .kb-item.on { box-shadow: 0 0 0 2px var(--k1), 0 10px 22px -14px var(--k1); }
    .kb-item-img { position: relative; aspect-ratio: 1; background: var(--ml-chip, #f5f5f5); display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .kb-item-img img { width: 100%; height: 100%; object-fit: cover; }
    .kb-item-img .em { font-size: 46px; }
    .kb-item-qty { position: absolute; left: 8px; top: 8px; min-width: 26px; height: 26px; padding: 0 7px; border-radius: 99px; background: linear-gradient(135deg, var(--k1), var(--k2)); color: #fff; font-size: 12px; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,.2); }
    .kb-item-body { padding: 9px 10px 10px; display: flex; flex-direction: column; gap: 4px; flex: 1; }
    .kb-item-name { font-size: 12.5px; line-height: 1.3; color: var(--ml-text, #333); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6em; }
    .kb-item-price { font-size: 15px; font-weight: 800; color: var(--ml-text, #222); }
    .kb-item-price s { font-size: 11px; font-weight: 500; color: var(--ml-muted, #999); margin-right: 4px; }
    .kb-add { margin-top: auto; height: 34px; border-radius: 10px; border: 0; font-weight: 800; font-size: 13px; cursor: pointer; background: color-mix(in srgb, var(--k1) 12%, transparent); color: var(--k1); }
    .kb-add:disabled { opacity: .45; cursor: default; }
    .kb-qty { margin-top: auto; display: flex; align-items: center; justify-content: space-between; height: 34px; border-radius: 10px; background: linear-gradient(135deg, var(--k1), var(--k2)); color: #fff; overflow: hidden; }
    .kb-qty button { width: 36px; height: 100%; border: 0; background: transparent; color: #fff; font-size: 16px; font-weight: 800; cursor: pointer; }
    .kb-qty button:disabled { opacity: .4; cursor: default; }
    .kb-qty span { font-weight: 800; font-size: 14px; }
    .kb-empty { padding: 26px 10px; text-align: center; color: var(--ml-muted, #888); font-size: 14px; }
    .kb-more { display: block; margin: 12px auto 0; padding: 9px 18px; border-radius: 999px; border: 1px solid var(--ml-line, #ddd); background: var(--ml-card, #fff); color: var(--ml-text, #333); font-weight: 700; font-size: 13px; cursor: pointer; }

    /* Cartão */
    .kb-card-note { position: relative; border-radius: 18px; padding: 18px; background: linear-gradient(135deg, color-mix(in srgb, var(--k1) 10%, var(--ml-card, #fff)), color-mix(in srgb, var(--k2) 10%, var(--ml-card, #fff))); border: 1px dashed color-mix(in srgb, var(--k1) 50%, transparent); }
    .kb-field { display: block; margin-bottom: 12px; }
    .kb-field span { display: block; font-size: 12px; font-weight: 700; color: var(--ml-muted, #666); margin-bottom: 5px; }
    .kb-field input, .kb-field textarea { width: 100%; border-radius: 12px; border: 1px solid var(--ml-line, #e5e5e5); background: var(--ml-card, #fff); color: var(--ml-text, #222); padding: 11px 12px; font-size: 14px; font-family: inherit; outline: none; resize: vertical; }
    .kb-field input:focus, .kb-field textarea:focus { border-color: var(--k1); box-shadow: 0 0 0 3px color-mix(in srgb, var(--k1) 18%, transparent); }
    .kb-two { display: grid; gap: 0 12px; grid-template-columns: 1fr 1fr; }
    .kb-ideas { display: flex; flex-wrap: wrap; gap: 6px; margin: -4px 0 12px; }
    .kb-ideas button { padding: 6px 11px; border-radius: 999px; border: 1px solid var(--ml-line, #e5e5e5); background: var(--ml-card, #fff); color: var(--ml-text, #444); font-size: 12px; cursor: pointer; }

    /* Palco do kit (prévia) */
    .kb-side { position: sticky; top: 76px; display: flex; flex-direction: column; gap: 12px; }
    .kb-stage { position: relative; height: 270px; border-radius: 20px; overflow: hidden; background: radial-gradient(120% 90% at 50% 100%, color-mix(in srgb, var(--k1) 34%, var(--ml-card, #fff)) 0%, color-mix(in srgb, var(--k2) 18%, var(--ml-card, #fff)) 55%, var(--ml-card, #fff) 100%); box-shadow: inset 0 0 0 1px var(--ml-line, #eee); }
    .kb-stage-base { position: absolute; left: 50%; bottom: 8px; transform: translateX(-50%); font-size: 104px; line-height: 1; filter: drop-shadow(0 10px 18px rgba(0,0,0,.25)); z-index: 2; }
    .kb-stage-base.img { width: 150px; height: 112px; border-radius: 18px; overflow: hidden; border: 4px solid #fff; font-size: 0; }
    .kb-stage-base.img img { width: 100%; height: 100%; object-fit: cover; }
    .kb-stage-p { position: absolute; width: 58px; height: 58px; margin: -29px 0 0 -29px; border-radius: 16px; overflow: hidden; background: #fff; border: 3px solid #fff; box-shadow: 0 6px 14px -4px rgba(0,0,0,.35); z-index: 3; transition: all .35s cubic-bezier(.2,.9,.3,1.3); display: flex; align-items: center; justify-content: center; }
    .kb-stage-p img { width: 100%; height: 100%; object-fit: cover; }
    .kb-stage-p b { position: absolute; right: -2px; bottom: -2px; min-width: 20px; height: 20px; border-radius: 10px; background: var(--k1); color: #fff; font-size: 10px; display: flex; align-items: center; justify-content: center; padding: 0 4px; }
    .kb-stage-e { position: absolute; font-size: 30px; z-index: 4; filter: drop-shadow(0 4px 6px rgba(0,0,0,.2)); animation: kbBob 3s ease-in-out infinite; transition: all .35s; }
    .kb-stage-e.img { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; border: 2px solid #fff; }
    .kb-stage-e.img img { width: 100%; height: 100%; object-fit: cover; }
    .kb-stage-hint { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; padding: 20px; text-align: center; font-size: 13px; font-weight: 600; color: var(--ml-muted, #555); }
    .kb-stage-name { position: absolute; left: 12px; top: 12px; right: 12px; display: flex; justify-content: space-between; gap: 8px; z-index: 5; }
    .kb-stage-name span { padding: 4px 10px; border-radius: 999px; background: rgba(255,255,255,.9); font-size: 11px; font-weight: 800; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .kb-lines { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
    .kb-lines li { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--ml-text, #333); }
    .kb-lines li .ic { width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px; overflow: hidden; background: var(--ml-chip, #f3f3f3); display: flex; align-items: center; justify-content: center; font-size: 17px; }
    .kb-lines li .ic img { width: 100%; height: 100%; object-fit: cover; }
    .kb-lines li .nm { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .kb-lines li .vl { font-weight: 700; white-space: nowrap; }
    .kb-lines li .rm { border: 0; background: none; color: var(--ml-muted, #aaa); cursor: pointer; padding: 4px; }
    .kb-total { display: flex; justify-content: space-between; align-items: baseline; padding-top: 12px; margin-top: 12px; border-top: 1px dashed var(--ml-line, #ddd); }
    .kb-total span { font-size: 13px; color: var(--ml-muted, #777); }
    .kb-total strong { font-size: 24px; font-weight: 800; color: var(--ml-text, #222); }
    .kb-sumrow { display: flex; justify-content: space-between; font-size: 13px; color: var(--ml-muted, #666); margin-top: 6px; }

    .kb-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 48px; padding: 0 22px; border-radius: 14px; border: 0; font-size: 15px; font-weight: 800; cursor: pointer; text-decoration: none; transition: transform .15s, opacity .2s; }
    .kb-btn:active { transform: scale(.98); }
    .kb-btn-main { color: #fff; background: linear-gradient(135deg, var(--k1), var(--k2)); box-shadow: 0 10px 22px -10px var(--k1); }
    .kb-btn-main:disabled { opacity: .5; cursor: default; box-shadow: none; }
    .kb-btn-soft { background: var(--ml-chip, #f2f2f2); color: var(--ml-text, #333); }
    .kb-nav { display: flex; gap: 10px; justify-content: space-between; margin-top: 18px; }
    .kb-warn { margin: 10px 0 0; padding: 10px 12px; border-radius: 12px; font-size: 13px; background: #fff7ed; color: #9a3412; }
    .kb-err { margin: 0 0 14px; padding: 12px 14px; border-radius: 14px; font-size: 14px; background: #fef2f2; color: #991b1b; display: flex; gap: 8px; }
    .kb-opts { display: grid; gap: 8px; }
    .kb-opt { display: flex; gap: 10px; align-items: flex-start; padding: 12px; border-radius: 14px; border: 1px solid var(--ml-line, #e5e5e5); cursor: pointer; background: var(--ml-card, #fff); color: var(--ml-text, #333); }
    .kb-opt input { margin-top: 3px; accent-color: var(--k1); }
    .kb-opt strong { display: block; font-size: 14px; }
    .kb-opt small { display: block; font-size: 12px; color: var(--ml-muted, #777); margin-top: 2px; }
    .kb-opt.on { border-color: var(--k1); box-shadow: 0 0 0 1px var(--k1); }
    .kb-h3 { margin: 16px 0 8px; font-size: 14px; font-weight: 800; color: var(--ml-text, #222); }

    /* Barra fixa no celular e iPad */
    .kb-bar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 55; padding: 10px 12px calc(10px + env(safe-area-inset-bottom)); background: var(--ml-card, #fff); box-shadow: 0 -6px 20px rgba(0,0,0,.12); display: flex; align-items: center; gap: 12px; }
    .kb-bar-sum { flex: 1; min-width: 0; }
    .kb-bar-sum small { display: block; font-size: 12px; color: var(--ml-muted, #777); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .kb-bar-sum strong { font-size: 19px; font-weight: 800; color: var(--ml-text, #222); }
    .kb-bar .kb-btn { height: 46px; padding: 0 18px; }
    .kb-bar-peek { width: 46px; height: 46px; flex-shrink: 0; border-radius: 14px; border: 0; background: var(--ml-chip, #f2f2f2); font-size: 22px; cursor: pointer; position: relative; }
    .kb-bar-peek b { position: absolute; right: -4px; top: -4px; min-width: 20px; height: 20px; border-radius: 10px; background: var(--k1); color: #fff; font-size: 11px; display: flex; align-items: center; justify-content: center; }
    @media (min-width: 1024px) { .kb-bar { display: none; } .kb { padding-bottom: 40px; } }
    @media (max-width: 1023px) { .kb-side-desk { display: none; } }
    .kb-sheet-bg { position: fixed; inset: 0; z-index: 70; background: rgba(15,23,42,.5); backdrop-filter: blur(3px); }
    .kb-sheet { position: fixed; left: 0; right: 0; bottom: 0; z-index: 71; max-height: 88vh; overflow-y: auto; border-radius: 24px 24px 0 0; background: var(--ml-page-bg, #f5f5f5); padding: 14px 12px calc(16px + env(safe-area-inset-bottom)); }
    .kb-sheet-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .kb-sheet-head b { font-size: 16px; color: var(--ml-text, #222); }
    .kb-x { width: 38px; height: 38px; border-radius: 12px; border: 0; background: var(--ml-card, #fff); color: var(--ml-text, #333); cursor: pointer; }
    .kb-toast { position: fixed; left: 50%; bottom: 92px; transform: translateX(-50%); z-index: 80; padding: 10px 16px; border-radius: 12px; background: #222; color: #fff; font-size: 13px; font-weight: 700; box-shadow: 0 6px 18px rgba(0,0,0,.3); }
    @media (min-width: 1024px) { .kb-toast { bottom: 28px; } }
</style>
@endpush

<div class="kb" x-data="kitBuilder(@js($cfg))" :style="`--k1:${look[3]};--k2:${look[4]}`" data-testid="kit-builder">
    <section class="kb-hero">
        <h1 class="sf-font">Monte seu kit</h1>
        <p>Escolha a cesta, coloque os produtos que quiser e finalize com balão, doces e um cartão. A gente monta e entrega pronto para presentear.</p>
        <div class="kb-hero-deco" aria-hidden="true"><span x-text="look[1]">🎁</span><span>🎈</span><span>🍫</span></div>
    </section>

    <nav class="kb-steps" aria-label="Etapas">
        <template x-for="(s, i) in steps" :key="i">
            <button type="button" class="kb-step" :class="{ on: step === i, done: step !== i && doneStep(i) }" :disabled="!canGo(i)" @click="go(i)" :data-testid="'kit-step-' + i">
                <b x-text="step !== i && doneStep(i) ? '✓' : i + 1"></b><span x-text="s"></span>
            </button>
        </template>
    </nav>

    @if($errors->any())
        <p class="kb-err" data-testid="kit-error"><i class="fas fa-triangle-exclamation" style="margin-top:2px"></i><span>{{ $errors->first() }}</span></p>
    @endif
    @if($otherStore)
        <p class="kb-err"><i class="fas fa-circle-info" style="margin-top:2px"></i><span>Você entrou com a conta de outra loja. Para pedir este kit, saia e entre com a conta desta loja.</span></p>
    @endif

    <div class="kb-grid">
        <div style="min-width:0">
            {{-- 1. Tipo --}}
            <section class="kb-box" x-show="step === 0" data-testid="kit-types">
                <h2 class="kb-h2">Qual kit você quer montar?</h2>
                <p class="kb-sub">Cada um tem um jeito e um tamanho. O preço da base inclui a embalagem e a montagem.</p>
                <div class="kb-types">
                    <template x-for="t in types" :key="t.id">
                        <button type="button" class="kb-type" :class="type && type.id === t.id && 'on'" @click="pickType(t)" :data-testid="'kit-type-' + t.id">
                            <div class="kb-type-cover" :style="`background:linear-gradient(135deg, ${t.look[3]}, ${t.look[4]})`">
                                <template x-if="t.img"><img :src="t.img" alt="" loading="lazy"></template>
                                <template x-if="!t.img">
                                    <span>
                                        <span class="em" x-text="t.look[1]"></span>
                                        <span class="sp" style="left:14%;top:16%">✨</span><span class="sp" style="right:12%;top:22%">💖</span><span class="sp" style="right:22%;bottom:16%">✨</span>
                                    </span>
                                </template>
                                <span class="kb-type-badge" x-text="t.look[1] + ' ' + t.look[0]"></span>
                                <span class="kb-type-check" x-show="type && type.id === t.id"><i class="fas fa-check"></i></span>
                            </div>
                            <div class="kb-type-body">
                                <strong x-text="t.name"></strong>
                                <p x-show="t.desc" x-text="t.desc"></p>
                                <div class="kb-type-meta">
                                    <span class="kb-pill hot" x-text="t.base > 0 ? 'Base ' + money(t.base) : 'Base grátis'"></span>
                                    <span class="kb-pill" x-text="(t.min === t.max ? t.min : t.min + ' a ' + t.max) + ' produtos'"></span>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </section>

            {{-- 2. Produtos --}}
            <section class="kb-box" x-show="step === 1" x-cloak data-testid="kit-products">
                <h2 class="kb-h2">Escolha os produtos</h2>
                <p class="kb-sub" x-text="type ? 'O ' + type.name + ' leva ' + (type.min === type.max ? type.min : 'de ' + type.min + ' a ' + type.max) + ' produtos.' : ''"></p>
                <div class="kb-meter">
                    <span><b x-text="count"></b> de <span x-text="type ? type.max : 0"></span></span>
                    <span class="kb-meter-bar"><i :style="`width:${type ? Math.min(100, count / type.max * 100) : 0}%`"></i></span>
                    <span x-show="type && count < type.min" style="color:#c2410c;font-weight:700" x-text="'Faltam ' + (type ? type.min - count : 0)"></span>
                    <span x-show="type && count >= type.min" style="color:#059669;font-weight:700">Pronto ✓</span>
                </div>
                <div class="kb-tools">
                    <label class="kb-search"><i class="fas fa-magnifying-glass"></i><input type="search" x-model.debounce.200ms="q" placeholder="Buscar produto" aria-label="Buscar produto" data-testid="kit-search"></label>
                </div>
                <div class="kb-chips" x-show="typeCats.length > 1">
                    <button type="button" class="kb-chip" :class="cat === 0 && 'on'" @click="cat = 0">Todos</button>
                    <button type="button" class="kb-chip" :class="cat === -1 && 'on'" @click="cat = -1" x-show="count > 0" x-text="'No kit (' + count + ')'"></button>
                    <template x-for="c in typeCats" :key="c.id"><button type="button" class="kb-chip" :class="cat === c.id && 'on'" @click="cat = c.id" x-text="c.name"></button></template>
                </div>
                <div class="kb-items">
                    <template x-for="p in shownProducts" :key="p.id">
                        <article class="kb-item" :class="qty(p.id) && 'on'" data-testid="kit-product">
                            <div class="kb-item-img">
                                <template x-if="p.img"><img :src="p.img" :alt="p.name" loading="lazy"></template>
                                <template x-if="!p.img"><span class="em">🎁</span></template>
                                <span class="kb-item-qty" x-show="qty(p.id)" x-text="qty(p.id) + 'x'"></span>
                            </div>
                            <div class="kb-item-body">
                                <div class="kb-item-name" x-text="p.name"></div>
                                <div class="kb-item-price"><s x-show="p.old" x-text="money(p.old)"></s><span x-text="money(p.price)"></span></div>
                                <template x-if="!qty(p.id)">
                                    <button type="button" class="kb-add" @click="addProduct(p)" :disabled="full">+ Colocar</button>
                                </template>
                                <template x-if="qty(p.id)">
                                    <div class="kb-qty">
                                        <button type="button" @click="setProduct(p, qty(p.id) - 1)" aria-label="Menos">−</button>
                                        <span x-text="qty(p.id)"></span>
                                        <button type="button" @click="setProduct(p, qty(p.id) + 1)" :disabled="full || qty(p.id) >= (p.stock || 1)" aria-label="Mais">+</button>
                                    </div>
                                </template>
                            </div>
                        </article>
                    </template>
                </div>
                <p class="kb-empty" x-show="!filteredProducts.length">Nenhum produto encontrado.</p>
                <button type="button" class="kb-more" x-show="filteredProducts.length > limit" @click="limit += 40" x-text="'Ver mais produtos (' + (filteredProducts.length - limit) + ')'"></button>
            </section>

            {{-- 3. Enfeites --}}
            <section class="kb-box" x-show="step === 2" x-cloak data-testid="kit-extras">
                <h2 class="kb-h2">Deixe ainda mais especial</h2>
                <p class="kb-sub">Balões, doces, flores e laços. É opcional: pode pular se quiser.</p>
                <div class="kb-chips" x-show="usedGroups.length > 1">
                    <button type="button" class="kb-chip" :class="grp === '' && 'on'" @click="grp = ''">Tudo</button>
                    <template x-for="g in usedGroups" :key="g.key"><button type="button" class="kb-chip" :class="grp === g.key && 'on'" @click="grp = g.key" x-text="g.emoji + ' ' + g.name"></button></template>
                </div>
                <template x-for="g in usedGroups" :key="g.key">
                    <div x-show="grp === '' || grp === g.key" style="margin-bottom:16px">
                        <h3 class="kb-h3" x-text="g.emoji + ' ' + g.name"></h3>
                        <div class="kb-items">
                            <template x-for="e in extras.filter(x => x.group === g.key)" :key="e.id">
                                <article class="kb-item" :class="eqty(e.id) && 'on'" data-testid="kit-extra">
                                    <div class="kb-item-img">
                                        <template x-if="e.img"><img :src="e.img" :alt="e.name" loading="lazy"></template>
                                        <template x-if="!e.img"><span class="em" x-text="g.emoji"></span></template>
                                        <span class="kb-item-qty" x-show="eqty(e.id)" x-text="eqty(e.id) + 'x'"></span>
                                    </div>
                                    <div class="kb-item-body">
                                        <div class="kb-item-name" x-text="e.name + (e.desc ? ' · ' + e.desc : '')"></div>
                                        <div class="kb-item-price" x-text="e.price > 0 ? '+ ' + money(e.price) : 'Grátis'"></div>
                                        <template x-if="!eqty(e.id)"><button type="button" class="kb-add" @click="setExtra(e, 1)">+ Colocar</button></template>
                                        <template x-if="eqty(e.id)">
                                            <div class="kb-qty">
                                                <button type="button" @click="setExtra(e, eqty(e.id) - 1)" aria-label="Menos">−</button>
                                                <span x-text="eqty(e.id)"></span>
                                                <button type="button" @click="setExtra(e, eqty(e.id) + 1)" :disabled="eqty(e.id) >= e.max" aria-label="Mais">+</button>
                                            </div>
                                        </template>
                                    </div>
                                </article>
                            </template>
                        </div>
                    </div>
                </template>
                <p class="kb-empty" x-show="!extras.length">Esta loja ainda não tem enfeites. Pode seguir.</p>
            </section>

            {{-- 4. Cartão --}}
            <section class="kb-box" x-show="step === 3" x-cloak data-testid="kit-message">
                <h2 class="kb-h2">Um recado no cartão 💌</h2>
                <p class="kb-sub">Opcional. A loja escreve no cartão do kit.</p>
                <div class="kb-card-note">
                    <div class="kb-two">
                        <label class="kb-field"><span>Para</span><input type="text" x-model="to" maxlength="60" placeholder="Nome de quem recebe"></label>
                        <label class="kb-field"><span>De</span><input type="text" x-model="from" maxlength="60" placeholder="Seu nome"></label>
                    </div>
                    <label class="kb-field"><span>Mensagem</span><textarea x-model="message" rows="4" maxlength="300" placeholder="Escreva sua mensagem" data-testid="kit-message-text"></textarea></label>
                    <div class="kb-ideas">
                        @foreach($msgIdeas as $idea)
                            <button type="button" @click="message = @js($idea)">{{ $idea }}</button>
                        @endforeach
                    </div>
                    <small style="font-size:12px;color:var(--ml-muted,#777)" x-text="message.length + ' de 300'"></small>
                </div>
            </section>

            {{-- 5. Revisar --}}
            <section class="kb-box" x-show="step === 4" x-cloak data-testid="kit-review">
                <h2 class="kb-h2">Confira seu kit</h2>
                <p class="kb-sub">Se estiver tudo certo, é só pedir. A loja confirma e combina a entrega.</p>
                <div class="lg:hidden" style="margin-bottom:14px">
                    @include('portal.partials.kit-stage')
                </div>
                @include('portal.partials.kit-lines')

                @if($logged)
                    <form method="POST" action="{{ route('portal.kit.store', ['userId' => $ownerId]) }}" x-ref="form" @submit="sending = true; markSent()">
                        @csrf
                        <input type="hidden" name="kit[type]" :value="type ? type.id : ''">
                        <template x-for="(n, id) in picked" :key="'p' + id"><input type="hidden" :name="'kit[products][' + id + ']'" :value="n"></template>
                        <template x-for="(n, id) in chosen" :key="'e' + id"><input type="hidden" :name="'kit[extras][' + id + ']'" :value="n"></template>
                        <input type="hidden" name="kit[message]" :value="message">
                        <input type="hidden" name="kit[to]" :value="to">
                        <input type="hidden" name="kit[from]" :value="from">

                        <template x-if="cfg.checkout.pickup || cfg.checkout.deliver">
                            <div>
                                <h3 class="kb-h3">Como receber</h3>
                                <div class="kb-opts">
                                    <label class="kb-opt" :class="delivery === 'retirada' && 'on'" x-show="cfg.checkout.pickup"><input type="radio" name="delivery_method" value="retirada" x-model="delivery"><span><strong>Retirar com a loja</strong><small>Sem custo de entrega</small></span></label>
                                    <label class="kb-opt" :class="delivery === 'entrega' && 'on'" x-show="cfg.checkout.deliver"><input type="radio" name="delivery_method" value="entrega" x-model="delivery"><span><strong>Receber em casa</strong><small x-text="shipText"></small></span></label>
                                </div>
                                <p class="kb-warn" x-show="delivery === 'entrega' && !cfg.checkout.hasAddress">Para entrega, complete seu endereço em <a href="{{ route('portal.profile') }}">Meus dados</a>.</p>
                            </div>
                        </template>

                        <template x-if="cfg.checkout.methods.length">
                            <div>
                                <h3 class="kb-h3">Pagamento</h3>
                                <div class="kb-opts">
                                    <label class="kb-opt" :class="checkout === 'pix' && 'on'" x-show="cfg.checkout.methods.includes('pix')"><input type="radio" name="checkout" value="pix" x-model="checkout"><span><strong>Pix</strong><small>Aprovação na hora</small></span></label>
                                    <label class="kb-opt" :class="checkout === 'cartao' && 'on'" x-show="cfg.checkout.methods.includes('cartao')"><input type="radio" name="checkout" value="cartao" x-model="checkout"><span><strong>Cartão</strong><small>Crédito ou débito pelo site</small></span></label>
                                    <label class="kb-opt" :class="checkout === 'boleto' && 'on'" x-show="cfg.checkout.methods.includes('boleto')"><input type="radio" name="checkout" value="boleto" x-model="checkout"><span><strong>Boleto</strong><small>Vence em 3 dias</small></span></label>
                                    <label class="kb-opt" :class="checkout === 'manual' && 'on'" x-show="cfg.checkout.manual"><input type="radio" name="checkout" value="manual" x-model="checkout"><span><strong>Combinar com a loja</strong><small>A loja confirma o kit e o pagamento com você</small></span></label>
                                </div>
                                <p class="kb-warn" x-show="checkout !== 'manual' && !cfg.checkout.hasCpf">Para pagar pelo site, cadastre seu CPF em <a href="{{ route('portal.profile') }}">Meus dados</a>.</p>
                            </div>
                        </template>

                        <div class="kb-nav">
                            <button type="button" class="kb-btn kb-btn-soft" @click="go(3)"><i class="fas fa-arrow-left"></i> Voltar</button>
                            <button type="submit" class="kb-btn kb-btn-main" :disabled="!ready || blocked || sending" data-testid="kit-submit">
                                <span x-show="!sending" x-text="checkout && checkout !== 'manual' ? 'Pedir e pagar' : 'Pedir este kit'"></span><span x-show="sending">Enviando…</span>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="kb-card-note" style="margin-top:16px;text-align:center">
                        <p style="margin:0 0 10px;font-size:14px;color:var(--ml-text,#333)"><b>Falta só entrar na sua conta.</b><br>Seu kit fica salvo neste aparelho.</p>
                        <a href="{{ $loginUrl }}" class="kb-btn kb-btn-main" data-testid="kit-login">Entrar para pedir</a>
                        <p style="margin:10px 0 0;font-size:13px;color:var(--ml-muted,#777)">Ainda não tem conta? <a href="{{ $registerUrl }}">Criar conta</a></p>
                    </div>
                @endif
            </section>

            <div class="kb-nav" x-show="step > 0 && step < 4" x-cloak>
                <button type="button" class="kb-btn kb-btn-soft" @click="go(step - 1)"><i class="fas fa-arrow-left"></i> Voltar</button>
                <button type="button" class="kb-btn kb-btn-main" @click="next()" :disabled="!canGo(step + 1)" data-testid="kit-next" x-text="nextLabel"></button>
            </div>
        </div>

        {{-- Prévia do kit (computador) --}}
        <aside class="kb-side kb-side-desk">
            <div class="kb-box">
                @include('portal.partials.kit-stage')
                <div style="margin-top:14px">@include('portal.partials.kit-lines')</div>
                <button type="button" class="kb-btn kb-btn-main" style="width:100%;margin-top:14px" x-show="step < 4" @click="next()" :disabled="!canGo(step + 1)" x-text="nextLabel"></button>
            </div>
        </aside>
    </div>

    {{-- Celular e iPad: barra fixa com o total e o kit --}}
    <div class="kb-bar" x-show="type" x-cloak>
        <button type="button" class="kb-bar-peek" @click="peek = true" aria-label="Ver meu kit"><span x-text="look[1]"></span><b x-show="count" x-text="count"></b></button>
        <div class="kb-bar-sum"><small x-text="type ? type.name : ''"></small><strong x-text="money(total)"></strong></div>
        <button type="button" class="kb-btn kb-btn-main" x-show="step < 4" @click="next()" :disabled="!canGo(step + 1)" x-text="step === 3 ? 'Revisar' : 'Continuar'"></button>
    </div>
    <div class="kb-sheet-bg" x-show="peek" x-cloak x-transition.opacity @click="peek = false"></div>
    <div class="kb-sheet" x-show="peek" x-cloak x-transition data-testid="kit-peek">
        <div class="kb-sheet-head"><b>Meu kit</b><button type="button" class="kb-x" @click="peek = false" aria-label="Fechar"><i class="fas fa-xmark"></i></button></div>
        <div class="kb-box">
            @include('portal.partials.kit-stage')
            <div style="margin-top:14px">@include('portal.partials.kit-lines')</div>
        </div>
    </div>

    <div class="kb-toast" x-show="toast" x-cloak x-transition.opacity x-text="toast"></div>
</div>

<script>
function kitBuilder(cfg) {
    const KEY = 'fm-kit-' + cfg.owner;
    const extraPos = [[14, 22], [84, 18], [10, 60], [88, 58], [24, 8], [74, 6], [50, 6], [6, 40], [94, 38], [32, 30], [68, 28]];
    return {
        cfg, types: cfg.types, extras: cfg.extras, products: cfg.products, cats: cfg.cats,
        steps: ['Kit', 'Produtos', 'Enfeites', 'Cartão', 'Revisar'],
        step: 0, type: null, picked: {}, chosen: {}, to: '', from: '', message: '',
        q: '', cat: 0, grp: '', limit: 40, peek: false, toast: '', sending: false,
        delivery: cfg.checkout.pickup ? 'retirada' : (cfg.checkout.deliver ? 'entrega' : ''),
        checkout: cfg.checkout.methods.length ? (cfg.checkout.manual ? 'manual' : cfg.checkout.methods[0]) : '',
        init() {
            let saved = null;
            try { saved = JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) {}
            // Pedido enviado e a pessoa voltou sem erro: começa um kit novo.
            if (saved && saved.sent && !cfg.hasErrors) { saved = null; this.clear(); }
            if (cfg.old && cfg.old.type) saved = { type: +cfg.old.type, picked: cfg.old.products || {}, chosen: cfg.old.extras || {}, to: cfg.old.to || '', from: cfg.old.from || '', message: cfg.old.message || '', step: 4 };
            if (saved) {
                const t = this.types.find(x => x.id === saved.type);
                if (t) {
                    this.type = t;
                    this.picked = this.clean(saved.picked, this.products);
                    this.chosen = this.clean(saved.chosen, this.extras);
                    this.to = saved.to || ''; this.from = saved.from || ''; this.message = saved.message || '';
                    this.step = Math.min(saved.step || 1, 4);
                    if (cfg.hasErrors) this.step = 4;
                }
            }
            if (cfg.start.tipo) { const t = this.types.find(x => x.id === cfg.start.tipo); if (t) { this.type = t; this.step = 1; } }
            if (!this.type && this.types.length === 1) { this.type = this.types[0]; }
            if (cfg.start.produto) {
                if (!this.type) this.type = this.types[0];
                const p = this.products.find(x => x.id === cfg.start.produto);
                if (p && this.allowed(p) && !this.picked[p.id]) { this.picked[p.id] = 1; this.step = Math.max(this.step, 1); }
            }
            this.$watch('step', () => { this.save(); this.limit = 40; window.scrollTo({ top: 0, behavior: 'smooth' }); });
            ['picked', 'chosen', 'to', 'from', 'message', 'type'].forEach(k => this.$watch(k, () => this.save()));
        },
        clean(map, list) {
            const out = {};
            Object.entries(map || {}).forEach(([id, n]) => { if (list.some(x => x.id === +id) && +n > 0) out[id] = +n; });
            return out;
        },
        save() {
            try { localStorage.setItem(KEY, JSON.stringify({ type: this.type?.id, picked: this.picked, chosen: this.chosen, to: this.to, from: this.from, message: this.message, step: this.step })); } catch (e) {}
        },
        markSent() { try { const s = JSON.parse(localStorage.getItem(KEY) || '{}'); s.sent = true; localStorage.setItem(KEY, JSON.stringify(s)); } catch (e) {} },
        clear() { try { localStorage.removeItem(KEY); } catch (e) {} },
        get look() { return this.type ? this.type.look : ['Kit', '🎁', 'fa-gift', '#ec4899', '#8b5cf6']; },
        allowed(p) { return !this.type || !this.type.cats.length || this.type.cats.includes(p.cat); },
        get typeCats() { const ids = new Set(this.products.filter(p => this.allowed(p)).map(p => p.cat)); return this.cats.filter(c => ids.has(c.id)); },
        get filteredProducts() {
            const words = this.q.trim().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').split(/\s+/).filter(Boolean);
            return this.products.filter(p => this.allowed(p)
                && (this.cat === 0 || (this.cat === -1 ? this.picked[p.id] : p.cat === this.cat))
                && words.every(w => p.name.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').includes(w)));
        },
        get shownProducts() { return this.filteredProducts.slice(0, this.limit); },
        get usedGroups() { return cfg.groups.filter(g => this.extras.some(e => e.group === g.key)); },
        qty(id) { return this.picked[id] || 0; },
        eqty(id) { return this.chosen[id] || 0; },
        get count() { return Object.values(this.picked).reduce((s, n) => s + n, 0); },
        get full() { return this.type && this.count >= this.type.max; },
        pickType(t) {
            const changed = !this.type || this.type.id !== t.id;
            this.type = t;
            if (changed) {
                // Produtos de fora das categorias deste kit e o que passa do limite saem.
                const keep = {}; let n = 0;
                Object.entries(this.picked).forEach(([id, q]) => { const p = this.products.find(x => x.id === +id); if (p && this.allowed(p) && n < t.max) { keep[id] = Math.min(q, t.max - n); n += keep[id]; } });
                this.picked = keep; this.cat = 0;
            }
            this.go(1);
        },
        addProduct(p) { this.setProduct(p, 1); this.flash('Colocado no kit'); },
        setProduct(p, n) {
            const others = this.count - this.qty(p.id);
            n = Math.max(0, Math.min(n, (p.stock || 1), (this.type?.max || 99) - others));
            const m = { ...this.picked };
            n ? m[p.id] = n : delete m[p.id];
            this.picked = m;
            if (this.type && this.count === this.type.max && n) this.flash('Kit completo! Pode seguir.');
        },
        setExtra(e, n) {
            n = Math.max(0, Math.min(n, e.max));
            const m = { ...this.chosen };
            n ? m[e.id] = n : delete m[e.id];
            this.chosen = m;
        },
        get pickedList() { return Object.entries(this.picked).map(([id, n]) => ({ p: this.products.find(x => x.id === +id), n })).filter(x => x.p); },
        get chosenList() { return Object.entries(this.chosen).map(([id, n]) => ({ e: this.extras.find(x => x.id === +id), n })).filter(x => x.e); },
        get productsTotal() { return this.pickedList.reduce((s, x) => s + x.p.price * x.n, 0); },
        get extrasTotal() { return this.chosenList.reduce((s, x) => s + x.e.price * x.n, 0); },
        get base() { return this.type ? this.type.base : 0; },
        get shipFee() {
            const s = cfg.checkout.ship;
            if (!s) return Number(cfg.checkout.fee) || 0;
            if (!s.ok) return 0;
            return s.freeAbove && this.productsTotal + this.base + this.extrasTotal + 0.009 >= s.freeAbove ? 0 : (Number(s.base) || 0);
        },
        get shipText() {
            const s = cfg.checkout.ship;
            if (s && !s.ok) return s.message;
            const f = this.shipFee;
            return (f > 0 ? 'Frete ' + this.money(f) : 'Frete grátis') + (s && s.place ? ' para ' + s.place : '');
        },
        get total() { return this.productsTotal + this.base + this.extrasTotal + (this.delivery === 'entrega' && this.step === 4 ? this.shipFee : 0); },
        get ready() { return this.type && this.count >= this.type.min && this.count <= this.type.max; },
        get blocked() {
            const c = cfg.checkout;
            return (this.checkout && this.checkout !== 'manual' && !c.hasCpf) || ((this.checkout === 'boleto' || this.delivery === 'entrega') && !c.hasAddress) || (this.delivery === 'entrega' && c.ship && !c.ship.ok);
        },
        doneStep(i) { return [!!this.type, this.ready, this.ready && this.step > 2, this.ready && this.step > 3, false][i]; },
        canGo(i) { return i <= 0 || (i === 1 ? !!this.type : (i <= 4 && this.ready)); },
        go(i) { if (this.canGo(i)) this.step = i; },
        next() {
            if (this.step === 1 && !this.ready) { this.flash(this.type ? 'Escolha pelo menos ' + this.type.min + ' produtos' : 'Escolha um kit'); return; }
            this.go(this.step + 1);
        },
        get nextLabel() { return ['Escolher produtos', 'Escolher enfeites', 'Escrever cartão', 'Revisar o kit', ''][this.step]; },
        // Posições no palco: produtos em leque acima da base, enfeites em volta.
        pPos(i) {
            const n = Math.max(1, this.pickedList.length), spread = Math.min(150, 30 + n * 22);
            const a = n === 1 ? 0 : (-spread / 2 + spread * i / (n - 1)) * Math.PI / 180;
            const row = n > 7 && i % 2 ? 0.7 : 1;
            return `left:${50 + Math.sin(a) * 34 * row}%;top:${66 - Math.cos(a) * 34 * row}%;transform:rotate(${a * 30}deg)`;
        },
        ePos(i) { const p = extraPos[i % extraPos.length]; return `left:${p[0]}%;top:${p[1]}%;margin-left:-15px;animation-delay:${(i * .37) % 2}s`; },
        get stageExtras() { const out = []; this.chosenList.forEach(x => { for (let k = 0; k < Math.min(x.n, 3); k++) out.push(x.e); }); return out.slice(0, 11); },
        groupEmoji(g) { return (cfg.groups.find(x => x.key === g) || {}).emoji || '✨'; },
        money(v) { return (Number(v) || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); },
        flash(m) { this.toast = m; clearTimeout(this._t); this._t = setTimeout(() => this.toast = '', 1800); },
    };
}
</script>
</x-portal-catalog-layout>
