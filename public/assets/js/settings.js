/* ═══════════════════════════════════════════════════════════
   FLOWMANAGER — SETTINGS.JS
   Interatividade das páginas de Configurações
   ═══════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    /* ══════════════════════════════════════════════════════
       SISTEMA DE TEMAS DE COR
       ══════════════════════════════════════════════════════ */
    const THEMES = {
        purple:  { accent: '#9333ea', dark: '#7c3aed', light: '#d8b4fe', faint: '#faf5ff', rgb: '147,51,234' },
        indigo:  { accent: '#4f46e5', dark: '#3730a3', light: '#a5b4fc', faint: '#eef2ff', rgb: '79,70,229' },
        blue:    { accent: '#2563eb', dark: '#1d4ed8', light: '#93c5fd', faint: '#eff6ff', rgb: '37,99,235' },
        teal:    { accent: '#0d9488', dark: '#0f766e', light: '#5eead4', faint: '#f0fdfa', rgb: '13,148,136' },
        emerald: { accent: '#059669', dark: '#047857', light: '#6ee7b7', faint: '#ecfdf5', rgb: '5,150,105' },
        rose:    { accent: '#e11d48', dark: '#be123c', light: '#fda4af', faint: '#fff1f2', rgb: '225,29,72' },
        orange:  { accent: '#ea580c', dark: '#c2410c', light: '#fdba74', faint: '#fff7ed', rgb: '234,88,12' },
        amber:   { accent: '#d97706', dark: '#b45309', light: '#fcd34d', faint: '#fffbeb', rgb: '217,119,6' },
    };

    window.FlowSettings = {
        themes: THEMES,

        getStoredJson: function (key, fallback) {
            var defaultValue = fallback || {};
            try {
                var raw = localStorage.getItem(key);
                if (!raw) return defaultValue;
                var parsed = JSON.parse(raw);
                return parsed && typeof parsed === 'object' ? parsed : defaultValue;
            } catch (e) {
                return defaultValue;
            }
        },

        loadPreferenceGroup: function (group, dbData) {
            var storageKey = 'flowmanager:' + group;
            var localData = FlowSettings.getStoredJson(storageKey, {});
            var remoteData = dbData && typeof dbData === 'object' ? dbData : {};
            var merged = Object.keys(remoteData).length
                ? Object.assign({}, localData, remoteData)
                : localData;

            try {
                if (Object.keys(merged).length) {
                    localStorage.setItem(storageKey, JSON.stringify(merged));
                }
            } catch (e) {}

            return merged;
        },

        savePreferenceGroup: function (group, data) {
            var csrf = document.querySelector('meta[name="csrf-token"]');
            if (!csrf || !window.fetch) return;

            fetch('/settings/preferences/' + group, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf.content,
                },
                body: JSON.stringify({ data: data })
            }).catch(function () {});
        },

        mergePreferenceGroup: function (group, patch) {
            var storageKey = 'flowmanager:' + group;
            var current = FlowSettings.getStoredJson(storageKey, {});
            var next = Object.assign({}, current, patch || {});

            try {
                localStorage.setItem(storageKey, JSON.stringify(next));
            } catch (e) {}

            FlowSettings.savePreferenceGroup(group, next);
            return next;
        },

        applyTheme: function (name, save) {
            const t = THEMES[name];
            if (!t) return;
            const root = document.documentElement;
            root.style.setProperty('--s-accent',       t.accent);
            root.style.setProperty('--s-accent-dark',  t.dark);
            root.style.setProperty('--s-accent-light', t.light);
            root.style.setProperty('--s-accent-faint', t.faint);
            root.style.setProperty('--s-accent-rgb',   t.rgb);

            if (save !== false) {
                try { localStorage.setItem('flowmanager:color-theme', name); } catch(e) {}
                FlowSettings.mergePreferenceGroup('appearance', {
                    color_theme: name,
                    color_custom: null,
                });
            }

            // atualizar swatches
            document.querySelectorAll('.settings-color-swatch[data-theme]').forEach(function (el) {
                el.classList.toggle('cs-active', el.dataset.theme === name);
            });

            // atualizar campo hex customizado
            var hexEl = document.getElementById('customColorHex');
            if (hexEl) hexEl.textContent = t.accent;
            var pickerEl = document.getElementById('customColorPicker');
            if (pickerEl) pickerEl.value = t.accent;

            // atualizar prévia
            FlowSettings.updatePreview(t);
        },

        applyCustomColor: function (hex) {
            const root = document.documentElement;
            root.style.setProperty('--s-accent', hex);
            // gerar dark/light aproximados
            root.style.setProperty('--s-accent-dark',  FlowSettings.adjustBrightness(hex, -20));
            root.style.setProperty('--s-accent-light', FlowSettings.adjustBrightness(hex, 80));
            root.style.setProperty('--s-accent-faint', FlowSettings.adjustBrightness(hex, 120));
            var rgb = FlowSettings.hexToRgb(hex);
            if (rgb) root.style.setProperty('--s-accent-rgb', rgb.r + ',' + rgb.g + ',' + rgb.b);

            try { localStorage.setItem('flowmanager:color-theme', 'custom'); } catch(e) {}
            try { localStorage.setItem('flowmanager:color-custom', hex); } catch(e) {}
            FlowSettings.mergePreferenceGroup('appearance', {
                color_theme: 'custom',
                color_custom: hex,
            });

            // desativar swatches
            document.querySelectorAll('.settings-color-swatch[data-theme]').forEach(function (el) {
                el.classList.remove('cs-active');
            });
            var hexEl = document.getElementById('customColorHex');
            if (hexEl) hexEl.textContent = hex;

            FlowSettings.updatePreview({ accent: hex });
        },

        updatePreview: function (t) {
            document.querySelectorAll('.settings-preview-topbar').forEach(function (el) {
                el.style.background = t.accent || t;
            });
            document.querySelectorAll('.settings-preview-sb-bar.spb-active').forEach(function (el) {
                el.style.background = t.accent || t;
            });
            document.querySelectorAll('.settings-preview-line.spl-accent').forEach(function (el) {
                el.style.background = (t.accent || t) + '55';
            });
        },

        loadTheme: function () {
            try {
                var prefs = FlowSettings.getStoredJson('flowmanager:appearance', {});
                var saved = prefs.color_theme || localStorage.getItem('flowmanager:color-theme');
                if (saved === 'custom') {
                    var hex = prefs.color_custom || localStorage.getItem('flowmanager:color-custom') || '#9333ea';
                    FlowSettings.applyCustomColor(hex);
                } else {
                    FlowSettings.applyTheme(saved || 'purple', false);
                }
            } catch(e) {
                FlowSettings.applyTheme('purple', false);
            }
        },

        /* utilitários */
        hexToRgb: function (hex) {
            var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        },

        adjustBrightness: function (hex, amount) {
            var rgb = FlowSettings.hexToRgb(hex);
            if (!rgb) return hex;
            var r = Math.min(255, Math.max(0, rgb.r + amount));
            var g = Math.min(255, Math.max(0, rgb.g + amount));
            var b = Math.min(255, Math.max(0, rgb.b + amount));
            return '#' + [r,g,b].map(function(v) {
                return v.toString(16).padStart(2,'0');
            }).join('');
        },
    };

    /* ══════════════════════════════════════════════════════
       MEDIDOR DE FORÇA DE SENHA
       ══════════════════════════════════════════════════════ */
    function checkPasswordStrength(pw) {
        var score = 0;
        if (pw.length >= 8)  score++;
        if (pw.length >= 12) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^a-zA-Z0-9]/.test(pw)) score++;
        return score; // 0-5
    }

    var STRENGTH_MAP = [
        { label: '',             cls: '' },
        { label: 'Muito fraca',  cls: 'pst-weak' },
        { label: 'Fraca',        cls: 'pst-weak' },
        { label: 'Razoável',     cls: 'pst-medium' },
        { label: 'Forte',        cls: 'pst-strong' },
        { label: 'Muito forte',  cls: 'pst-great' },
    ];

    function updateStrengthUI(input) {
        var wrap = document.getElementById('passwordStrengthWrap');
        if (!wrap) return;
        var score = checkPasswordStrength(input.value);
        var bars  = wrap.querySelectorAll('.password-strength-bar');
        var lbl   = wrap.querySelector('.password-strength-text');
        bars.forEach(function (bar, i) {
            bar.className = 'password-strength-bar';
            if (i < score) bar.classList.add('bar-' + score);
        });
        if (lbl) {
            var info = STRENGTH_MAP[score] || STRENGTH_MAP[0];
            lbl.textContent  = info.label;
            lbl.className    = 'password-strength-text ' + info.cls;
        }
    }

    function initPasswordStrength() {
        var input = document.querySelector('[data-password-strength]');
        if (!input) return;
        input.addEventListener('input', function () { updateStrengthUI(input); });
        updateStrengthUI(input);
    }

    /* ══════════════════════════════════════════════════════
       MOSTRAR / OCULTAR SENHA
       ══════════════════════════════════════════════════════ */
    function initPasswordToggles() {
        document.querySelectorAll('[data-pass-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var targetId = btn.getAttribute('data-pass-toggle');
                var input    = document.getElementById(targetId);
                if (!input) return;
                var isHidden = input.type === 'password';
                input.type   = isHidden ? 'text' : 'password';
                var eyeOpen  = btn.querySelector('[data-eye-open]');
                var eyeClose = btn.querySelector('[data-eye-close]');
                if (eyeOpen)  eyeOpen.classList.toggle('hidden',  !isHidden);
                if (eyeClose) eyeClose.classList.toggle('hidden', isHidden);
            });
        });
    }

    /* ══════════════════════════════════════════════════════
       PRÉVIA DO AVATAR
       ══════════════════════════════════════════════════════ */
    function initAvatarPreview() {
        var input = document.querySelector('[data-avatar-input]');
        if (!input) return;
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                var img  = document.getElementById('avatarPreviewImg');
                var ph   = document.getElementById('avatarPlaceholder');
                if (img) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                }
                if (ph) ph.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
    }

    /* ══════════════════════════════════════════════════════
       COMPLETUDE DO PERFIL
       ══════════════════════════════════════════════════════ */
    function updateProfileCompletion() {
        var fields = [
            '[name="name"]','[name="email"]','[name="phone"]',
            '[name="location"]','[name="about_me"]','[name="google_id"]',
        ];
        var filled = 0;
        fields.forEach(function (sel) {
            var el = document.querySelector(sel);
            if (el && el.value && el.value.trim().length > 0) filled++;
        });
        // foto de perfil
        var img = document.getElementById('avatarPreviewImg');
        if (img && !img.classList.contains('hidden')) filled++;
        var total = fields.length + 1;
        var pct   = Math.round((filled / total) * 100);

        var fillBar = document.getElementById('completionFill');
        var pctEl   = document.getElementById('completionPct');
        if (fillBar) fillBar.style.width = pct + '%';
        if (pctEl)   pctEl.textContent   = pct + '%';
    }

    /* ══════════════════════════════════════════════════════
       RANGE SLIDERS
       ══════════════════════════════════════════════════════ */
    function initRangeSliders() {
        document.querySelectorAll('.settings-range').forEach(function (slider) {
            function update() {
                var min = parseFloat(slider.min) || 0;
                var max = parseFloat(slider.max) || 100;
                var val = parseFloat(slider.value) || 0;
                var pct = ((val - min) / (max - min) * 100).toFixed(1);
                slider.style.setProperty('--rfill', pct + '%');
                // feedback visual se existir
                var out = document.getElementById(slider.getAttribute('data-range-output'));
                if (out) out.textContent = slider.getAttribute('data-range-suffix')
                    ? val + slider.getAttribute('data-range-suffix')
                    : val;
            }
            slider.addEventListener('input', update);
            update();
        });
    }

    /* ══════════════════════════════════════════════════════
       SELETOR DE APARÊNCIA (claro/escuro/sistema)
       ══════════════════════════════════════════════════════ */
    function initAppearanceCards() {
        document.querySelectorAll('.settings-option-card[data-appearance]').forEach(function (card) {
            card.addEventListener('click', function () {
                var mode = card.getAttribute('data-appearance');
                document.querySelectorAll('.settings-option-card[data-appearance]').forEach(function (c) {
                    c.classList.remove('soc-active');
                });
                card.classList.add('soc-active');
                // aplicar modo
                if (mode === 'dark') {
                    document.documentElement.classList.add('dark');
                    try { localStorage.setItem('flowmanager:theme', 'dark'); } catch(e) {}
                    FlowSettings.mergePreferenceGroup('appearance', { theme_mode: 'dark' });
                } else if (mode === 'light') {
                    document.documentElement.classList.remove('dark');
                    try { localStorage.setItem('flowmanager:theme', 'light'); } catch(e) {}
                    FlowSettings.mergePreferenceGroup('appearance', { theme_mode: 'light' });
                } else {
                    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.documentElement.classList.toggle('dark', prefersDark);
                    try { localStorage.removeItem('flowmanager:theme'); } catch(e) {}
                    FlowSettings.mergePreferenceGroup('appearance', { theme_mode: 'system' });
                }
            });
        });

        // marcar ativo atual
        try {
            var prefs = FlowSettings.getStoredJson('flowmanager:appearance', {});
            var current = prefs.theme_mode || localStorage.getItem('flowmanager:theme');
            var activeMode = current || 'system';
            var activeCard = document.querySelector('.settings-option-card[data-appearance="' + activeMode + '"]');
            if (activeCard) activeCard.classList.add('soc-active');
        } catch(e) {}
    }

    /* ══════════════════════════════════════════════════════
       COR PERSONALIZADA (input type=color)
       ══════════════════════════════════════════════════════ */
    function initCustomColorPicker() {
        var picker = document.getElementById('customColorPicker');
        if (!picker) return;
        picker.addEventListener('input', function () {
            FlowSettings.applyCustomColor(picker.value);
        });
    }

    /* ══════════════════════════════════════════════════════
       FONT SIZE LIVE PREVIEW
       ══════════════════════════════════════════════════════ */
    function applyFontSize(size) {
        document.documentElement.style.setProperty('--app-base-font', size + 'px');
        try { localStorage.setItem('flowmanager:font-size', size); } catch(e) {}
        FlowSettings.mergePreferenceGroup('appearance', { font_size: String(size) });
    }

    function initFontSizeSlider() {
        var slider = document.getElementById('fontSizeSlider');
        if (!slider) return;
        slider.addEventListener('input', function () { applyFontSize(slider.value); });
        try {
            var prefs = FlowSettings.getStoredJson('flowmanager:appearance', {});
            var saved = prefs.font_size || localStorage.getItem('flowmanager:font-size');
            if (saved) { slider.value = saved; applyFontSize(saved); }
        } catch(e) {}
    }

    /* ══════════════════════════════════════════════════════
       COMPACT MODE
       ══════════════════════════════════════════════════════ */
    function initCompactMode() {
        var toggle = document.getElementById('compactModeToggle');
        if (!toggle) return;
        try {
            var prefs = FlowSettings.getStoredJson('flowmanager:appearance', {});
            var saved = Object.prototype.hasOwnProperty.call(prefs, 'compact_mode')
                ? !!prefs.compact_mode
                : localStorage.getItem('flowmanager:compact') === 'true';
            toggle.checked = saved;
            document.documentElement.classList.toggle('compact-mode', saved);
        } catch(e) {}
        toggle.addEventListener('change', function () {
            document.documentElement.classList.toggle('compact-mode', toggle.checked);
            try { localStorage.setItem('flowmanager:compact', toggle.checked); } catch(e) {}
            FlowSettings.mergePreferenceGroup('appearance', { compact_mode: toggle.checked });
        });
    }

    /* ══════════════════════════════════════════════════════
       INIT
       ══════════════════════════════════════════════════════ */
    function init() {
        FlowSettings.loadTheme();
        initPasswordStrength();
        initPasswordToggles();
        initAvatarPreview();
        initRangeSliders();
        initAppearanceCards();
        initCustomColorPicker();
        initFontSizeSlider();
        initCompactMode();
        // completude (com 150ms de delay para campos Livewire serem preenchidos)
        setTimeout(updateProfileCompletion, 150);

        // reagir a mudanças de campo para atualizar completude
        document.querySelectorAll('.settings-profile-page input, .settings-profile-page textarea').forEach(function (el) {
            el.addEventListener('input', updateProfileCompletion);
        });
    }

    document.addEventListener('DOMContentLoaded', init);
    // Livewire navigate (SPA)
    document.addEventListener('livewire:navigated', init);

})();
