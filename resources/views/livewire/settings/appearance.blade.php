<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="settings-appearance-page w-full mobile-393-base">
    <script>
        window.flowAppearance = window.flowAppearance || {
            prefs: function () {
                return window.FlowSettings ? FlowSettings.getStoredJson('flowmanager:appearance', {}) : {};
            },
            get: function (key, fallback, legacyKey) {
                var prefs = this.prefs();
                if (Object.prototype.hasOwnProperty.call(prefs, key)) {
                    return prefs[key];
                }
                if (!legacyKey) return fallback;
                var legacy = localStorage.getItem(legacyKey);
                return legacy !== null ? legacy : fallback;
            },
            getBool: function (key, fallback, legacyKey) {
                var prefs = this.prefs();
                if (Object.prototype.hasOwnProperty.call(prefs, key)) {
                    return !!prefs[key];
                }
                if (!legacyKey) return fallback;
                var legacy = localStorage.getItem(legacyKey);
                return legacy === null ? fallback : legacy === 'true';
            },
            save: function (key, value, legacyKey, removeLegacy) {
                if (legacyKey) {
                    try {
                        if (removeLegacy && (value === null || value === 'system')) {
                            localStorage.removeItem(legacyKey);
                        } else {
                            localStorage.setItem(legacyKey, String(value));
                        }
                    } catch (e) {}
                }

                if (window.FlowSettings) {
                    FlowSettings.mergePreferenceGroup('appearance', { [key]: value });
                }
            }
        };
    </script>


    <x-settings.layout :heading="''">

        <div class="s-pg-grid">
        <div class="s-col-main">

        {{-- ── CARD: TEMA DE COR ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="settings-card-title">{{ __('Tema de cor') }}</p>
                        <p class="settings-card-desc">{{ __('Escolha a cor principal do sistema — aplicada em todo o site') }}</p>
                    </div>
                </div>
                <span class="s-badge s-badge-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.72rem;height:0.72rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/></svg>
                    Novo
                </span>
            </div>

            {{-- Swatches de temas predefinidos --}}
            <div class="settings-color-grid">
                <button type="button" class="settings-color-swatch" data-theme="purple" title="Violeta">
                    <div class="settings-color-dot" style="background:linear-gradient(135deg,#a855f7,#7c3aed)"></div>
                    <span class="settings-color-name">Violeta</span>
                </button>
                <button type="button" class="settings-color-swatch" data-theme="indigo" title="Índigo">
                    <div class="settings-color-dot" style="background:linear-gradient(135deg,#6366f1,#3730a3)"></div>
                    <span class="settings-color-name">Índigo</span>
                </button>
                <button type="button" class="settings-color-swatch" data-theme="blue" title="Azul">
                    <div class="settings-color-dot" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)"></div>
                    <span class="settings-color-name">Azul</span>
                </button>
                <button type="button" class="settings-color-swatch" data-theme="teal" title="Teal">
                    <div class="settings-color-dot" style="background:linear-gradient(135deg,#14b8a6,#0f766e)"></div>
                    <span class="settings-color-name">Teal</span>
                </button>
                <button type="button" class="settings-color-swatch" data-theme="emerald" title="Verde">
                    <div class="settings-color-dot" style="background:linear-gradient(135deg,#10b981,#047857)"></div>
                    <span class="settings-color-name">Verde</span>
                </button>
                <button type="button" class="settings-color-swatch" data-theme="rose" title="Rosa">
                    <div class="settings-color-dot" style="background:linear-gradient(135deg,#f43f5e,#be123c)"></div>
                    <span class="settings-color-name">Rosa</span>
                </button>
                <button type="button" class="settings-color-swatch" data-theme="orange" title="Laranja">
                    <div class="settings-color-dot" style="background:linear-gradient(135deg,#f97316,#c2410c)"></div>
                    <span class="settings-color-name">Laranja</span>
                </button>
                <button type="button" class="settings-color-swatch" data-theme="amber" title="Âmbar">
                    <div class="settings-color-dot" style="background:linear-gradient(135deg,#f59e0b,#b45309)"></div>
                    <span class="settings-color-name">Âmbar</span>
                </button>
            </div>

            {{-- Cor personalizada --}}
            <div style="margin-top:1rem">
                <p style="font-size:0.8rem;font-weight:600;color:#64748b;margin-bottom:0.5rem" class="dark:text-slate-400">Ou escolha uma cor personalizada:</p>
                <div class="settings-custom-color-row">
                    <input type="color" id="customColorPicker" class="settings-custom-color-swatch" value="#9333ea" title="Cor personalizada">
                    <span class="settings-custom-color-label">Cor personalizada</span>
                    <span class="settings-custom-color-hex" id="customColorHex">#9333ea</span>
                </div>
            </div>

            {{-- Prévia mini --}}
            <div style="margin-top:1.25rem">
                <p style="font-size:0.8rem;font-weight:600;color:#64748b;margin-bottom:0.5rem" class="dark:text-slate-400">Pré-visualização:</p>
                <div class="settings-preview-block">
                    <div class="settings-preview-topbar">
                        <div class="settings-preview-dot"></div>
                        <div class="settings-preview-dot"></div>
                        <div class="settings-preview-dot"></div>
                    </div>
                    <div class="settings-preview-body">
                        <div class="settings-preview-sidebar">
                            <div class="settings-preview-sb-bar spb-active"></div>
                            <div class="settings-preview-sb-bar"></div>
                            <div class="settings-preview-sb-bar"></div>
                            <div class="settings-preview-sb-bar"></div>
                        </div>
                        <div class="settings-preview-content">
                            <div class="settings-preview-line spl-accent"></div>
                            <div class="settings-preview-line spl-med"></div>
                            <div class="settings-preview-line spl-short"></div>
                            <div class="settings-preview-line" style="width:85%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div>{{-- /s-col-main --}}

        <div class="s-col-side">

        {{-- ── CARD: MODO ESCURO / CLARO ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="settings-card-title">{{ __('Tema visual') }}</p>
                        <p class="settings-card-desc">{{ __('Escolha entre modo claro, escuro ou seguir o sistema') }}</p>
                    </div>
                </div>
            </div>

            <div class="settings-option-row">
                <button type="button" class="settings-option-card" data-appearance="light" title="Claro">
                    <span class="settings-option-icon">☀️</span>
                    <span class="settings-option-label">Claro</span>
                </button>
                <button type="button" class="settings-option-card" data-appearance="dark" title="Escuro">
                    <span class="settings-option-icon">🌙</span>
                    <span class="settings-option-label">Escuro</span>
                </button>
                <button type="button" class="settings-option-card" data-appearance="system" title="Sistema">
                    <span class="settings-option-icon">💻</span>
                    <span class="settings-option-label">Sistema</span>
                </button>
            </div>
        </div>

        {{-- ── CARD: NAVEGAÇÃO NO IPAD ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-15a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="settings-card-title">{{ __('Navegação no iPad (paisagem)') }}</p>
                        <p class="settings-card-desc">{{ __('Escolha o estilo de navegação ao usar o iPad em modo horizontal') }}</p>
                    </div>
                </div>
                <span class="s-badge s-badge-accent">iPad</span>
            </div>

            <div x-data="{
                mode: flowAppearance.get('tablet_nav_mode', 'sidebar', 'flowmanager:tablet-nav-mode') === 'tabbar' ? 'tabbar' : 'sidebar',
                save(next) {
                    this.mode = next;
                    localStorage.setItem('flowmanager:tablet-nav-mode', next);
                    flowAppearance.save('tablet_nav_mode', next, 'flowmanager:tablet-nav-mode');
                    window.dispatchEvent(new CustomEvent('flowmanager:tablet-nav-mode-changed', { detail: { mode: next } }));
                }
            }">
                <div class="settings-tab-nav-toggle">
                    <button type="button"
                        @click="save('sidebar')"
                        :class="mode === 'sidebar' ? 's-active' : ''"
                        class="settings-tab-nav-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.875rem;height:0.875rem;display:inline;vertical-align:-0.1em;margin-right:0.3rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Z"/></svg>
                        Sidebar lateral
                    </button>
                    <button type="button"
                        @click="save('tabbar')"
                        :class="mode === 'tabbar' ? 's-active' : ''"
                        class="settings-tab-nav-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.875rem;height:0.875rem;display:inline;vertical-align:-0.1em;margin-right:0.3rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                        Tab bar inferior
                    </button>
                </div>
                <p style="font-size:0.75rem;color:#94a3b8;margin-top:0.6rem">
                    <template x-if="mode === 'sidebar'">
                        <span>✓ Sidebar fixa à esquerda, mais espaço para o conteúdo</span>
                    </template>
                    <template x-if="mode === 'tabbar'">
                        <span>✓ Tab bar na parte inferior, navegação com polegar</span>
                    </template>
                </p>
            </div>
        </div>

        {{-- ── CARD: PREFERÊNCIAS DE EXIBIÇÃO ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 13.5V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 9.75V10.5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="settings-card-title">{{ __('Preferências de exibição') }}</p>
                        <p class="settings-card-desc">{{ __('Ajuste tamanho de fonte, animações e densidade') }}</p>
                    </div>
                </div>
            </div>

            {{-- Tamanho da fonte --}}
            <div style="margin-bottom:1.5rem">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem">
                    <label class="settings-field-label" style="margin:0">Tamanho da fonte</label>
                    <span style="font-size:0.8rem;font-weight:700;color:var(--s-accent)" id="fontSizeVal">14px</span>
                </div>
                <input type="range" id="fontSizeSlider" class="settings-range"
                    min="12" max="18" value="14" step="1"
                    data-range-output="fontSizeVal" data-range-suffix="px">
                <div style="display:flex;justify-content:space-between;margin-top:0.3rem">
                    <span style="font-size:0.68rem;color:#94a3b8">Pequena</span>
                    <span style="font-size:0.68rem;color:#94a3b8">Grande</span>
                </div>
            </div>

            {{-- Toggles --}}
            <div>
                {{-- Compact mode --}}
                <div class="settings-toggle-row">
                    <div class="settings-toggle-info">
                        <p class="settings-toggle-title">Modo compacto</p>
                        <p class="settings-toggle-desc">Reduz o espaçamento para visualizar mais conteúdo</p>
                    </div>
                    <label class="settings-toggle-switch">
                        <input type="checkbox" id="compactModeToggle">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </label>
                </div>

                {{-- Animações --}}
                <div class="settings-toggle-row" x-data="{
                    animOff: flowAppearance.getBool('no_animations', false, 'flowmanager:no-animations'),
                    toggle() {
                        this.animOff = !this.animOff;
                        document.documentElement.classList.toggle('no-animations', this.animOff);
                        localStorage.setItem('flowmanager:no-animations', this.animOff);
                        flowAppearance.save('no_animations', this.animOff, 'flowmanager:no-animations');
                    }
                }">
                    <div class="settings-toggle-info">
                        <p class="settings-toggle-title">Reduzir animações</p>
                        <p class="settings-toggle-desc">Desativa transições e efeitos de movimento</p>
                    </div>
                    <label class="settings-toggle-switch" @click.prevent="toggle()">
                        <input type="checkbox" :checked="animOff" readonly>
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </label>
                </div>

                {{-- Notificações visuais --}}
                <div class="settings-toggle-row" x-data="{
                    enabled: flowAppearance.getBool('toasts', true, 'flowmanager:toasts'),
                    toggle() {
                        this.enabled = !this.enabled;
                        localStorage.setItem('flowmanager:toasts', this.enabled);
                        flowAppearance.save('toasts', this.enabled, 'flowmanager:toasts');
                    }
                }">
                    <div class="settings-toggle-info">
                        <p class="settings-toggle-title">Notificações toast</p>
                        <p class="settings-toggle-desc">Exibe mensagens de confirmação e alertas</p>
                    </div>
                    <label class="settings-toggle-switch" @click.prevent="toggle()">
                        <input type="checkbox" :checked="enabled" readonly>
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </label>
                </div>
            </div>
        </div>

        {{-- CARD: Tipografia --}}
        <div class="settings-card" x-data="{
            font: flowAppearance.get('font_family', 'system', 'flowmanager:font') || 'system',
            fonts: [
                {id:'system',label:'Sistema',desc:'Fonte padrão do dispositivo',sample:'Aa'},
                {id:'inter',label:'Inter',desc:'Moderna e legível',sample:'Aa',css:'Inter,sans-serif'},
                {id:'roboto',label:'Roboto',desc:'Clean e profissional',sample:'Aa',css:'Roboto,sans-serif'},
                {id:'poppins',label:'Poppins',desc:'Geométrica e amigável',sample:'Aa',css:'Poppins,sans-serif'},
                {id:'nunito',label:'Nunito',desc:'Arredondada e suave',sample:'Aa',css:'Nunito,sans-serif'},
                {id:'mono',label:'Mono',desc:'Código e precisão',sample:'Aa',css:'ui-monospace,monospace'},
            ],
            apply(id,css) {
                this.font = id;
                localStorage.setItem(\'flowmanager:font\', id);
                flowAppearance.save('font_family', id, 'flowmanager:font');
                document.documentElement.style.setProperty(\'--font-family\', css || \'\');
                if(css) document.body.style.fontFamily = css;
                else document.body.style.fontFamily = \'\';
            }
        }" x-init="
            const f = fonts.find(x=>x.id===font);
            if(f && f.css) document.body.style.fontFamily = f.css;
        ">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(99,102,241,.1);color:#6366f1">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Tipografia</p>
                        <p class="settings-card-desc">Família de fontes usada na interface</p>
                    </div>
                </div>
                <span class="s-badge" x-text="fonts.find(f=>f.id===font)?.label || 'Sistema'"></span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.65rem;padding:0 1.25rem 1.5rem">
                <template x-for="f in fonts" :key="f.id">
                    <button type="button"
                        @click="apply(f.id, f.css)"
                        :style="'font-family:'+( f.css||'inherit')"
                        :class="font===f.id ? 'settings-font-btn s-on' : 'settings-font-btn'"
                        style="text-align:left;">
                        <span class="settings-font-sample" x-text="f.sample" :style="'font-family:'+(f.css||'inherit');font-size:1.5rem;font-weight:700;color:#1e293b;display:block;line-height:1;margin-bottom:.3rem'"></span>
                        <span class="settings-font-name" x-text="f.label" style="font-size:.78rem;font-weight:700;color:#1e293b;display:block"></span>
                        <span class="settings-font-desc" x-text="f.desc" style="font-size:.68rem;color:#94a3b8;display:block;margin-top:.1rem"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- CARD: Arredondamento dos cantos --}}
        <div class="settings-card" x-data="{
            radius: flowAppearance.get('corner_radius', 'default', 'flowmanager:radius') || 'default',
            opts: [
                {id:'sharp',label:'Angular',desc:'Sem arredondamento',val:'0'},
                {id:'slight',label:'Suave',desc:'Leve arredondamento',val:'4px'},
                {id:'default',label:'Padrão',desc:'Arredondamento padrão',val:'8px'},
                {id:'rounded',label:'Arredondado',desc:'Bastante arredondado',val:'16px'},
                {id:'pill',label:'Pill',desc:'Totalmente arredondado',val:'999px'},
            ],
            apply(id,val) {
                this.radius = id;
                localStorage.setItem(\'flowmanager:radius\', id);
                flowAppearance.save('corner_radius', id, 'flowmanager:radius');
                document.documentElement.style.setProperty(\'--border-radius-base\', val);
            }
        }" x-init="
            const r = opts.find(o=>o.id===radius);
            if(r) document.documentElement.style.setProperty(\'--border-radius-base\', r.val);
        ">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(245,158,11,.1);color:#f59e0b">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 0 1 7.5 5.25h9a2.25 2.25 0 0 1 2.25 2.25v9a2.25 2.25 0 0 1-2.25 2.25h-9a2.25 2.25 0 0 1-2.25-2.25v-9Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Arredondamento de cantos</p>
                        <p class="settings-card-desc">Define o raio dos elementos de interface</p>
                    </div>
                </div>
                <span class="s-badge" x-text="opts.find(o=>o.id===radius)?.label || 'Padrão'"></span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:.65rem;padding:0 1.25rem 1.5rem">
                <template x-for="o in opts" :key="o.id">
                    <button type="button"
                        @click="apply(o.id, o.val)"
                        :class="radius===o.id ? 'settings-radius-btn s-on' : 'settings-radius-btn'">
                        <div style="width:1.8rem;height:1.8rem;background:rgba(var(--s-accent-rgb),.15);margin-bottom:.4rem;" :style="'border-radius:'+o.val+';background:rgba(var(--s-accent-rgb),'+(radius===o.id?'.3':'.12')+');'"></div>
                        <span style="font-size:.75rem;font-weight:700;color:#1e293b;display:block" x-text="o.label"></span>
                        <span style="font-size:.65rem;color:#94a3b8;display:block;margin-top:.1rem" x-text="o.desc"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- ── CARD: DENSIDADE DE LAYOUT ── --}}
        <div class="settings-card" x-data="{
            density: flowAppearance.get('density', 'comfortable', 'flowmanager:density') || 'comfortable',
            opts: [
                {id:'compact',icon:'▣',label:'Compacto',desc:'Mais itens na tela, espaçamento reduzido'},
                {id:'comfortable',icon:'◫',label:'Confortável',desc:'Equilíbrio entre densidade e legibilidade'},
                {id:'spacious',icon:'□',label:'Espaçoso',desc:'Mais respiração visual, fácil leitura'},
            ],
            apply(id) {
                this.density = id;
                localStorage.setItem('flowmanager:density', id);
                flowAppearance.save('density', id, 'flowmanager:density');
                document.documentElement.setAttribute('data-density', id);
            }
        }" x-init="document.documentElement.setAttribute('data-density', density)">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(16,185,129,.1);color:#10b981">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Densidade do layout</p>
                        <p class="settings-card-desc">Controla o espaco entre os elementos da interface</p>
                    </div>
                </div>
                <span class="s-badge" x-text="opts.find(o=>o.id===density)?.label || 'Confortavel'"></span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;padding:0 1.25rem 1.5rem">
                <template x-for="o in opts" :key="o.id">
                    <button type="button"
                        @click="apply(o.id)"
                        :class="density===o.id ? 'settings-density-btn s-on' : 'settings-density-btn'">
                        <span style="font-size:1.4rem;display:block;margin-bottom:.35rem" x-text="o.icon"></span>
                        <span style="font-size:.78rem;font-weight:700;color:#1e293b;display:block" x-text="o.label"></span>
                        <span style="font-size:.67rem;color:#94a3b8;display:block;margin-top:.15rem;line-height:1.3" x-text="o.desc"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- ── CARD: SIDEBAR / MENU LATERAL ── --}}
        <div class="settings-card" x-data="{
            sidebarStyle: flowAppearance.get('sidebar_style', 'icons-text', 'flowmanager:sidebar-style') || 'icons-text',
            opts: [
                {id:'icons-only',label:'Apenas ícones',desc:'Sidebar estreita, apenas ícones visíveis'},
                {id:'icons-text',label:'Ícones + Texto',desc:'Padrão: ícone e nome da seção'},
                {id:'text-only',label:'Só texto',desc:'Lista de links sem ícones'},
            ],
            apply(id) {
                this.sidebarStyle = id;
                localStorage.setItem('flowmanager:sidebar-style', id);
                flowAppearance.save('sidebar_style', id, 'flowmanager:sidebar-style');
                document.documentElement.setAttribute('data-sidebar-style', id);
            }
        }" x-init="document.documentElement.setAttribute('data-sidebar-style', sidebarStyle)">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(99,102,241,.1);color:#6366f1">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Estilo do menu lateral</p>
                        <p class="settings-card-desc">Como o menu de navegacao e exibido</p>
                    </div>
                </div>
                <span class="s-badge" x-text="opts.find(o=>o.id===sidebarStyle)?.label || 'Icones + Texto'"></span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.65rem;padding:0 1.25rem 1.5rem">
                <template x-for="o in opts" :key="o.id">
                    <button type="button"
                        @click="apply(o.id)"
                        :class="sidebarStyle===o.id ? 'settings-radius-btn s-on' : 'settings-radius-btn'">
                        <span style="font-size:.78rem;font-weight:700;color:#1e293b;display:block;margin-bottom:.2rem" x-text="o.label"></span>
                        <span style="font-size:.67rem;color:#94a3b8;display:block;line-height:1.3" x-text="o.desc"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- ── CARD: INTENSIDADE DE CORES ── --}}
        <div class="settings-card" x-data="{
            intensity: flowAppearance.get('color_intensity', 'normal', 'flowmanager:color-intensity') || 'normal',
            opts: [
                {id:'muted',label:'Suave',desc:'Tons mais claros e discretos',val:'0.6'},
                {id:'normal',label:'Normal',desc:'Intensidade padrao do tema',val:'1'},
                {id:'vivid',label:'Vibrante',desc:'Cores mais saturadas e marcantes',val:'1.3'},
            ],
            apply(id,val) {
                this.intensity = id;
                localStorage.setItem('flowmanager:color-intensity', id);
                flowAppearance.save('color_intensity', id, 'flowmanager:color-intensity');
                document.documentElement.style.setProperty('--color-intensity', val);
            }
        }" x-init="
            const cur = opts.find(o=>o.id===intensity);
            if(cur) document.documentElement.style.setProperty('--color-intensity', cur.val);
        ">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(236,72,153,.1);color:#ec4899">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Intensidade das cores</p>
                        <p class="settings-card-desc">Ajusta a saturacao do tema de cor escolhido</p>
                    </div>
                </div>
                <span class="s-badge" x-text="opts.find(o=>o.id===intensity)?.label || 'Normal'"></span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.65rem;padding:0 1.25rem 1.5rem">
                <template x-for="o in opts" :key="o.id">
                    <button type="button"
                        @click="apply(o.id, o.val)"
                        :class="intensity===o.id ? 'settings-density-btn s-on' : 'settings-density-btn'">
                        <div :style="'width:2rem;height:.45rem;border-radius:2rem;margin:0 auto .5rem;background:var(--s-accent);filter:saturate('+o.val+')'"></div>
                        <span style="font-size:.78rem;font-weight:700;color:#1e293b;display:block" x-text="o.label"></span>
                        <span style="font-size:.67rem;color:#94a3b8;display:block;margin-top:.1rem" x-text="o.desc"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- ── CARD: BARRA DE PROGRESSO / LOADING ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(14,165,233,.1);color:#0ea5e9">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Visuais adicionais</p>
                        <p class="settings-card-desc">Outros efeitos e elementos visuais da interface</p>
                    </div>
                </div>
            </div>
            <div style="padding:0 1.25rem 1.5rem;display:flex;flex-direction:column;gap:.25rem">
                {{-- Sombras --}}
                <div class="settings-toggle-row" x-data="{
                    on: flowAppearance.getBool('shadows', true, 'flowmanager:shadows'),
                    toggle() { this.on = !this.on; localStorage.setItem('flowmanager:shadows', this.on); flowAppearance.save('shadows', this.on, 'flowmanager:shadows'); document.documentElement.classList.toggle('no-shadows', !this.on); }
                }">
                    <div class="settings-toggle-info">
                        <p class="settings-toggle-title">Sombras nos cards</p>
                        <p class="settings-toggle-desc">Exibe sombra suave nos paineis e cartoes</p>
                    </div>
                    <label class="settings-toggle-switch" @click.prevent="toggle()">
                        <input type="checkbox" :checked="on" readonly>
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </label>
                </div>

                {{-- Glassmorphism --}}
                <div class="settings-toggle-row" x-data="{
                    on: flowAppearance.getBool('glass', false, 'flowmanager:glass'),
                    toggle() { this.on = !this.on; localStorage.setItem('flowmanager:glass', this.on); flowAppearance.save('glass', this.on, 'flowmanager:glass'); document.documentElement.classList.toggle('glass-mode', this.on); }
                }">
                    <div class="settings-toggle-info">
                        <p class="settings-toggle-title">Efeito vidro (Glass)</p>
                        <p class="settings-toggle-desc">Aplica transparencia e blur no header e sidebar</p>
                    </div>
                    <label class="settings-toggle-switch" @click.prevent="toggle()">
                        <input type="checkbox" :checked="on" readonly>
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </label>
                </div>

                {{-- Hover highlights --}}
                <div class="settings-toggle-row" x-data="{
                    on: flowAppearance.getBool('hover_highlight', true, 'flowmanager:hover-highlight'),
                    toggle() { this.on = !this.on; localStorage.setItem('flowmanager:hover-highlight', this.on); flowAppearance.save('hover_highlight', this.on, 'flowmanager:hover-highlight'); document.documentElement.classList.toggle('no-hover-highlight', !this.on); }
                }">
                    <div class="settings-toggle-info">
                        <p class="settings-toggle-title">Destaque ao passar o mouse</p>
                        <p class="settings-toggle-desc">Realca linhas e itens quando o cursor passa sobre eles</p>
                    </div>
                    <label class="settings-toggle-switch" @click.prevent="toggle()">
                        <input type="checkbox" :checked="on" readonly>
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </label>
                </div>

                {{-- Barra de progresso no topo --}}
                <div class="settings-toggle-row" x-data="{
                    on: flowAppearance.getBool('progress_bar', true, 'flowmanager:progress-bar'),
                    toggle() { this.on = !this.on; localStorage.setItem('flowmanager:progress-bar', this.on); flowAppearance.save('progress_bar', this.on, 'flowmanager:progress-bar'); }
                }">
                    <div class="settings-toggle-info">
                        <p class="settings-toggle-title">Barra de carregamento</p>
                        <p class="settings-toggle-desc">Exibe barra animada no topo durante navegacao</p>
                    </div>
                    <label class="settings-toggle-switch" @click.prevent="toggle()">
                        <input type="checkbox" :checked="on" readonly>
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </label>
                </div>
            </div>
        </div>

        </div>{{-- /s-col-side --}}
        </div>{{-- /s-pg-grid --}}

    </x-settings.layout>
</section>
