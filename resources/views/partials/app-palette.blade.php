{{-- Cores do app (Configurações › Aparência). Troca as famílias de cor da marca
     (índigo/violeta = principal, roxo/fúcsia = apoio, rosa = destaque) pela paleta
     escolhida, só nas variáveis de cor do Tailwind: o formato das telas não muda.
     Vale primeiro o que está salvo na conta (igual em todos os aparelhos). --}}
@php
    $fmPalette = null;
    $fmLook = null;
    if (auth()->check()) {
        $fmPrefs = auth()->user()->preferences;
        $fmPalette = is_array($fmPrefs) ? ($fmPrefs['appearance']['palette'] ?? null) : null;
        // Cores dos cards de produto e fundo das telas
        $fmLook = is_array($fmPrefs) ? ($fmPrefs['appearance']['look'] ?? null) : null;
    }
@endphp
<script>
(function () {
    var P = {"slate":["oklch(98.4% 0.003 247.858)","oklch(96.8% 0.007 247.896)","oklch(92.9% 0.013 255.508)","oklch(86.9% 0.022 252.894)","oklch(70.4% 0.04 256.788)","oklch(55.4% 0.046 257.417)","oklch(44.6% 0.043 257.281)","oklch(37.2% 0.044 257.287)","oklch(27.9% 0.041 260.031)","oklch(20.8% 0.042 265.755)","oklch(12.9% 0.042 264.695)"],"red":["oklch(97.1% 0.013 17.38)","oklch(93.6% 0.032 17.717)","oklch(88.5% 0.062 18.334)","oklch(80.8% 0.114 19.571)","oklch(70.4% 0.191 22.216)","oklch(63.7% 0.237 25.331)","oklch(57.7% 0.245 27.325)","oklch(50.5% 0.213 27.518)","oklch(44.4% 0.177 26.899)","oklch(39.6% 0.141 25.723)","oklch(25.8% 0.092 26.042)"],"orange":["oklch(98% 0.016 73.684)","oklch(95.4% 0.038 75.164)","oklch(90.1% 0.076 70.697)","oklch(83.7% 0.128 66.29)","oklch(75% 0.183 55.934)","oklch(70.5% 0.213 47.604)","oklch(64.6% 0.222 41.116)","oklch(55.3% 0.195 38.402)","oklch(47% 0.157 37.304)","oklch(40.8% 0.123 38.172)","oklch(26.6% 0.079 36.259)"],"amber":["oklch(98.7% 0.022 95.277)","oklch(96.2% 0.059 95.617)","oklch(92.4% 0.12 95.746)","oklch(87.9% 0.169 91.605)","oklch(82.8% 0.189 84.429)","oklch(76.9% 0.188 70.08)","oklch(66.6% 0.179 58.318)","oklch(55.5% 0.163 48.998)","oklch(47.3% 0.137 46.201)","oklch(41.4% 0.112 45.904)","oklch(27.9% 0.077 45.635)"],"yellow":["oklch(98.7% 0.026 102.212)","oklch(97.3% 0.071 103.193)","oklch(94.5% 0.129 101.54)","oklch(90.5% 0.182 98.111)","oklch(85.2% 0.199 91.936)","oklch(79.5% 0.184 86.047)","oklch(68.1% 0.162 75.834)","oklch(55.4% 0.135 66.442)","oklch(47.6% 0.114 61.907)","oklch(42.1% 0.095 57.708)","oklch(28.6% 0.066 53.813)"],"lime":["oklch(98.6% 0.031 120.757)","oklch(96.7% 0.067 122.328)","oklch(93.8% 0.127 124.321)","oklch(89.7% 0.196 126.665)","oklch(84.1% 0.238 128.85)","oklch(76.8% 0.233 130.85)","oklch(64.8% 0.2 131.684)","oklch(53.2% 0.157 131.589)","oklch(45.3% 0.124 130.933)","oklch(40.5% 0.101 131.063)","oklch(27.4% 0.072 132.109)"],"green":["oklch(98.2% 0.018 155.826)","oklch(96.2% 0.044 156.743)","oklch(92.5% 0.084 155.995)","oklch(87.1% 0.15 154.449)","oklch(79.2% 0.209 151.711)","oklch(72.3% 0.219 149.579)","oklch(62.7% 0.194 149.214)","oklch(52.7% 0.154 150.069)","oklch(44.8% 0.119 151.328)","oklch(39.3% 0.095 152.535)","oklch(26.6% 0.065 152.934)"],"emerald":["oklch(97.9% 0.021 166.113)","oklch(95% 0.052 163.051)","oklch(90.5% 0.093 164.15)","oklch(84.5% 0.143 164.978)","oklch(76.5% 0.177 163.223)","oklch(69.6% 0.17 162.48)","oklch(59.6% 0.145 163.225)","oklch(50.8% 0.118 165.612)","oklch(43.2% 0.095 166.913)","oklch(37.8% 0.077 168.94)","oklch(26.2% 0.051 172.552)"],"teal":["oklch(98.4% 0.014 180.72)","oklch(95.3% 0.051 180.801)","oklch(91% 0.096 180.426)","oklch(85.5% 0.138 181.071)","oklch(77.7% 0.152 181.912)","oklch(70.4% 0.14 182.503)","oklch(60% 0.118 184.704)","oklch(51.1% 0.096 186.391)","oklch(43.7% 0.078 188.216)","oklch(38.6% 0.063 188.416)","oklch(27.7% 0.046 192.524)"],"cyan":["oklch(98.4% 0.019 200.873)","oklch(95.6% 0.045 203.388)","oklch(91.7% 0.08 205.041)","oklch(86.5% 0.127 207.078)","oklch(78.9% 0.154 211.53)","oklch(71.5% 0.143 215.221)","oklch(60.9% 0.126 221.723)","oklch(52% 0.105 223.128)","oklch(45% 0.085 224.283)","oklch(39.8% 0.07 227.392)","oklch(30.2% 0.056 229.695)"],"sky":["oklch(97.7% 0.013 236.62)","oklch(95.1% 0.026 236.824)","oklch(90.1% 0.058 230.902)","oklch(82.8% 0.111 230.318)","oklch(74.6% 0.16 232.661)","oklch(68.5% 0.169 237.323)","oklch(58.8% 0.158 241.966)","oklch(50% 0.134 242.749)","oklch(44.3% 0.11 240.79)","oklch(39.1% 0.09 240.876)","oklch(29.3% 0.066 243.157)"],"blue":["oklch(97% 0.014 254.604)","oklch(93.2% 0.032 255.585)","oklch(88.2% 0.059 254.128)","oklch(80.9% 0.105 251.813)","oklch(70.7% 0.165 254.624)","oklch(62.3% 0.214 259.815)","oklch(54.6% 0.245 262.881)","oklch(48.8% 0.243 264.376)","oklch(42.4% 0.199 265.638)","oklch(37.9% 0.146 265.522)","oklch(28.2% 0.091 267.935)"],"indigo":["oklch(96.2% 0.018 272.314)","oklch(93% 0.034 272.788)","oklch(87% 0.065 274.039)","oklch(78.5% 0.115 274.713)","oklch(67.3% 0.182 276.935)","oklch(58.5% 0.233 277.117)","oklch(51.1% 0.262 276.966)","oklch(45.7% 0.24 277.023)","oklch(39.8% 0.195 277.366)","oklch(35.9% 0.144 278.697)","oklch(25.7% 0.09 281.288)"],"violet":["oklch(96.9% 0.016 293.756)","oklch(94.3% 0.029 294.588)","oklch(89.4% 0.057 293.283)","oklch(81.1% 0.111 293.571)","oklch(70.2% 0.183 293.541)","oklch(60.6% 0.25 292.717)","oklch(54.1% 0.281 293.009)","oklch(49.1% 0.27 292.581)","oklch(43.2% 0.232 292.759)","oklch(38% 0.189 293.745)","oklch(28.3% 0.141 291.089)"],"purple":["oklch(97.7% 0.014 308.299)","oklch(94.6% 0.033 307.174)","oklch(90.2% 0.063 306.703)","oklch(82.7% 0.119 306.383)","oklch(71.4% 0.203 305.504)","oklch(62.7% 0.265 303.9)","oklch(55.8% 0.288 302.321)","oklch(49.6% 0.265 301.924)","oklch(43.8% 0.218 303.724)","oklch(38.1% 0.176 304.987)","oklch(29.1% 0.149 302.717)"],"fuchsia":["oklch(97.7% 0.017 320.058)","oklch(95.2% 0.037 318.852)","oklch(90.3% 0.076 319.62)","oklch(83.3% 0.145 321.434)","oklch(74% 0.238 322.16)","oklch(66.7% 0.295 322.15)","oklch(59.1% 0.293 322.896)","oklch(51.8% 0.253 323.949)","oklch(45.2% 0.211 324.591)","oklch(40.1% 0.17 325.612)","oklch(29.3% 0.136 325.661)"],"pink":["oklch(97.1% 0.014 343.198)","oklch(94.8% 0.028 342.258)","oklch(89.9% 0.061 343.231)","oklch(82.3% 0.12 346.018)","oklch(71.8% 0.202 349.761)","oklch(65.6% 0.241 354.308)","oklch(59.2% 0.249 0.584)","oklch(52.5% 0.223 3.958)","oklch(45.9% 0.187 3.815)","oklch(40.8% 0.153 2.432)","oklch(28.4% 0.109 3.907)"],"rose":["oklch(96.9% 0.015 12.422)","oklch(94.1% 0.03 12.58)","oklch(89.2% 0.058 10.001)","oklch(81% 0.117 11.638)","oklch(71.2% 0.194 13.428)","oklch(64.5% 0.246 16.439)","oklch(58.6% 0.253 17.585)","oklch(51.4% 0.222 16.935)","oklch(45.5% 0.188 13.697)","oklch(41% 0.159 10.272)","oklch(27.1% 0.105 12.094)"]};
    var RGB = {"slate":[[69,85,108],[144,161,185]],"red":[[231,0,11],[255,100,103]],"orange":[[245,73,0],[255,137,4]],"amber":[[225,113,0],[255,185,0]],"yellow":[[208,135,0],[253,199,0]],"lime":[[94,165,0],[154,230,0]],"green":[[0,166,62],[5,223,114]],"emerald":[[0,153,102],[0,212,146]],"teal":[[0,150,137],[0,213,190]],"cyan":[[0,146,184],[0,211,242]],"sky":[[0,132,209],[0,188,255]],"blue":[[21,93,252],[81,162,255]],"indigo":[[79,57,246],[124,134,255]],"violet":[[127,34,254],[166,132,255]],"purple":[[152,16,250],[194,122,255]],"fuchsia":[[200,0,222],[237,106,255]],"pink":[[230,0,118],[251,100,182]],"rose":[[236,0,63],[255,99,126]]};
    var SHADES = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
    var DEF = { primary: 'indigo', secondary: 'purple', accent: 'pink' };
    window.FM_PALETTES = P;
    // Amostra fixa de cada família (tom 500), para os botões de cor não mudarem junto
    var sw = '';
    Object.keys(P).forEach(function (k) { sw += '--fm-sw-' + k + ':' + P[k][5] + ';'; });
    var swEl = document.createElement('style'); swEl.id = 'fm-swatches'; swEl.textContent = ':root{' + sw + '}';
    document.head.appendChild(swEl);
    window.fmApplyPalette = function (v) {
        v = v || {};
        var p = P[v.primary] ? v.primary : DEF.primary;
        var s = P[v.secondary] ? v.secondary : DEF.secondary;
        var a = P[v.accent] ? v.accent : DEF.accent;
        var map = [];
        if (p !== DEF.primary) map.push(['indigo', p], ['violet', p]);
        if (s !== DEF.secondary) map.push(['purple', s], ['fuchsia', s]);
        if (a !== DEF.accent) map.push(['pink', a]);
        var css = '';
        map.forEach(function (m) {
            SHADES.forEach(function (n, i) { css += '--color-' + m[0] + '-' + n + ':' + P[m[1]][i] + ';'; });
        });
        var el = document.getElementById('fm-palette');
        if (!el) { el = document.createElement('style'); el.id = 'fm-palette'; document.head.appendChild(el); }
        el.textContent = css ? ':root{' + css + '}' : '';
        var root = document.documentElement;
        root.setAttribute('data-fm-palette', p + '-' + s + '-' + a);
        if (p !== DEF.primary || s !== DEF.secondary) {
            var c = RGB[s] || RGB[p];
            root.style.setProperty('--s-accent', P[s][6]);
            root.style.setProperty('--s-accent-dark', P[s][7]);
            root.style.setProperty('--s-accent-light', P[s][3]);
            root.style.setProperty('--s-accent-faint', P[s][0]);
            root.style.setProperty('--s-accent-rgb', c[0].join(','));
            root.style.setProperty('--fm-primary-rgb', RGB[p][0].join(','));
            // As telas antigas de Configurações leem a cor de destaque daqui
            try {
                localStorage.setItem('flowmanager:color-theme', 'custom');
                localStorage.setItem('flowmanager:color-custom', '#' + c[0].map(function (x) { return ('0' + x.toString(16)).slice(-2); }).join(''));
            } catch (e) {}
        } else {
            root.style.removeProperty('--fm-primary-rgb');
            try { if (localStorage.getItem('flowmanager:color-theme') === 'custom') localStorage.setItem('flowmanager:color-theme', 'purple'); } catch (e) {}
        }
    };
    // Cores dos cards de produto: acompanham o tema, um estilo pronto ou as cores escolhidas
    var CARD_FIXED = {
        branco: ['#ffffff', '#e2e8f0', '#f1f5f9', '#e2e8f0', '#f1f5f9', '#334155', '#0f172a', '#475569'],
        grafite: [P.slate[1], P.slate[3], P.slate[2], P.slate[2], P.slate[1], '#334155', '#0f172a', '#475569'],
        escuro: ['#1e293b', '#475569', '#334155', '#334155', '#0f172a', '#e2e8f0', '#f1f5f9', '#64748b'],
        noite: [P.indigo[9], P.indigo[6], P.violet[8], P.indigo[7], P.indigo[10], '#e0e7ff', '#eef2ff', P.indigo[5]],
    };
    // Estilos coloridos prontos: fundo, borda e nome, etiquetas, detalhes
    var CARD_SETS = {
        rosa: ['pink', 'pink', 'rose', 'amber'], menta: ['emerald', 'teal', 'cyan', 'lime'], ceu: ['sky', 'sky', 'blue', 'cyan'],
        pessego: ['orange', 'orange', 'rose', 'amber'], lavanda: ['violet', 'violet', 'fuchsia', 'pink'], limao: ['lime', 'green', 'yellow', 'emerald'],
        coral: ['rose', 'red', 'orange', 'amber'], oceano: ['cyan', 'blue', 'teal', 'sky'], uva: ['purple', 'purple', 'fuchsia', 'violet'],
        caramelo: ['amber', 'orange', 'yellow', 'rose'], cereja: ['red', 'rose', 'pink', 'orange'], floresta: ['green', 'emerald', 'lime', 'teal'],
    };
    window.FM_CARD_FIXED = CARD_FIXED;
    window.FM_CARD_SETS = CARD_SETS;
    window.fmCardColors = function (look, pal) {
        look = look || {}; pal = pal || {};
        var mode = look.cards || 'tema';
        if (CARD_FIXED[mode]) return CARD_FIXED[mode];
        var f;
        if (mode === 'custom') {
            f = { bg: look.c_bg, border: look.c_border, tag: look.c_tag, detail: look.c_detail };
        } else if (CARD_SETS[mode]) {
            var cs = CARD_SETS[mode];
            f = { bg: cs[0], border: cs[1], tag: cs[2], detail: cs[3] };
        } else if (mode === 'tema') {
            var p = P[pal.primary] ? pal.primary : DEF.primary;
            var s = P[pal.secondary] ? pal.secondary : DEF.secondary;
            var a = P[pal.accent] ? pal.accent : DEF.accent;
            // Com as cores padrão e tom médio o card fica como sempre foi (lilás)
            if (p === DEF.primary && s === DEF.secondary && a === DEF.accent && (!look.tone || look.tone === 'medio')) return null;
            f = { bg: p, border: p, tag: s, detail: a };
        } else {
            return null;
        }
        ['bg', 'border', 'tag', 'detail'].forEach(function (k) { if (!P[f[k]]) f[k] = { bg: 'indigo', border: 'violet', tag: 'pink', detail: 'orange' }[k]; });
        // Intensidade: suave (bem claro), médio, forte (mais cor)
        var t = { suave: 0, medio: 1, forte: 2 }[look.tone] ; if (t === undefined) t = 1;
        return [P[f.bg][t], P[f.border][2 + t], P[f.detail][1 + t], P[f.tag][1 + t], P[f.detail][t], '#334155', P[f.border][10], P[f.border][6]];
    };
    // Desenhos do fundo: SVG pequeno repetido, na cor escolhida
    var PATS = {
        pontos: [22, '<circle cx="11" cy="11" r="1.8" fill="C"/>'],
        bolhas: [64, '<circle cx="14" cy="16" r="7" fill="C"/><circle cx="46" cy="42" r="11" fill="C"/><circle cx="50" cy="10" r="3" fill="C"/><circle cx="18" cy="52" r="4" fill="C"/>'],
        linhas: [18, '<path d="M-2 20 20 -2M7 27 27 7" stroke="C" stroke-width="1.6"/>'],
        grade: [28, '<path d="M28 0H0V28" fill="none" stroke="C" stroke-width="1.2"/>'],
        xadrez: [32, '<rect width="16" height="16" fill="C"/><rect x="16" y="16" width="16" height="16" fill="C"/>'],
        mais: [30, '<path d="M15 9v12M9 15h12" stroke="C" stroke-width="2.2" stroke-linecap="round"/>'],
        ondas: [48, '<path d="M0 12c6 0 6-6 12-6s6 6 12 6 6-6 12-6 6 6 12 6M0 36c6 0 6-6 12-6s6 6 12 6 6-6 12-6 6 6 12 6" fill="none" stroke="C" stroke-width="1.8"/>'],
        zigue: [32, '<path d="M0 12l8-6 8 6 8-6 8 6M0 28l8-6 8 6 8-6 8 6" fill="none" stroke="C" stroke-width="1.8" stroke-linejoin="round"/>'],
        coracoes: [44, '<path d="M22 31s-9-5.6-9-11.4A4.8 4.8 0 0 1 22 17a4.8 4.8 0 0 1 9 2.6C31 25.4 22 31 22 31z" fill="C"/><path d="M6 9s-3.5-2.2-3.5-4.5A1.9 1.9 0 0 1 6 3.5a1.9 1.9 0 0 1 3.5 1C9.5 6.8 6 9 6 9z" fill="C"/>'],
        estrelas: [48, '<path d="M24 10l3.5 7.3 8 1.1-5.8 5.6 1.4 8-7.1-3.8-7.1 3.8 1.4-8-5.8-5.6 8-1.1z" fill="C"/><circle cx="6" cy="40" r="1.6" fill="C"/><circle cx="42" cy="42" r="1.2" fill="C"/>'],
        folhas: [52, '<path d="M14 40C14 24 26 14 40 12c0 15-10 27-26 28z" fill="C"/><path d="M14 40 32 22" stroke="#fff" stroke-opacity=".5" stroke-width="1.2"/>'],
        flores: [52, '<g fill="C"><circle cx="26" cy="18" r="5.5"/><circle cx="34" cy="26" r="5.5"/><circle cx="26" cy="34" r="5.5"/><circle cx="18" cy="26" r="5.5"/></g><circle cx="26" cy="26" r="3.6" fill="#fff" fill-opacity=".7"/>'],
        confete: [56, '<g fill="C"><rect x="6" y="8" width="8" height="3" rx="1.5" transform="rotate(30 10 9)"/><rect x="36" y="14" width="8" height="3" rx="1.5" transform="rotate(-40 40 15)"/><rect x="18" y="38" width="8" height="3" rx="1.5" transform="rotate(70 22 39)"/><circle cx="46" cy="44" r="2.4"/><circle cx="26" cy="22" r="1.8"/></g>'],
        diamantes: [36, '<path d="M18 6l8 12-8 12-8-12z" fill="none" stroke="C" stroke-width="1.6"/>'],
    };
    window.FM_PATTERNS = Object.keys(PATS);
    window.fmPatternUrl = function (id, color, opacity) {
        var d = PATS[id]; if (!d) return '';
        var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + d[0] + '" height="' + d[0] + '" viewBox="0 0 ' + d[0] + ' ' + d[0] + '"><g opacity="' + opacity + '">' + d[1].replace(/="C"/g, '="' + color + '"') + '</g></svg>';
        return 'url("data:image/svg+xml,' + encodeURIComponent(svg) + '")';
    };
    // Fundo das telas: cor de baixo + desenho por cima (claro e escuro)
    window.fmBgCss = function (look, pal, dark) {
        look = look || {}; pal = pal || {};
        var base = '';
        if (!dark) {
            var c = P[look.bg_c] ? look.bg_c : 'indigo';
            base = {
                tema: 'linear-gradient(135deg, var(--color-slate-50) 0%, var(--color-indigo-50) 50%, var(--color-purple-100) 100%)',
                cinza: 'linear-gradient(#eef1f5, #eef1f5)',
                areia: 'linear-gradient(135deg, #fffbeb 0%, #fff7ed 50%, #fdf2f8 100%)',
                branco: 'linear-gradient(#fff, #fff)',
                cor: 'linear-gradient(135deg, ' + P[c][0] + ' 0%, ' + P[c][1] + ' 60%, ' + P[c][2] + ' 100%)',
                padrao: 'linear-gradient(to bottom right, var(--color-slate-50), var(--color-blue-50), var(--color-indigo-50))',
            }[look.bg] || '';
        }
        var pat = '';
        if (PATS[look.pat]) {
            var fam = look.pat_c && look.pat_c !== 'tema' && P[look.pat_c] ? look.pat_c : (P[pal.primary] ? pal.primary : DEF.primary);
            var o = { suave: 0.09, medio: 0.16, forte: 0.26 }[look.pat_o] || 0.16;
            pat = window.fmPatternUrl(look.pat, P[fam][dark ? 4 : 5], dark ? o * 0.75 : o);
            if (!base) base = dark ? 'linear-gradient(to bottom right, #0f172a, #1e293b, #0f172a)' : 'linear-gradient(to bottom right, var(--color-slate-50), var(--color-blue-50), var(--color-indigo-50))';
        }
        return pat ? pat + ', ' + base : base;
    };
    window.fmApplyLook = function (look, pal) {
        look = look || {};
        var c = window.fmCardColors(look, pal);
        var el = document.getElementById('fm-cards');
        if (!el) { el = document.createElement('style'); el.id = 'fm-cards'; document.head.appendChild(el); }
        var css = c ? 'html:root{--card-bg:' + c[0] + ';--card-border:' + c[1] + ';--card-accent:' + c[2] + ';--card-accent2:' + c[3] + ';--card-danger:' + c[4] + ';--card-text:' + c[5] + ';--card-title:' + c[6] + ';--card-btn:' + c[7] + ';}' : '';
        var light = window.fmBgCss(look, pal, false), dk = PATS[look.pat] ? window.fmBgCss(look, pal, true) : '';
        if (light) css += 'html:not(.dark) body{background:' + light + ' !important;background-attachment:fixed !important;}';
        if (dk) css += 'html.dark body{background:' + dk + ' !important;background-attachment:fixed !important;}';
        el.textContent = css;
        var root = document.documentElement;
        var set = function (k, v, ok) { if (ok.indexOf(v) >= 0) root.setAttribute(k, v); else root.removeAttribute(k); };
        set('data-fm-bg', look.bg, ['tema', 'cinza', 'areia', 'branco', 'cor']);
        set('data-fm-cshape', look.shape, ['medio', 'reto']);
        set('data-fm-side', look.side, ['tema', 'branco', 'cinza', 'areia', 'cor']);
        set('data-fm-tab', look.tab, ['tema', 'branco', 'cor']);
    };
    try {
        var server = @json($fmPalette);
        var local = null;
        try { local = JSON.parse(localStorage.getItem('flowmanager:palette') || 'null'); } catch (e) {}
        var v = server || local;
        if (server) localStorage.setItem('flowmanager:palette', JSON.stringify(server));
        if (v) window.fmApplyPalette(v);
        var look = @json($fmLook);
        var lookLocal = null;
        try { lookLocal = JSON.parse(localStorage.getItem('flowmanager:look') || 'null'); } catch (e) {}
        if (look) localStorage.setItem('flowmanager:look', JSON.stringify(look));
        window.fmApplyLook(look || lookLocal, v || {});
    } catch (e) {}
})();
</script>
