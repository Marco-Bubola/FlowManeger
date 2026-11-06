{{-- Tela de abertura com a logo animada (opcional). Aparece uma vez por visita (sessionStorage), dura ~1,2 s,
     some ao tocar e não aparece para quem pediu menos movimento. Params: $splash (logo|cortina|circulo),
     $splashKey, $splashLogo, $splashName, $splashSub, $splashBg, $splashInk, $splashAcc. --}}
@php
    $spMode = in_array($splash ?? '', ['logo', 'cortina', 'circulo'], true) ? $splash : null;
@endphp
@if($spMode)
<div id="fm-splash" class="fm-sp fm-spm-{{ $spMode }}" aria-hidden="true"
     style="--sp-bg: {{ $splashBg ?? '#0f172a' }}; --sp-ink: {{ $splashInk ?? '#ffffff' }}; --sp-acc: {{ $splashAcc ?? '#a855f7' }}">
    <div class="fm-sp-in">
        @if(! empty($splashLogo))<span class="fm-sp-logo"><img src="{{ $splashLogo }}" alt=""></span>@endif
        @if(! empty($splashName))<b class="fm-sp-name">{{ $splashName }}</b>@endif
        @if(! empty($splashSub))<small class="fm-sp-sub">{{ $splashSub }}</small>@endif
        <i class="fm-sp-bar"></i>
    </div>
</div>
<style>
    .fm-sp { position: fixed; inset: 0; z-index: 2147483000; display: none;
    }
    .fm-sp.fm-sp-on { display: flex; align-items: center; justify-content: center; background: var(--sp-bg); color: var(--sp-ink); font-family: inherit; cursor: pointer; }
    .fm-sp-in { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 24px; text-align: center; }
    .fm-sp-logo { width: 92px; height: 92px; border-radius: 26px; background: #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 18px 50px -12px rgba(0,0,0,.45); overflow: hidden; }
    .fm-sp-logo img { max-width: 76%; max-height: 76%; object-fit: contain; }
    .fm-sp-name { font-size: clamp(22px, 5vw, 34px); font-weight: 800; letter-spacing: -.02em; line-height: 1.1; max-width: 90vw; }
    .fm-sp-sub { font-size: 14px; opacity: .75; max-width: 80vw; }
    .fm-sp-bar { display: block; width: 120px; height: 3px; border-radius: 3px; background: color-mix(in srgb, var(--sp-ink) 18%, transparent); overflow: hidden; position: relative; }
    .fm-sp-bar::after { content: ''; position: absolute; inset: 0; background: var(--sp-acc); transform: scaleX(0); transform-origin: left; animation: fm-sp-load 1s .15s ease forwards; }
    @keyframes fm-sp-load { to { transform: none; } }
    /* Logo: pula e o nome sobe */
    .fm-sp-logo { animation: fm-sp-pop .7s cubic-bezier(.2, 1.4, .4, 1) both; }
    .fm-sp-name { animation: fm-sp-up .6s .25s ease both; }
    .fm-sp-sub { animation: fm-sp-up .6s .4s ease both; }
    @keyframes fm-sp-pop { from { transform: scale(.3) rotate(-12deg); opacity: 0; } to { transform: none; opacity: 1; } }
    @keyframes fm-sp-up { from { transform: translateY(16px); opacity: 0; } to { transform: none; opacity: 1; } }
    .fm-sp.out { transition: opacity .45s ease, transform .45s ease; opacity: 0; transform: scale(1.04); pointer-events: none; }
    /* Cortina: a tela sobe e mostra o site */
    .fm-spm-cortina.out { opacity: 1; transform: translateY(-100%); transition: transform .7s cubic-bezier(.7, 0, .2, 1); }
    .fm-spm-cortina.out .fm-sp-in { transition: opacity .3s; opacity: 0; }
    /* Círculo: abre um buraco redondo a partir da logo */
    .fm-spm-circulo { -webkit-mask: radial-gradient(circle at 50% 50%, transparent var(--sp-r, 0px), #000 calc(var(--sp-r, 0px) + 1px)); mask: radial-gradient(circle at 50% 50%, transparent var(--sp-r, 0px), #000 calc(var(--sp-r, 0px) + 1px)); }
    .fm-spm-circulo.out { opacity: 1; transform: none; transition: none; }
    @media (prefers-reduced-motion: reduce) { .fm-sp { display: none !important; } }
</style>
<script>
(function () {
    var el = document.getElementById('fm-splash');
    if (!el) return;
    var key = 'fm-splash-' + @js($splashKey ?? 'x');
    var seen = false;
    try { seen = sessionStorage.getItem(key) === '1'; sessionStorage.setItem(key, '1'); } catch (e) {}
    if (seen || (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) || /[?&]sem_abertura=1/.test(location.search)) { el.remove(); return; }
    el.classList.add('fm-sp-on');
    var done = false;
    function close() {
        if (done) return; done = true;
        if (el.classList.contains('fm-spm-circulo')) {
            var max = Math.hypot(window.innerWidth, window.innerHeight), t0 = null;
            el.querySelector('.fm-sp-in').style.opacity = '0';
            (function step(t) { t0 = t0 || t; var p = Math.min(1, (t - t0) / 650); el.style.setProperty('--sp-r', (max * p * p) + 'px'); if (p < 1) requestAnimationFrame(step); else el.remove(); })(performance.now());
            return;
        }
        el.classList.add('out');
        setTimeout(function () { el.remove(); }, 800);
    }
    el.addEventListener('click', close);
    setTimeout(close, 1300);
    window.addEventListener('pageshow', function (e) { if (e.persisted) el.remove(); });
})();
</script>
@endif
