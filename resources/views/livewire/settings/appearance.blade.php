<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public array $palette = [];
    public array $look = [];

    public function mount(): void
    {
        $prefs = Auth::user()->preferences;
        $this->palette = is_array($prefs) ? ($prefs['appearance']['palette'] ?? []) : [];
        $this->look = is_array($prefs) ? ($prefs['appearance']['look'] ?? []) : [];
    }

    /** Cores dos cards de produto e fundo das telas, salvos na conta. */
    public function saveLook(array $value): void
    {
        $fams = ['slate', 'red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose'];
        $pick = fn ($k, array $ok, $def) => in_array($value[$k] ?? null, $ok, true) ? $value[$k] : $def;
        $cards = ['tema', 'classico', 'branco', 'grafite', 'escuro', 'noite', 'custom', 'rosa', 'menta', 'ceu', 'pessego', 'lavanda', 'limao', 'coral', 'oceano', 'uva', 'caramelo', 'cereja', 'floresta'];
        $pats = ['nenhum', 'pontos', 'bolhas', 'linhas', 'grade', 'xadrez', 'mais', 'ondas', 'zigue', 'coracoes', 'estrelas', 'folhas', 'flores', 'confete', 'diamantes'];
        $clean = [
            'cards' => $pick('cards', $cards, 'tema'),
            'tone' => $pick('tone', ['suave', 'medio', 'forte'], 'medio'),
            'shape' => $pick('shape', ['redondo', 'medio', 'reto'], 'redondo'),
            'bg' => $pick('bg', ['padrao', 'tema', 'cinza', 'areia', 'branco', 'cor'], 'padrao'),
            'bg_c' => $pick('bg_c', $fams, null),
            'pat' => $pick('pat', $pats, 'nenhum'),
            'pat_c' => $pick('pat_c', array_merge(['tema'], $fams), 'tema'),
            'pat_o' => $pick('pat_o', ['suave', 'medio', 'forte'], 'medio'),
            'side' => $pick('side', ['padrao', 'tema', 'cor', 'branco', 'cinza', 'areia'], 'padrao'),
            'tab' => $pick('tab', ['padrao', 'tema', 'cor', 'branco'], 'padrao'),
        ];
        foreach (['c_bg', 'c_border', 'c_tag', 'c_detail'] as $k) {
            $clean[$k] = in_array($value[$k] ?? null, $fams, true) ? $value[$k] : null;
        }

        $user = Auth::user();
        $prefs = is_array($user->preferences) ? $user->preferences : [];
        $appearance = is_array($prefs['appearance'] ?? null) ? $prefs['appearance'] : [];
        $appearance['look'] = $clean;
        $prefs['appearance'] = $appearance;
        $user->update(['preferences' => $prefs]);
        $this->look = $clean;
        $this->skipRender();
    }

    /** Guarda as cores na conta, para valerem em todos os aparelhos. */
    public function savePalette(array $value): void
    {
        $allowed = ['slate', 'red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose'];
        $clean = [];
        foreach (['primary', 'secondary', 'accent'] as $key) {
            $clean[$key] = in_array($value[$key] ?? null, $allowed, true) ? $value[$key] : null;
        }
        $clean['theme'] = preg_replace('/[^a-z0-9-]/', '', (string) ($value['theme'] ?? 'custom'));

        $user = Auth::user();
        $prefs = is_array($user->preferences) ? $user->preferences : [];
        $appearance = is_array($prefs['appearance'] ?? null) ? $prefs['appearance'] : [];
        $appearance['palette'] = $clean;
        $prefs['appearance'] = $appearance;
        $user->update(['preferences' => $prefs]);
        $this->palette = $clean;
        // A tela já mudou no navegador; redesenhar aqui perderia a aba ativa do cabeçalho
        $this->skipRender();
    }
}; ?>

@php
    // Temas prontos: principal (índigo/violeta), apoio (roxo/fúcsia) e destaque (rosa)
    $themes = [
        ['flow', 'FlowManager', 'O visual padrão do app', 'indigo', 'purple', 'pink'],
        ['oceano', 'Oceano', 'Azul calmo e confiável', 'blue', 'cyan', 'sky'],
        ['esmeralda', 'Esmeralda', 'Verde de dinheiro no caixa', 'emerald', 'teal', 'lime'],
        ['por-do-sol', 'Pôr do sol', 'Laranja quente com rosa', 'orange', 'rose', 'amber'],
        ['framboesa', 'Framboesa', 'Rosa forte e alegre', 'pink', 'rose', 'fuchsia'],
        ['lavanda', 'Lavanda', 'Violeta suave', 'violet', 'fuchsia', 'pink'],
        ['ceu', 'Céu', 'Azul claro e leve', 'sky', 'blue', 'cyan'],
        ['floresta', 'Floresta', 'Verde natural', 'green', 'emerald', 'lime'],
        ['vinho', 'Vinho', 'Elegante e marcante', 'rose', 'red', 'pink'],
        ['ambar', 'Âmbar', 'Dourado e acolhedor', 'amber', 'orange', 'yellow'],
        ['grafite', 'Grafite', 'Sóbrio, quase sem cor', 'slate', 'blue', 'sky'],
        ['cereja', 'Cereja', 'Vermelho com energia', 'red', 'rose', 'orange'],
    ];
    $families = [
        ['indigo', 'Índigo'], ['violet', 'Violeta'], ['purple', 'Roxo'], ['fuchsia', 'Fúcsia'], ['pink', 'Rosa'], ['rose', 'Rosado'],
        ['red', 'Vermelho'], ['orange', 'Laranja'], ['amber', 'Âmbar'], ['yellow', 'Amarelo'], ['lime', 'Lima'], ['green', 'Verde'],
        ['emerald', 'Esmeralda'], ['teal', 'Turquesa'], ['cyan', 'Ciano'], ['sky', 'Céu'], ['blue', 'Azul'], ['slate', 'Grafite'],
    ];
@endphp

<section class="settings-appearance-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    <div class="ap-page" x-data="fmAppearance(@js($palette), @js($look), @js(array_map(fn ($t) => ['id' => $t[0], 'name' => $t[1], 'primary' => $t[3], 'secondary' => $t[4], 'accent' => $t[5]], $themes)))" x-init="init()">

        <div class="ap-grid">
            <div class="ap-main">

                {{-- Temas prontos --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500"><i class="bi bi-palette2"></i></span>
                        <div class="min-w-0">
                            <h2>Tema do app</h2>
                            <p>Escolha um tema pronto. Só as cores mudam, as telas continuam no mesmo formato.</p>
                        </div>
                        <span class="ap-saved" x-show="savedAt" x-transition.opacity x-cloak><i class="bi bi-cloud-check"></i> Salvo na conta</span>
                    </div>
                    <div class="ap-themes">
                        @foreach($themes as [$id, $name, $desc, $p, $s, $a])
                            <button type="button" class="ap-theme" :class="{ 'is-on': theme === '{{ $id }}' }" @click="pickTheme('{{ $id }}')">
                                <span class="ap-theme-art" style="--p: var(--fm-sw-{{ $p }}); --s: var(--fm-sw-{{ $s }}); --a: var(--fm-sw-{{ $a }})">
                                    <span class="ap-theme-side"></span>
                                    <span class="ap-theme-body">
                                        <span class="ap-theme-bar"></span>
                                        <span class="ap-theme-line"></span>
                                        <span class="ap-theme-line w-2/3"></span>
                                        <span class="ap-theme-btn"></span>
                                    </span>
                                </span>
                                <span class="ap-theme-name">{{ $name }}</span>
                                <span class="ap-theme-desc">{{ $desc }}</span>
                                <span class="ap-theme-check"><i class="bi bi-check-lg"></i></span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Cores sob medida --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-pink-500 to-orange-400"><i class="bi bi-eyedropper"></i></span>
                        <div class="min-w-0">
                            <h2>Monte suas cores</h2>
                            <p>Troque cada cor do app separadamente. Vale para botões, menus, abas, gráficos e destaques.</p>
                        </div>
                    </div>
                    @foreach([['primary', 'Cor principal', 'Menu ativo, botões e títulos'], ['secondary', 'Cor de apoio', 'Degradês, ícones e etiquetas'], ['accent', 'Cor de destaque', 'Brilhos e detalhes dos degradês']] as [$slot, $label, $hint])
                        <div class="ap-row">
                            <div class="ap-row-label">
                                <span class="ap-row-dot" :style="'background: var(--fm-sw-' + pal.{{ $slot }} + ')'"></span>
                                <div>
                                    <p>{{ $label }}</p>
                                    <small>{{ $hint }}</small>
                                </div>
                            </div>
                            <div class="ap-swatches">
                                @foreach($families as [$fam, $famName])
                                    <button type="button" class="ap-swatch" title="{{ $famName }}" style="background: var(--fm-sw-{{ $fam }})"
                                            :class="{ 'is-on': pal.{{ $slot }} === '{{ $fam }}' }" @click="pickColor('{{ $slot }}', '{{ $fam }}')">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <div class="ap-actions">
                        <button type="button" class="ap-btn-ghost" @click="reset()"><i class="bi bi-arrow-counterclockwise"></i> Voltar ao padrão</button>
                    </div>
                </div>

                {{-- Cards de produto --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-violet-500 to-pink-500"><i class="bi bi-postcard-heart"></i></span>
                        <div class="min-w-0">
                            <h2>Cores dos cards de produto</h2>
                            <p>Os cards da lista de produtos, das vendas, dos kits e das promoções. "Acompanhar o tema" muda junto quando você troca o tema.</p>
                        </div>
                        <span class="ap-saved" x-show="lookSavedAt" x-transition.opacity x-cloak><i class="bi bi-cloud-check"></i> Salvo</span>
                    </div>
                    <template x-for="g in cardGroups" :key="g.label">
                        <div class="ap-group">
                            <p class="ap-group-label" x-text="g.label"></p>
                            <div class="ap-cardstyles">
                                <template x-for="m in g.items" :key="m.id">
                                    <button type="button" class="ap-cs" :class="{ 'is-on': look.cards === m.id }" @click="pickCards(m.id)">
                                        <span class="ap-cs-art" :style="cardArt(m.id)">
                                            <span class="ap-cs-img"></span>
                                            <span class="ap-cs-name"></span>
                                            <span class="ap-cs-prices"><span></span><span></span></span>
                                        </span>
                                        <span class="ap-cs-label" x-text="m.label"></span>
                                        <span class="ap-cs-desc" x-text="m.desc" x-show="m.desc"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div x-show="look.cards === 'custom'" x-cloak class="mt-2">
                        @foreach([['c_bg', 'Fundo do card', 'A cor clara atrás do nome'], ['c_border', 'Borda e nome', 'Contorno do card e cor do nome'], ['c_tag', 'Etiquetas', 'Código e preço de custo'], ['c_detail', 'Detalhes', 'Moldura da foto e preço de venda']] as [$slot, $label, $hint])
                            <div class="ap-row">
                                <div class="ap-row-label">
                                    <span class="ap-row-dot" :style="'background: var(--fm-sw-' + (look.{{ $slot }} || customDefault.{{ $slot }}) + ')'"></span>
                                    <div>
                                        <p>{{ $label }}</p>
                                        <small>{{ $hint }}</small>
                                    </div>
                                </div>
                                <div class="ap-swatches">
                                    @foreach($families as [$fam, $famName])
                                        <button type="button" class="ap-swatch" title="{{ $famName }}" style="background: var(--fm-sw-{{ $fam }})"
                                                :class="{ 'is-on': (look.{{ $slot }} || customDefault.{{ $slot }}) === '{{ $fam }}' }" @click="pickCardColor('{{ $slot }}', '{{ $fam }}')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="ap-two">
                        <div>
                            <p class="ap-label">Intensidade da cor</p>
                            <div class="ap-seg mt-2">
                                <template x-for="t in [{id:'suave',label:'Suave'},{id:'medio',label:'Média'},{id:'forte',label:'Forte'}]" :key="t.id">
                                    <button type="button" :class="{ 'is-on': (look.tone || 'medio') === t.id }" @click="setLook('tone', t.id)" x-text="t.label"></button>
                                </template>
                            </div>
                        </div>
                        <div>
                            <p class="ap-label">Cantos do card</p>
                            <div class="ap-seg mt-2">
                                <template x-for="t in [{id:'redondo',label:'Redondos',r:'.7rem'},{id:'medio',label:'Médios',r:'.4rem'},{id:'reto',label:'Retos',r:'.12rem'}]" :key="t.id">
                                    <button type="button" :class="{ 'is-on': (look.shape || 'redondo') === t.id }" @click="setLook('shape', t.id)"><span class="ap-shape" :style="'border-radius:' + t.r"></span><span x-text="t.label"></span></button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fundo das telas --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-amber-400 to-rose-500"><i class="bi bi-image"></i></span>
                        <div class="min-w-0">
                            <h2>Fundo das telas</h2>
                            <p>A cor e o desenho atrás dos cards em todas as páginas. O desenho aparece também no modo escuro.</p>
                        </div>
                    </div>
                    <p class="ap-label">Cor do fundo</p>
                    <div class="ap-bgs mt-2">
                        <template x-for="b in bgModes" :key="b.id">
                            <button type="button" class="ap-bg" :class="{ 'is-on': (look.bg || 'padrao') === b.id }" @click="pickBg(b.id)">
                                <span class="ap-bg-art" :style="'background:' + bgArt(b.id)"><span></span><span></span></span>
                                <span x-text="b.label"></span>
                            </button>
                        </template>
                    </div>
                    <div class="ap-row" x-show="look.bg === 'cor'" x-cloak>
                        <div class="ap-row-label">
                            <span class="ap-row-dot" :style="'background: var(--fm-sw-' + (look.bg_c || 'indigo') + ')'"></span>
                            <div><p>Qual cor</p><small>Fica bem clarinha no fundo</small></div>
                        </div>
                        <div class="ap-swatches">
                            @foreach($families as [$fam, $famName])
                                <button type="button" class="ap-swatch" title="{{ $famName }}" style="background: var(--fm-sw-{{ $fam }})"
                                        :class="{ 'is-on': (look.bg_c || 'indigo') === '{{ $fam }}' }" @click="look.bg_c = '{{ $fam }}'; look.bg = 'cor'; applyLook()">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <p class="ap-label mt-4">Desenho no fundo</p>
                    <div class="ap-pats mt-2">
                        <template x-for="pt in patterns" :key="pt.id">
                            <button type="button" class="ap-pat" :class="{ 'is-on': (look.pat || 'nenhum') === pt.id }" @click="setLook('pat', pt.id)">
                                <span class="ap-pat-art" :style="patArt(pt.id)"><i class="bi bi-slash-circle" x-show="pt.id === 'nenhum'"></i></span>
                                <span x-text="pt.label"></span>
                            </button>
                        </template>
                    </div>
                    <div x-show="look.pat && look.pat !== 'nenhum'" x-cloak>
                        <div class="ap-row">
                            <div class="ap-row-label">
                                <span class="ap-row-dot" :style="'background:' + (look.pat_c && look.pat_c !== 'tema' ? 'var(--fm-sw-' + look.pat_c + ')' : 'var(--color-indigo-500)')"></span>
                                <div><p>Cor do desenho</p><small>"Do tema" muda junto com o tema</small></div>
                            </div>
                            <div class="ap-swatches">
                                <button type="button" class="ap-swatch ap-swatch-theme" title="Do tema" :class="{ 'is-on': !look.pat_c || look.pat_c === 'tema' }" @click="setLook('pat_c', 'tema')"><i class="bi bi-check-lg"></i></button>
                                @foreach($families as [$fam, $famName])
                                    <button type="button" class="ap-swatch" title="{{ $famName }}" style="background: var(--fm-sw-{{ $fam }})"
                                            :class="{ 'is-on': look.pat_c === '{{ $fam }}' }" @click="setLook('pat_c', '{{ $fam }}')">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="ap-row">
                            <div class="ap-row-label"><div><p>Quanto aparece</p><small>Suave fica bem discreto</small></div></div>
                            <div class="ap-seg">
                                <template x-for="t in [{id:'suave',label:'Suave'},{id:'medio',label:'Médio'},{id:'forte',label:'Forte'}]" :key="t.id">
                                    <button type="button" :class="{ 'is-on': (look.pat_o || 'medio') === t.id }" @click="setLook('pat_o', t.id)" x-text="t.label"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Menu e barra --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-indigo-500 to-sky-500"><i class="bi bi-layout-sidebar-inset"></i></span>
                        <div class="min-w-0">
                            <h2>Menu lateral e barra do celular</h2>
                            <p>A cor do menu do computador e da barra de baixo do celular. Vale no modo claro. Para esconder áreas do menu, use <a href="{{ route('settings.menu') }}" wire:navigate class="font-bold text-indigo-600 dark:text-indigo-300">Menu do app</a>.</p>
                        </div>
                    </div>
                    <p class="ap-label">Menu lateral</p>
                    <div class="ap-sides mt-2">
                        <template x-for="m in sideModes" :key="m.id">
                            <button type="button" class="ap-sidebtn" :class="{ 'is-on': (look.side || 'padrao') === m.id }" @click="setLook('side', m.id)">
                                <span class="ap-side-art"><span :style="'background:' + sideArt(m.id)"><i></i><i></i><i></i></span><b></b></span>
                                <span x-text="m.label"></span>
                            </button>
                        </template>
                    </div>
                    <p class="ap-label mt-4">Barra de baixo do celular</p>
                    <div class="ap-seg mt-2">
                        <template x-for="m in tabModes" :key="m.id">
                            <button type="button" :class="{ 'is-on': (look.tab || 'padrao') === m.id }" @click="setLook('tab', m.id)"><span class="ap-tabdot" :style="'background:' + tabArt(m.id)"></span><span x-text="m.label"></span></button>
                        </template>
                    </div>
                </div>

                {{-- Texto e leitura --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-sky-500 to-indigo-500"><i class="bi bi-fonts"></i></span>
                        <div class="min-w-0">
                            <h2>Texto e leitura</h2>
                            <p>Fonte, tamanho das letras e espaçamento. Fica salvo neste aparelho.</p>
                        </div>
                    </div>
                    <div class="ap-fonts">
                        <template x-for="f in fonts" :key="f.id">
                            <button type="button" class="ap-font" :class="{ 'is-on': font === f.id }" @click="pickFont(f.id)">
                                <span class="ap-font-sample" :style="f.css ? 'font-family:' + f.css : ''">Aa</span>
                                <span class="ap-font-name" x-text="f.label"></span>
                            </button>
                        </template>
                    </div>
                    <div class="ap-size">
                        <div class="flex items-center justify-between">
                            <p class="ap-label">Tamanho das letras</p>
                            <span class="ap-size-val" x-text="sizeLabel()"></span>
                        </div>
                        <div class="ap-size-steps">
                            <template x-for="n in [12, 13, 14, 15, 16, 17, 18]" :key="n">
                                <button type="button" :class="{ 'is-on': size === n }" @click="pickSize(n)" :style="'font-size:' + (n - 1) + 'px'">A</button>
                            </template>
                        </div>
                    </div>
                    <div class="ap-toggles">
                        <label class="ap-toggle">
                            <span><b>Modo compacto</b><small>Menos espaço entre os itens para ver mais coisas na tela</small></span>
                            <input type="checkbox" :checked="compact" @change="setCompact($event.target.checked)">
                            <i></i>
                        </label>
                        <label class="ap-toggle">
                            <span><b>Reduzir animações</b><small>Tira os efeitos de movimento ao abrir telas e janelas</small></span>
                            <input type="checkbox" :checked="noAnim" @change="setNoAnim($event.target.checked)">
                            <i></i>
                        </label>
                    </div>
                </div>
            </div>

            <aside class="ap-side">
                {{-- Claro / escuro --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-slate-600 to-slate-900"><i class="bi bi-circle-half"></i></span>
                        <div class="min-w-0">
                            <h2>Claro ou escuro</h2>
                            <p>Fica salvo neste aparelho.</p>
                        </div>
                    </div>
                    <div class="ap-modes">
                        <template x-for="m in [{id:'light',label:'Claro',icon:'bi-sun'},{id:'dark',label:'Escuro',icon:'bi-moon-stars'},{id:'system',label:'Automático',icon:'bi-phone'}]" :key="m.id">
                            <button type="button" class="ap-mode" :class="{ 'is-on': mode === m.id }" @click="pickMode(m.id)">
                                <span class="ap-mode-art" :class="'ap-mode-' + m.id"></span>
                                <span><i class="bi" :class="m.icon"></i> <span x-text="m.label"></span></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Navegação no iPad --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-blue-500 to-cyan-500"><i class="bi bi-tablet-landscape"></i></span>
                        <div class="min-w-0">
                            <h2>Menu no iPad deitado</h2>
                            <p>Menu lateral ou barra embaixo, como no celular.</p>
                        </div>
                    </div>
                    <div class="ap-seg">
                        <button type="button" :class="{ 'is-on': tabletNav === 'sidebar' }" @click="pickTabletNav('sidebar')"><i class="bi bi-layout-sidebar"></i> Menu lateral</button>
                        <button type="button" :class="{ 'is-on': tabletNav === 'tabbar' }" @click="pickTabletNav('tabbar')"><i class="bi bi-segmented-nav"></i> Barra embaixo</button>
                    </div>
                </div>
            </aside>
        </div>

        {{-- Prévia numa janela: dá para ver o app inteiro e trocar as escolhas ali mesmo --}}
        <button type="button" class="ap-pv-fab bg-gradient-to-r from-indigo-600 to-purple-600" @click="openPreview()"><i class="bi bi-eye"></i> Ver prévia</button>
        <template x-teleport="body">
            <div class="ap-modal" x-show="pvOpen" x-cloak x-transition.opacity @keydown.escape.window="pvOpen = false">
                <div class="ap-modal-back" @click="pvOpen = false"></div>
                <div class="ap-modal-box" role="dialog" aria-label="Prévia do app">
                    <div class="ap-modal-head">
                        <span class="ap-ico bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-eye"></i></span>
                        <div class="min-w-0 flex-1">
                            <h2>Prévia do app</h2>
                            <p>Troque aqui e veja na hora. Tudo já fica salvo.</p>
                        </div>
                        <div class="ap-seg ap-dev">
                            <button type="button" :class="{ 'is-on': pvDev === 'pc' }" @click="pvDev = 'pc'"><i class="bi bi-laptop"></i><span>Computador</span></button>
                            <button type="button" :class="{ 'is-on': pvDev === 'cel' }" @click="pvDev = 'cel'"><i class="bi bi-phone"></i><span>Celular</span></button>
                        </div>
                        <button type="button" class="ap-modal-x" @click="pvOpen = false" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="ap-modal-body">
                        <div class="ap-stage" :class="{ 'is-dark': dark }">
                            {{-- Computador --}}
                            <div class="ap-mock-pc" x-show="pvDev === 'pc'" :style="{ background: previewBg() }">
                                <div class="ap-mk-side" :style="'background:' + (dark ? '#0f172a' : sideArt(look.side || 'padrao'))">
                                    <span class="ap-mk-logo bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500"></span>
                                    <span class="ap-mk-item is-on bg-gradient-to-r from-indigo-500/15 to-purple-500/15 text-indigo-700 dark:text-indigo-300"><i class="bi bi-house"></i> Início</span>
                                    <span class="ap-mk-item"><i class="bi bi-bag"></i> Vendas</span>
                                    <span class="ap-mk-item"><i class="bi bi-box-seam"></i> Produtos</span>
                                    <span class="ap-mk-item"><i class="bi bi-wallet2"></i> Finanças</span>
                                    <span class="ap-mk-item"><i class="bi bi-gear"></i> Configurações</span>
                                </div>
                                <div class="ap-mk-main">
                                    <div class="ap-mk-head bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500">
                                        <div><b>Vendas do mês</b><span>R$ 12.480,00</span></div>
                                        <span class="ap-mk-pill"><i class="bi bi-plus-lg"></i> Nova venda</span>
                                    </div>
                                    <div class="ap-mk-stats">
                                        <span class="bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="bi bi-graph-up"></i> 32 vendas</span>
                                        <span class="bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-300"><i class="bi bi-people"></i> 18 clientes</span>
                                        <span class="bg-pink-50 text-pink-700 dark:bg-pink-500/15 dark:text-pink-300"><i class="bi bi-box-seam"></i> 4 em falta</span>
                                    </div>
                                    <div class="ap-mk-cards">
                                        @foreach([['Batom matte', 'R$ 32,90', 'from-pink-300 to-rose-400'], ['Hidratante', 'R$ 45,00', 'from-sky-300 to-indigo-400'], ['Perfume floral', 'R$ 129,90', 'from-amber-200 to-orange-400'], ['Sabonete', 'R$ 12,50', 'from-emerald-200 to-teal-400']] as [$pn, $pp, $pg])
                                            <div class="ap-pc" :style="'border-radius:' + mockRadius()">
                                                <span class="ap-pc-code">P00{{ $loop->iteration }}</span>
                                                <span class="ap-pc-img"><span class="bg-gradient-to-br {{ $pg }}"></span></span>
                                                <b>{{ $pn }}</b>
                                                <span class="ap-pc-prices"><span>R$ 18,00</span><span>{{ $pp }}</span></span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            {{-- Celular --}}
                            <div class="ap-mock-cel" x-show="pvDev === 'cel'" :style="{ background: previewBg() }">
                                <div class="ap-mk-cel-top"><b>Produtos</b><i class="bi bi-bell"></i></div>
                                <div class="ap-mk-head bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500"><div><b>Em estoque</b><span>148 itens</span></div></div>
                                <div class="ap-mk-cards is-cel">
                                    @foreach([['Batom matte', 'R$ 32,90', 'from-pink-300 to-rose-400'], ['Hidratante', 'R$ 45,00', 'from-sky-300 to-indigo-400']] as [$pn, $pp, $pg])
                                        <div class="ap-pc" :style="'border-radius:' + mockRadius()">
                                            <span class="ap-pc-code">P00{{ $loop->iteration }}</span>
                                            <span class="ap-pc-img"><span class="bg-gradient-to-br {{ $pg }}"></span></span>
                                            <b>{{ $pn }}</b>
                                            <span class="ap-pc-prices"><span>R$ 18,00</span><span>{{ $pp }}</span></span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="ap-mk-tab" :style="'background:' + (dark ? '#0a0e1e' : tabArt(look.tab || 'padrao'))">
                                    <span><i class="bi bi-house"></i></span><span><i class="bi bi-bag"></i></span>
                                    <span class="ap-mk-tab-mid bg-gradient-to-br from-indigo-500 to-purple-600"><i class="bi bi-plus-lg"></i></span>
                                    <span class="is-on text-indigo-600 dark:text-indigo-300"><i class="bi bi-box-seam"></i></span><span><i class="bi bi-list"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="ap-quick">
                            <div class="ap-quick-tabs">
                                <template x-for="t in [{id:'tema',label:'Tema',icon:'bi-palette2'},{id:'cards',label:'Cards',icon:'bi-postcard-heart'},{id:'fundo',label:'Fundo',icon:'bi-image'},{id:'menu',label:'Menu',icon:'bi-layout-sidebar-inset'}]" :key="t.id">
                                    <button type="button" :class="{ 'is-on': pvTab === t.id }" @click="pvTab = t.id"><i class="bi" :class="t.icon"></i> <span x-text="t.label"></span></button>
                                </template>
                            </div>
                            <div class="ap-quick-body">
                                <div x-show="pvTab === 'tema'" class="ap-chips">
                                    <template x-for="t in themes" :key="t.id">
                                        <button type="button" class="ap-chip" :class="{ 'is-on': theme === t.id }" @click="pickTheme(t.id)">
                                            <span class="ap-chip-dots"><i :style="'background:var(--fm-sw-' + t.primary + ')'"></i><i :style="'background:var(--fm-sw-' + t.secondary + ')'"></i><i :style="'background:var(--fm-sw-' + t.accent + ')'"></i></span>
                                            <span x-text="t.name"></span>
                                        </button>
                                    </template>
                                    <div class="ap-chips-row">
                                        <span class="ap-mini-label">Claro ou escuro</span>
                                        <div class="ap-seg">
                                            <button type="button" :class="{ 'is-on': mode === 'light' }" @click="pickMode('light')"><i class="bi bi-sun"></i> Claro</button>
                                            <button type="button" :class="{ 'is-on': mode === 'dark' }" @click="pickMode('dark')"><i class="bi bi-moon-stars"></i> Escuro</button>
                                        </div>
                                    </div>
                                </div>
                                <div x-show="pvTab === 'cards'" x-cloak class="ap-chips">
                                    <template x-for="m in allCardModes()" :key="m.id">
                                        <button type="button" class="ap-chip" :class="{ 'is-on': look.cards === m.id }" @click="pickCards(m.id)">
                                            <span class="ap-chip-card" :style="cardArt(m.id)"></span>
                                            <span x-text="m.label"></span>
                                        </button>
                                    </template>
                                    <div class="ap-chips-row">
                                        <span class="ap-mini-label">Intensidade</span>
                                        <div class="ap-seg">
                                            <template x-for="t in [{id:'suave',label:'Suave'},{id:'medio',label:'Média'},{id:'forte',label:'Forte'}]" :key="t.id">
                                                <button type="button" :class="{ 'is-on': (look.tone || 'medio') === t.id }" @click="setLook('tone', t.id)" x-text="t.label"></button>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="ap-chips-row">
                                        <span class="ap-mini-label">Cantos</span>
                                        <div class="ap-seg">
                                            <template x-for="t in [{id:'redondo',label:'Redondos'},{id:'medio',label:'Médios'},{id:'reto',label:'Retos'}]" :key="t.id">
                                                <button type="button" :class="{ 'is-on': (look.shape || 'redondo') === t.id }" @click="setLook('shape', t.id)" x-text="t.label"></button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div x-show="pvTab === 'fundo'" x-cloak class="ap-chips">
                                    <template x-for="b in bgModes" :key="b.id">
                                        <button type="button" class="ap-chip" :class="{ 'is-on': (look.bg || 'padrao') === b.id }" @click="pickBg(b.id)">
                                            <span class="ap-chip-sq" :style="'background:' + bgArt(b.id)"></span><span x-text="b.label"></span>
                                        </button>
                                    </template>
                                    <span class="ap-mini-label w-full">Desenho</span>
                                    <template x-for="pt in patterns" :key="pt.id">
                                        <button type="button" class="ap-chip" :class="{ 'is-on': (look.pat || 'nenhum') === pt.id }" @click="setLook('pat', pt.id)">
                                            <span class="ap-chip-sq ap-chip-pat" :style="patArt(pt.id)"></span><span x-text="pt.label"></span>
                                        </button>
                                    </template>
                                    <div class="ap-chips-row" x-show="look.pat && look.pat !== 'nenhum'">
                                        <span class="ap-mini-label">Quanto aparece</span>
                                        <div class="ap-seg">
                                            <template x-for="t in [{id:'suave',label:'Suave'},{id:'medio',label:'Médio'},{id:'forte',label:'Forte'}]" :key="t.id">
                                                <button type="button" :class="{ 'is-on': (look.pat_o || 'medio') === t.id }" @click="setLook('pat_o', t.id)" x-text="t.label"></button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div x-show="pvTab === 'menu'" x-cloak class="ap-chips">
                                    <span class="ap-mini-label w-full">Menu lateral</span>
                                    <template x-for="m in sideModes" :key="m.id">
                                        <button type="button" class="ap-chip" :class="{ 'is-on': (look.side || 'padrao') === m.id }" @click="setLook('side', m.id); pvDev = 'pc'">
                                            <span class="ap-chip-sq" :style="'background:' + sideArt(m.id)"></span><span x-text="m.label"></span>
                                        </button>
                                    </template>
                                    <span class="ap-mini-label w-full">Barra do celular</span>
                                    <template x-for="m in tabModes" :key="m.id">
                                        <button type="button" class="ap-chip" :class="{ 'is-on': (look.tab || 'padrao') === m.id }" @click="setLook('tab', m.id); pvDev = 'cel'">
                                            <span class="ap-chip-sq" :style="'background:' + tabArt(m.id)"></span><span x-text="m.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <style>
        .ap-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 1rem; align-items: start; }
        .ap-main, .ap-side { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .ap-side { position: sticky; top: 1rem; }

        .ap-themes { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: .7rem; }
        .ap-theme { position: relative; text-align: left; border: 2px solid #e2e8f0; border-radius: 1rem; padding: .5rem .5rem .65rem; background: #f8fafc; transition: transform .15s, border-color .15s, box-shadow .15s; }
        .ap-theme:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -16px rgba(15, 23, 42, .4); }
        .ap-theme.is-on { border-color: var(--color-indigo-600); background: #fff; box-shadow: 0 0 0 4px color-mix(in oklab, var(--color-indigo-500) 18%, transparent); }
        .dark .ap-theme { background: #111827; border-color: #1f2937; }
        .dark .ap-theme.is-on { background: #1e293b; border-color: var(--color-indigo-600); }
        .ap-theme-art { display: flex; height: 4.6rem; border-radius: .7rem; overflow: hidden; background: #fff; border: 1px solid #e2e8f0; }
        .dark .ap-theme-art { background: #0b1220; border-color: #1f2937; }
        .ap-theme-side { width: 26%; background: linear-gradient(180deg, var(--p), var(--s)); }
        .ap-theme-body { flex: 1; padding: .4rem; display: flex; flex-direction: column; gap: .28rem; }
        .ap-theme-bar { height: .7rem; border-radius: .3rem; background: linear-gradient(90deg, var(--p), var(--s), var(--a)); }
        .ap-theme-line { height: .3rem; border-radius: 1rem; background: #e2e8f0; }
        .dark .ap-theme-line { background: #1f2937; }
        .ap-theme-btn { margin-top: auto; align-self: flex-end; width: 42%; height: .6rem; border-radius: 1rem; background: var(--p); }
        .ap-theme-name { display: block; margin-top: .5rem; font-size: .82rem; font-weight: 800; color: #0f172a; }
        .ap-theme-desc { display: block; font-size: .68rem; color: #64748b; line-height: 1.3; }
        .dark .ap-theme-name { color: #f1f5f9; }
        .dark .ap-theme-desc { color: #94a3b8; }
        .ap-theme-check { position: absolute; top: .9rem; right: .9rem; display: none; place-items: center; width: 1.4rem; height: 1.4rem; border-radius: 999px; background: #fff; color: #0f172a; font-size: .85rem; box-shadow: 0 4px 10px rgba(15, 23, 42, .25); }
        .ap-theme.is-on .ap-theme-check { display: grid; }

        .ap-row { display: grid; grid-template-columns: 200px minmax(0, 1fr); gap: .75rem; align-items: center; padding: .75rem 0; border-top: 1px dashed #e2e8f0; }
        .dark .ap-row { border-color: #1e293b; }
        .ap-row-label { display: flex; align-items: center; gap: .6rem; }
        .ap-row-label p { font-size: .85rem; font-weight: 700; color: #0f172a; }
        .ap-row-label small { display: block; font-size: .7rem; color: #64748b; }
        .dark .ap-row-label p { color: #f1f5f9; }
        .dark .ap-row-label small { color: #94a3b8; }
        .ap-row-dot { flex: none; width: 1.6rem; height: 1.6rem; border-radius: .55rem; box-shadow: inset 0 0 0 2px rgba(255, 255, 255, .5), 0 4px 10px -4px rgba(15, 23, 42, .5); }
        .ap-swatches { display: flex; flex-wrap: wrap; gap: .4rem; }
        .ap-swatch { display: grid; place-items: center; width: 1.85rem; height: 1.85rem; border-radius: 999px; color: transparent; transition: transform .12s; box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .08); }
        .ap-swatch:hover { transform: scale(1.12); }
        .ap-swatch.is-on { color: #fff; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #0f172a; }
        .dark .ap-swatch.is-on { box-shadow: 0 0 0 2px #0f172a, 0 0 0 4px #f1f5f9; }
        .ap-actions { display: flex; justify-content: flex-end; padding-top: .75rem; border-top: 1px dashed #e2e8f0; }
        .dark .ap-actions { border-color: #1e293b; }

        .ap-fonts { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .5rem; }
        .ap-font { display: flex; flex-direction: column; align-items: center; gap: .15rem; padding: .6rem .3rem; border-radius: .9rem; border: 2px solid #e2e8f0; background: #f8fafc; }
        .ap-font.is-on { border-color: var(--color-indigo-600); background: #fff; }
        .dark .ap-font { background: #111827; border-color: #1f2937; }
        .dark .ap-font.is-on { border-color: var(--color-indigo-600); background: #1e293b; }
        .ap-font-sample { font-size: 1.35rem; font-weight: 700; color: #0f172a; line-height: 1.1; }
        .ap-font-name { font-size: .7rem; font-weight: 600; color: #64748b; }
        .dark .ap-font-sample { color: #f1f5f9; }
        .ap-size { margin-top: 1rem; }
        .ap-label { font-size: .82rem; font-weight: 700; color: #0f172a; }
        .dark .ap-label { color: #f1f5f9; }
        .ap-size-val { font-size: .75rem; font-weight: 700; color: var(--color-indigo-600); }
        .ap-size-steps { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: .35rem; margin-top: .5rem; }
        .ap-size-steps button { height: 2.4rem; border-radius: .7rem; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: 800; color: #334155; }
        .ap-size-steps button.is-on { background: var(--color-indigo-600); border-color: transparent; color: #fff; }
        .dark .ap-size-steps button { background: #111827; border-color: #1f2937; color: #cbd5e1; }
        .dark .ap-size-steps button.is-on { background: var(--color-indigo-600); color: #fff; }

        .ap-preview { display: flex; gap: .5rem; padding: .5rem; border-radius: 1rem; background: #f1f5f9; border: 1px solid #e2e8f0; }
        .dark .ap-preview { background: #020617; border-color: #1e293b; }
        .ap-pv-side { width: 38%; display: flex; flex-direction: column; gap: .3rem; padding: .45rem; border-radius: .8rem; background: #fff; }
        .dark .ap-pv-side { background: #0f172a; }
        .ap-pv-logo { width: 1.6rem; height: 1.6rem; border-radius: .5rem; margin-bottom: .3rem; }
        .ap-pv-item { display: flex; align-items: center; gap: .35rem; font-size: .66rem; font-weight: 600; color: #64748b; padding: .3rem .4rem; border-radius: .5rem; white-space: nowrap; overflow: hidden; }
        .ap-pv-item.is-on { font-weight: 800; }
        .ap-pv-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: .4rem; }
        .ap-pv-head { border-radius: .8rem; padding: .55rem .65rem; color: #fff; }
        .ap-pv-head b { display: block; font-size: .62rem; opacity: .9; }
        .ap-pv-head span { font-size: .95rem; font-weight: 800; }
        .ap-pv-stats { display: flex; flex-wrap: wrap; gap: .3rem; }
        .ap-pv-stats span { font-size: .6rem; font-weight: 700; padding: .2rem .4rem; border-radius: .5rem; white-space: nowrap; }
        .ap-pv-bars { display: flex; align-items: flex-end; gap: .25rem; height: 3.6rem; padding: .35rem; border-radius: .7rem; background: #fff; }
        .dark .ap-pv-bars { background: #0f172a; }
        .ap-pv-bars span { flex: 1; border-radius: .25rem .25rem .1rem .1rem; }
        .ap-pv-tabs { display: flex; gap: .2rem; padding: .2rem; border-radius: .6rem; background: #fff; }
        .dark .ap-pv-tabs { background: #0f172a; }
        .ap-pv-tabs span { flex: 1; text-align: center; font-size: .58rem; font-weight: 700; color: #64748b; padding: .25rem 0; border-radius: .45rem; }
        .ap-pv-tabs span.text-white { color: #fff; }
        .ap-pv-btn { display: flex; align-items: center; justify-content: center; gap: .3rem; color: #fff; font-size: .68rem; font-weight: 800; padding: .45rem; border-radius: .7rem; }

        .ap-cardstyles { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: .55rem; }
        .ap-cs { position: relative; display: flex; flex-direction: column; gap: .2rem; text-align: left; padding: .45rem .45rem .6rem; border-radius: 1rem; border: 2px solid #e2e8f0; background: #f8fafc; transition: transform .15s, border-color .15s; }
        .ap-cs:hover { transform: translateY(-2px); }
        .ap-cs.is-on { border-color: var(--color-indigo-600); background: #fff; box-shadow: 0 0 0 4px color-mix(in oklab, var(--color-indigo-500) 18%, transparent); }
        .dark .ap-cs { background: #111827; border-color: #1f2937; }
        .dark .ap-cs.is-on { background: #1e293b; border-color: var(--color-indigo-600); }
        .ap-cs-art { display: flex; flex-direction: column; gap: .25rem; padding: .3rem; border-radius: .7rem; background: var(--a-bg); border: 2px solid var(--a-border); height: 5.6rem; }
        .ap-cs-img { flex: 1; border-radius: .45rem; background: #fff; border: 2px solid var(--a-detail); }
        .ap-cs-name { height: .32rem; width: 70%; margin: .1rem auto 0; border-radius: 1rem; background: var(--a-title); opacity: .75; }
        .ap-cs-prices { display: flex; justify-content: space-between; gap: .3rem; }
        .ap-cs-prices span { height: .55rem; width: 42%; border-radius: .25rem; background: linear-gradient(90deg, var(--a-tag), var(--a-border)); }
        .ap-cs-prices span + span { background: linear-gradient(90deg, var(--a-danger), var(--a-tag)); }
        .ap-cs-label { margin-top: .3rem; font-size: .78rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
        .ap-cs-desc { font-size: .66rem; color: #64748b; line-height: 1.25; }
        .dark .ap-cs-label { color: #f1f5f9; }
        .dark .ap-cs-desc { color: #94a3b8; }
        .ap-bgs { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .55rem; }
        .ap-bg { display: flex; flex-direction: column; align-items: center; gap: .35rem; padding: .45rem .45rem .55rem; border-radius: .9rem; border: 2px solid #e2e8f0; background: #f8fafc; font-size: .74rem; font-weight: 700; color: #334155; }
        .ap-bg.is-on { border-color: var(--color-indigo-600); background: #fff; }
        .dark .ap-bg { background: #111827; border-color: #1f2937; color: #e2e8f0; }
        .dark .ap-bg.is-on { border-color: var(--color-indigo-600); background: #1e293b; }
        .ap-bg-art { position: relative; width: 100%; height: 2.8rem; border-radius: .6rem; border: 1px solid #e2e8f0; display: flex; gap: .25rem; padding: .35rem; }
        .ap-bg-art span { flex: 1; border-radius: .3rem; background: #fff; box-shadow: 0 2px 6px -3px rgba(15, 23, 42, .3); }
        .ap-bg-padrao { background: linear-gradient(135deg, #f8fafc, #eff6ff, #eef2ff); }
        .ap-bg-tema { background: linear-gradient(135deg, var(--color-slate-50), var(--color-indigo-50) 50%, var(--color-purple-100)); }
        .ap-bg-cinza { background: #eef1f5; }
        .ap-bg-areia { background: linear-gradient(135deg, #fffbeb, #fff7ed 50%, #fdf2f8); }
        .ap-bg-branco { background: #fff; }
        .ap-bg-branco span { border: 1px solid #e2e8f0; }
        .ap-pv-cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; margin-top: .6rem; }
        .ap-pc { position: relative; display: flex; flex-direction: column; gap: .3rem; padding: .3rem; border-radius: 1rem; background: var(--card-bg, #e6e6fa); border: 2px solid var(--card-border, #b39ddb); }
        .ap-pc-code { position: absolute; top: .3rem; left: .3rem; z-index: 1; font-size: .55rem; font-weight: 800; padding: .1rem .35rem; border-radius: .6rem 0 .6rem 0; color: var(--card-text, #424242); background: linear-gradient(90deg, var(--card-accent2, #f8bbd0), var(--card-border, #b39ddb)); }
        .ap-pc-img { display: grid; place-items: center; height: 4.2rem; border-radius: .75rem; background: #fff; border: 3px solid var(--card-accent, #ffe0b2); }
        .ap-pc-img span { width: 1.6rem; height: 2.6rem; border-radius: .4rem; }
        .ap-pc b { font-size: .62rem; font-weight: 900; text-transform: uppercase; letter-spacing: .04em; text-align: center; color: var(--card-title, #1e1b4b); }
        .ap-pc-prices { display: flex; justify-content: space-between; gap: .2rem; }
        .ap-pc-prices span { font-size: .55rem; font-weight: 800; padding: .12rem .3rem; border-radius: .4rem; color: var(--card-text, #424242); background: linear-gradient(90deg, var(--card-accent2, #f8bbd0), var(--card-border, #b39ddb)); }
        .ap-pc-prices span + span { background: linear-gradient(90deg, var(--card-danger, #ffebee), var(--card-accent2, #f8bbd0)); }
                @media (max-width: 767px) { .ap-bgs { grid-template-columns: repeat(3, minmax(0, 1fr)); } .ap-cardstyles { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .4rem; } .ap-cs-desc { display: none; } }

        .ap-modes { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .5rem; }
        .ap-mode { display: flex; flex-direction: column; gap: .35rem; align-items: center; padding: .45rem .45rem .55rem; border-radius: .9rem; border: 2px solid #e2e8f0; background: #f8fafc; font-size: .74rem; font-weight: 700; color: #334155; }
        .ap-mode.is-on { border-color: var(--color-indigo-600); background: #fff; }
        .dark .ap-mode { background: #111827; border-color: #1f2937; color: #e2e8f0; }
        .dark .ap-mode.is-on { border-color: var(--color-indigo-600); background: #1e293b; }
        .ap-mode-art { width: 100%; height: 2.6rem; border-radius: .55rem; border: 1px solid #e2e8f0; }
        .ap-mode-light { background: linear-gradient(135deg, #fff 55%, #e2e8f0 55%); }
        .ap-mode-dark { background: linear-gradient(135deg, #1e293b 55%, #020617 55%); border-color: #334155; }
        .ap-mode-system { background: linear-gradient(135deg, #fff 50%, #1e293b 50%); }

        @media (max-width: 1535px) { .ap-row { grid-template-columns: 1fr; gap: .5rem; } }
        @media (max-width: 1279px) { .ap-grid { grid-template-columns: minmax(0, 1fr) 320px; } }
        @media (max-width: 1023px) {
            .ap-grid { grid-template-columns: minmax(0, 1fr); }
            .ap-side { position: static; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ap-side .ap-preview-card { grid-row: span 2; }
        }
        @media (max-width: 767px) {
            .ap-side { display: flex; }
            .ap-themes { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
            .ap-fonts { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .ap-swatches { display: grid; grid-template-columns: repeat(9, minmax(0, 1fr)); gap: .35rem; }
            .ap-swatch { width: 100%; height: auto; aspect-ratio: 1; }
        }

        /* Mais escolhas: grupos de cards, desenhos, menu e prévia em janela */
        .ap-page .ap-seg, .ap-modal .ap-seg { grid-template-columns: none; grid-auto-flow: column; grid-auto-columns: 1fr; }
        .ap-group + .ap-group { margin-top: .9rem; }
        .ap-group-label, .ap-mini-label { font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #64748b; margin-bottom: .45rem; }
        .dark .ap-group-label, .dark .ap-mini-label { color: #94a3b8; }
        .ap-two { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed #e2e8f0; }
        .dark .ap-two { border-color: #1e293b; }
        .ap-shape { width: .95rem; height: .95rem; border: 2px solid currentColor; }
        .ap-pats { display: grid; grid-template-columns: repeat(auto-fill, minmax(78px, 1fr)); gap: .5rem; }
        .ap-pat { display: flex; flex-direction: column; align-items: center; gap: .3rem; padding: .35rem .35rem .45rem; border-radius: .9rem; border: 2px solid #e2e8f0; background: #f8fafc; font-size: .7rem; font-weight: 700; color: #334155; }
        .ap-pat.is-on { border-color: var(--color-indigo-600); background: #fff; }
        .dark .ap-pat { background: #111827; border-color: #1f2937; color: #e2e8f0; }
        .dark .ap-pat.is-on { border-color: var(--color-indigo-600); background: #1e293b; }
        .ap-pat-art { display: grid; place-items: center; width: 100%; height: 2.8rem; border-radius: .55rem; border: 1px solid #e2e8f0; color: #94a3b8; }
        .ap-swatch-theme { background: conic-gradient(var(--color-indigo-500), var(--color-purple-500), var(--color-pink-500), var(--color-indigo-500)); }
        .ap-sides { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .5rem; }
        .ap-sidebtn { display: flex; flex-direction: column; align-items: center; gap: .3rem; padding: .35rem .35rem .45rem; border-radius: .9rem; border: 2px solid #e2e8f0; background: #f8fafc; font-size: .7rem; font-weight: 700; color: #334155; }
        .ap-sidebtn.is-on { border-color: var(--color-indigo-600); background: #fff; }
        .dark .ap-sidebtn { background: #111827; border-color: #1f2937; color: #e2e8f0; }
        .dark .ap-sidebtn.is-on { border-color: var(--color-indigo-600); background: #1e293b; }
        .ap-side-art { display: flex; width: 100%; height: 3rem; border-radius: .55rem; overflow: hidden; border: 1px solid #e2e8f0; background: #f1f5f9; }
        .ap-side-art > span { width: 42%; display: flex; flex-direction: column; gap: .2rem; padding: .3rem; border-right: 1px solid #e2e8f0; }
        .ap-side-art > span i { height: .3rem; border-radius: 1rem; background: color-mix(in oklab, var(--color-indigo-500) 35%, #cbd5e1); }
        .ap-side-art > span i:first-child { background: var(--color-indigo-500); }
        .ap-side-art b { flex: 1; }
        .ap-tabdot { width: .9rem; height: .9rem; border-radius: .3rem; border: 1px solid #cbd5e1; }

        .ap-pv-fab { position: fixed; z-index: 60; right: 1.25rem; bottom: 1.25rem; display: inline-flex; align-items: center; gap: .45rem; padding: .75rem 1.1rem; border-radius: 999px; color: #fff; font-size: .85rem; font-weight: 800; box-shadow: 0 14px 30px -10px color-mix(in oklab, var(--color-indigo-600) 70%, transparent); }
        .ap-pv-fab:hover { transform: translateY(-2px); }
        @media (max-width: 1023px) { .ap-pv-fab { bottom: calc(5.4rem + env(safe-area-inset-bottom)); right: 1rem; } }

        .ap-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .ap-modal-back { position: absolute; inset: 0; background: rgba(15, 23, 42, .55); backdrop-filter: blur(4px); }
        .ap-modal-box { position: relative; width: min(1180px, 100%); max-height: calc(100dvh - 2rem); display: flex; flex-direction: column; border-radius: 1.4rem; background: #fff; box-shadow: 0 30px 80px -20px rgba(15, 23, 42, .6); overflow: hidden; }
        .dark .ap-modal-box { background: #0b1220; border: 1px solid #1e293b; }
        .ap-modal-head { display: flex; align-items: center; gap: .75rem; padding: .9rem 1rem; border-bottom: 1px solid #e2e8f0; }
        .dark .ap-modal-head { border-color: #1e293b; }
        .ap-modal-head h2 { font-size: 1rem; font-weight: 800; color: #0f172a; }
        .ap-modal-head p { font-size: .75rem; color: #64748b; }
        .dark .ap-modal-head h2 { color: #f1f5f9; }
        .ap-dev { width: 15rem; }
        .ap-modal-x { display: grid; place-items: center; width: 2.3rem; height: 2.3rem; border-radius: .8rem; background: #f1f5f9; color: #334155; }
        .dark .ap-modal-x { background: #1e293b; color: #e2e8f0; }
        .ap-modal-body { flex: 1; min-height: 0; display: grid; grid-template-columns: minmax(0, 1fr) 340px; overflow: hidden; }
        .ap-stage { display: grid; place-items: center; padding: 1rem; background: #e2e8f0; overflow: auto; }
        .dark .ap-stage { background: #020617; }
        .ap-mock-pc { width: 100%; max-width: 780px; display: flex; gap: .7rem; padding: .7rem; border-radius: 1rem; box-shadow: 0 18px 40px -20px rgba(15, 23, 42, .5); }
        .ap-mk-side { width: 9.5rem; flex: none; display: flex; flex-direction: column; gap: .3rem; padding: .6rem; border-radius: .9rem; box-shadow: 0 6px 18px -10px rgba(15, 23, 42, .4); }
        .ap-mk-logo { width: 1.9rem; height: 1.9rem; border-radius: .6rem; margin-bottom: .4rem; }
        .ap-mk-item { display: flex; align-items: center; gap: .4rem; font-size: .72rem; font-weight: 600; color: #64748b; padding: .38rem .45rem; border-radius: .55rem; white-space: nowrap; }
        .ap-stage.is-dark .ap-mk-item { color: #94a3b8; }
        .ap-mk-item.is-on { font-weight: 800; }
        .ap-mk-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: .55rem; }
        .ap-mk-head { display: flex; align-items: center; justify-content: space-between; gap: .5rem; border-radius: .9rem; padding: .7rem .8rem; color: #fff; }
        .ap-mk-head b { display: block; font-size: .68rem; opacity: .9; }
        .ap-mk-head span { font-size: 1.05rem; font-weight: 800; }
        .ap-mk-pill { font-size: .68rem !important; font-weight: 800; padding: .35rem .6rem; border-radius: 999px; background: rgba(255, 255, 255, .22); white-space: nowrap; }
        .ap-mk-stats { display: flex; flex-wrap: wrap; gap: .35rem; }
        .ap-mk-stats span { font-size: .66rem; font-weight: 700; padding: .25rem .5rem; border-radius: .55rem; }
        .ap-mk-cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .55rem; }
        .ap-mk-cards.is-cel { grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 0 .5rem; }
        .ap-mock-cel { position: relative; width: 270px; height: 470px; display: flex; flex-direction: column; gap: .55rem; padding: .7rem 0 0; border-radius: 2rem; border: 6px solid #0f172a; overflow: hidden; box-shadow: 0 18px 40px -20px rgba(15, 23, 42, .6); }
        .ap-mock-cel .ap-mk-head { margin: 0 .5rem; }
        .ap-mk-cel-top { display: flex; justify-content: space-between; align-items: center; margin: 0 .5rem; padding: .5rem .7rem; border-radius: .9rem; background: rgba(255, 255, 255, .9); font-size: .8rem; color: #0f172a; }
        .ap-stage.is-dark .ap-mk-cel-top { background: #0f172a; color: #f1f5f9; }
        .ap-mk-tab { position: absolute; left: 0; right: 0; bottom: 0; display: flex; justify-content: space-around; align-items: center; padding: .5rem .3rem .7rem; color: #64748b; box-shadow: 0 -10px 24px -14px rgba(15, 23, 42, .4); }
        .ap-mk-tab span { display: grid; place-items: center; width: 2rem; height: 2rem; font-size: 1rem; }
        .ap-mk-tab-mid { border-radius: 999px; color: #fff !important; width: 2.6rem !important; height: 2.6rem !important; margin-top: -1rem; }
        .ap-quick { display: flex; flex-direction: column; min-height: 0; border-left: 1px solid #e2e8f0; }
        .dark .ap-quick { border-color: #1e293b; }
        .ap-quick-tabs { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .25rem; padding: .6rem; border-bottom: 1px solid #e2e8f0; }
        .dark .ap-quick-tabs { border-color: #1e293b; }
        .ap-quick-tabs button { display: flex; flex-direction: column; align-items: center; gap: .15rem; padding: .45rem .2rem; border-radius: .7rem; font-size: .7rem; font-weight: 700; color: #64748b; }
        .ap-quick-tabs button.is-on { background: color-mix(in oklab, var(--color-indigo-500) 12%, transparent); color: var(--color-indigo-700); }
        .dark .ap-quick-tabs button.is-on { color: var(--color-indigo-300); }
        .ap-quick-body { flex: 1; overflow-y: auto; padding: .75rem; }
        .ap-chips { display: flex; flex-wrap: wrap; gap: .4rem; }
        .ap-chip { display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .6rem .35rem .35rem; border-radius: .8rem; border: 2px solid #e2e8f0; background: #f8fafc; font-size: .72rem; font-weight: 700; color: #334155; }
        .ap-chip.is-on { border-color: var(--color-indigo-600); background: #fff; }
        .dark .ap-chip { background: #111827; border-color: #1f2937; color: #e2e8f0; }
        .dark .ap-chip.is-on { border-color: var(--color-indigo-500); background: #1e293b; }
        .ap-chip-dots { display: flex; }
        .ap-chip-dots i { width: .9rem; height: .9rem; border-radius: 999px; border: 2px solid #fff; margin-left: -.3rem; }
        .ap-chip-dots i:first-child { margin-left: 0; }
        .ap-chip-card { width: 1.5rem; height: 1.5rem; border-radius: .45rem; background: var(--a-bg); border: 3px solid var(--a-border); box-shadow: inset 0 -5px 0 var(--a-tag); }
        .ap-chip-sq { width: 1.5rem; height: 1.5rem; border-radius: .45rem; border: 1px solid #cbd5e1; }
        .ap-chip-pat { background-size: 17px 17px, cover !important; }
        .ap-chips-row { width: 100%; display: flex; flex-direction: column; gap: .35rem; margin-top: .6rem; }
        .ap-chips-row .ap-mini-label { margin: 0; }
        @media (max-width: 1023px) {
            .ap-modal { padding: 0; align-items: flex-end; }
            .ap-modal-box { width: 100%; max-height: 94dvh; border-radius: 1.4rem 1.4rem 0 0; }
            .ap-modal-body { grid-template-columns: 1fr; grid-template-rows: minmax(0, 1fr) auto; overflow-y: auto; }
            .ap-quick { border-left: 0; border-top: 1px solid #e2e8f0; }
            .ap-quick-body { max-height: 15rem; }
            .ap-mk-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .ap-mk-cards > :nth-child(4) { display: none; }
            .ap-sides { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 767px) {
            .ap-modal-head p { display: none; }
            .ap-dev { width: auto; }
            .ap-dev span { display: none; }
            .ap-mock-pc { gap: .4rem; padding: .4rem; }
            .ap-mk-side { width: 5.6rem; padding: .4rem; }
            .ap-mk-item { font-size: .6rem; padding: .3rem; }
            .ap-mk-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ap-mk-cards > :nth-child(3) { display: none; }
            .ap-mock-cel { width: 230px; height: 400px; }
            .ap-two { grid-template-columns: 1fr; }
            .ap-pats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .ap-stage { padding: .6rem; }
        }
    </style>

    <script>
        window.fmAppearance = window.fmAppearance || function (saved, savedLook, themes) {
            return {
                themes: themes,
                look: { cards: 'tema', tone: 'medio', shape: 'redondo', bg: 'padrao', bg_c: null, pat: 'nenhum', pat_c: 'tema', pat_o: 'medio', side: 'padrao', tab: 'padrao', c_bg: null, c_border: null, c_tag: null, c_detail: null },
                pvOpen: false,
                pvDev: window.innerWidth < 768 ? 'cel' : 'pc',
                pvTab: 'cards',
                dark: document.documentElement.classList.contains('dark'),
                customDefault: { c_bg: 'violet', c_border: 'purple', c_tag: 'pink', c_detail: 'orange' },
                lookSavedAt: null,
                lookTimer: null,
                cardGroups: [
                    { label: 'Combinar com o app', items: [
                        { id: 'tema', label: 'Acompanhar o tema', desc: 'Muda junto com o tema' },
                        { id: 'classico', label: 'Lilás clássico', desc: 'O visual de sempre' },
                        { id: 'custom', label: 'Minhas cores', desc: 'Escolha cada parte' },
                    ] },
                    { label: 'Neutros', items: [
                        { id: 'branco', label: 'Branco', desc: 'A foto aparece mais' },
                        { id: 'grafite', label: 'Grafite', desc: 'Cinza discreto' },
                        { id: 'escuro', label: 'Escuro', desc: 'Letras claras' },
                        { id: 'noite', label: 'Noite', desc: 'Azul bem escuro' },
                    ] },
                    { label: 'Coloridos', items: [
                        { id: 'rosa', label: 'Rosa' }, { id: 'pessego', label: 'Pêssego' }, { id: 'caramelo', label: 'Caramelo' },
                        { id: 'limao', label: 'Limão' }, { id: 'menta', label: 'Menta' }, { id: 'floresta', label: 'Floresta' },
                        { id: 'oceano', label: 'Oceano' }, { id: 'ceu', label: 'Céu' }, { id: 'lavanda', label: 'Lavanda' },
                        { id: 'uva', label: 'Uva' }, { id: 'coral', label: 'Coral' }, { id: 'cereja', label: 'Cereja' },
                    ] },
                ],
                bgModes: [
                    { id: 'padrao', label: 'Padrão' },
                    { id: 'tema', label: 'Do tema' },
                    { id: 'cor', label: 'Uma cor' },
                    { id: 'cinza', label: 'Cinza' },
                    { id: 'areia', label: 'Areia' },
                    { id: 'branco', label: 'Branco' },
                ],
                patterns: [
                    { id: 'nenhum', label: 'Nenhum' }, { id: 'pontos', label: 'Pontos' }, { id: 'bolhas', label: 'Bolhas' }, { id: 'linhas', label: 'Linhas' },
                    { id: 'grade', label: 'Grade' }, { id: 'xadrez', label: 'Xadrez' }, { id: 'mais', label: 'Cruzes' }, { id: 'diamantes', label: 'Losangos' },
                    { id: 'ondas', label: 'Ondas' }, { id: 'zigue', label: 'Zigue-zague' }, { id: 'coracoes', label: 'Corações' }, { id: 'estrelas', label: 'Estrelas' },
                    { id: 'folhas', label: 'Folhas' }, { id: 'flores', label: 'Flores' }, { id: 'confete', label: 'Confete' },
                ],
                sideModes: [
                    { id: 'padrao', label: 'Vidro' }, { id: 'tema', label: 'Tom do tema' }, { id: 'cor', label: 'Cor do tema' },
                    { id: 'branco', label: 'Branco' }, { id: 'cinza', label: 'Cinza' }, { id: 'areia', label: 'Areia' },
                ],
                tabModes: [
                    { id: 'padrao', label: 'Padrão' }, { id: 'tema', label: 'Tom do tema' }, { id: 'cor', label: 'Cor do tema' }, { id: 'branco', label: 'Branco' },
                ],
                theme: 'flow',
                pal: { primary: 'indigo', secondary: 'purple', accent: 'pink' },
                font: 'system',
                size: 14,
                compact: false,
                noAnim: false,
                mode: 'system',
                tabletNav: 'sidebar',
                savedAt: null,
                saveTimer: null,
                fonts: [
                    { id: 'system', label: 'Padrão', css: '' },
                    { id: 'inter', label: 'Inter', css: 'Inter, sans-serif' },
                    { id: 'poppins', label: 'Poppins', css: 'Poppins, sans-serif' },
                    { id: 'nunito', label: 'Nunito', css: 'Nunito, sans-serif' },
                    { id: 'roboto', label: 'Roboto', css: 'Roboto, sans-serif' },
                ],
                init() {
                    var lk = savedLook && savedLook.cards ? savedLook : null;
                    if (!lk) { try { lk = JSON.parse(localStorage.getItem('flowmanager:look') || 'null'); } catch (e) {} }
                    if (lk) this.look = Object.assign({}, this.look, lk);
                    var local = null;
                    try { local = JSON.parse(localStorage.getItem('flowmanager:palette') || 'null'); } catch (e) {}
                    var v = (saved && saved.primary) ? saved : local;
                    if (v) {
                        this.pal = { primary: v.primary || 'indigo', secondary: v.secondary || 'purple', accent: v.accent || 'pink' };
                        this.theme = v.theme || this.matchTheme();
                    }
                    try {
                        this.font = localStorage.getItem('flowmanager:font') || 'system';
                        var s = parseInt(localStorage.getItem('flowmanager:font-size'), 10);
                        this.size = s >= 12 && s <= 18 ? s : 14;
                        this.compact = ['1', 'true'].includes(localStorage.getItem('flowmanager:compact'));
                        this.noAnim = ['1', 'true'].includes(localStorage.getItem('flowmanager:no-animations'));
                        this.mode = localStorage.getItem('flowmanager:theme') || 'system';
                        this.tabletNav = localStorage.getItem('flowmanager:tablet-nav-mode') === 'tabbar' ? 'tabbar' : 'sidebar';
                    } catch (e) {}
                    // Fontes para a amostra dos botões
                    ['inter:700', 'poppins:700', 'nunito:700', 'roboto:700'].forEach(function (f) {
                        if (document.querySelector('link[data-ap-font="' + f + '"]')) return;
                        var l = document.createElement('link'); l.rel = 'stylesheet'; l.dataset.apFont = f;
                        l.href = 'https://fonts.bunny.net/css?family=' + f; document.head.appendChild(l);
                    });
                },
                matchTheme() {
                    var p = this.pal, hit = this.themes.find(function (t) { return t.primary === p.primary && t.secondary === p.secondary && t.accent === p.accent; });
                    return hit ? hit.id : 'custom';
                },
                apply() {
                    var v = Object.assign({ theme: this.theme }, this.pal);
                    window.fmApplyPalette && window.fmApplyPalette(v);
                    window.fmApplyLook && window.fmApplyLook(this.look, this.pal);
                    try { localStorage.setItem('flowmanager:palette', JSON.stringify(v)); } catch (e) {}
                    clearTimeout(this.saveTimer);
                    this.saveTimer = setTimeout(() => {
                        this.$wire.savePalette(v).then(() => { this.savedAt = Date.now(); setTimeout(() => { this.savedAt = null; }, 2500); });
                    }, 400);
                },
                pickTheme(id) {
                    var t = this.themes.find(function (x) { return x.id === id; });
                    if (!t) return;
                    this.theme = id;
                    this.pal = { primary: t.primary, secondary: t.secondary, accent: t.accent };
                    this.apply();
                },
                pickColor(slot, fam) {
                    this.pal[slot] = fam;
                    this.theme = this.matchTheme();
                    this.apply();
                },
                reset() { this.pickTheme('flow'); },
                // Mostra as cores de cada estilo de card na amostra (mesma conta usada no app)
                cardArt(mode) {
                    var classic = ['#e6e6fa', '#b39ddb', '#ffe0b2', '#f8bbd0', '#ffebee', '#424242', '#1e1b4b'];
                    var lk = Object.assign({}, this.look, { cards: mode });
                    if (mode === 'custom') { ['c_bg', 'c_border', 'c_tag', 'c_detail'].forEach((k) => { lk[k] = lk[k] || this.customDefault[k]; }); }
                    var c = (window.fmCardColors && window.fmCardColors(lk, this.pal)) || classic;
                    return '--a-bg:' + c[0] + ';--a-border:' + c[1] + ';--a-detail:' + c[2] + ';--a-tag:' + c[3] + ';--a-danger:' + c[4] + ';--a-title:' + c[6];
                },
                applyLook() {
                    var lk = Object.assign({}, this.look);
                    if (lk.cards === 'custom') { ['c_bg', 'c_border', 'c_tag', 'c_detail'].forEach((k) => { lk[k] = lk[k] || this.customDefault[k]; }); this.look = lk; }
                    window.fmApplyLook && window.fmApplyLook(lk, this.pal);
                    try { localStorage.setItem('flowmanager:look', JSON.stringify(lk)); } catch (e) {}
                    clearTimeout(this.lookTimer);
                    this.lookTimer = setTimeout(() => {
                        this.$wire.saveLook(lk).then(() => { this.lookSavedAt = Date.now(); setTimeout(() => { this.lookSavedAt = null; }, 2500); });
                    }, 400);
                },
                pickCards(id) { this.look.cards = id; this.applyLook(); },
                setLook(k, v) { this.look[k] = v; this.applyLook(); },
                allCardModes() { return this.cardGroups.reduce(function (a, g) { return a.concat(g.items); }, []); },
                openPreview() { this.dark = document.documentElement.classList.contains('dark'); this.pvOpen = true; },
                mockRadius() { return { medio: '.6rem', reto: '.2rem' }[this.look.shape] || '1rem'; },
                bgArt(id) {
                    var lk = Object.assign({}, this.look, { bg: id, pat: 'nenhum' });
                    return (window.fmBgCss && window.fmBgCss(lk, this.pal, false)) || 'linear-gradient(135deg, #f8fafc, #eff6ff, #eef2ff)';
                },
                patArt(id) {
                    if (id === 'nenhum') return 'background:#f8fafc';
                    var lk = Object.assign({}, this.look, { pat: id, pat_o: 'forte', bg: 'branco' });
                    return 'background:' + ((window.fmBgCss && window.fmBgCss(lk, this.pal, false)) || '#fff');
                },
                previewBg() {
                    var bg = window.fmBgCss && window.fmBgCss(this.look, this.pal, this.dark);
                    return bg || (this.dark ? 'linear-gradient(to bottom right, #0f172a, #1e293b, #0f172a)' : 'linear-gradient(to bottom right, var(--color-slate-50), var(--color-blue-50), var(--color-indigo-50))');
                },
                sideArt(id) {
                    return { tema: 'linear-gradient(180deg, var(--color-indigo-50), var(--color-purple-100))', cor: 'linear-gradient(180deg, var(--color-indigo-100), var(--color-purple-200))', branco: '#fff', cinza: '#f1f5f9', areia: 'linear-gradient(180deg, #fffbeb, #fff1f2)' }[id] || 'rgba(255,255,255,.82)';
                },
                tabArt(id) {
                    return { tema: 'linear-gradient(180deg, var(--color-indigo-50), var(--color-purple-100))', cor: 'linear-gradient(180deg, var(--color-indigo-100), var(--color-purple-200))', branco: '#fff' }[id] || 'linear-gradient(180deg, rgba(255,255,255,.96), rgba(244,247,251,.94))';
                },
                pickCardColor(slot, fam) { this.look[slot] = fam; this.look.cards = 'custom'; this.applyLook(); },
                pickBg(id) { this.look.bg = id; if (id === 'cor' && !this.look.bg_c) this.look.bg_c = this.pal.primary || 'indigo'; this.applyLook(); },
                pickFont(id) {
                    this.font = id;
                    try { id === 'system' ? localStorage.removeItem('flowmanager:font') : localStorage.setItem('flowmanager:font', id); } catch (e) {}
                    var f = this.fonts.find(function (x) { return x.id === id; });
                    var root = document.documentElement;
                    if (f && f.css) {
                        var fam = { inter: 'inter:400,500,600,700', poppins: 'poppins:400,500,600,700', nunito: 'nunito:400,600,700', roboto: 'roboto:400,500,700' }[id];
                        var l = document.createElement('link'); l.rel = 'stylesheet'; l.href = 'https://fonts.bunny.net/css?family=' + fam; document.head.appendChild(l);
                        root.style.setProperty('--fm-font', "'" + f.label + "', ui-sans-serif, system-ui, sans-serif");
                        root.classList.add('fm-custom-font');
                    } else {
                        root.classList.remove('fm-custom-font');
                        root.style.removeProperty('--fm-font');
                    }
                },
                sizeLabel() { return this.size === 14 ? 'Normal (14px)' : (this.size < 14 ? 'Menor' : 'Maior') + ' (' + this.size + 'px)'; },
                pickSize(n) {
                    this.size = n;
                    try { localStorage.setItem('flowmanager:font-size', n); } catch (e) {}
                    document.documentElement.style.setProperty('--fm-font-scale', (n / 14).toString());
                    document.documentElement.style.setProperty('--s-font-size-factor', (n / 14).toString());
                },
                setCompact(on) {
                    this.compact = on;
                    try { localStorage.setItem('flowmanager:compact', on ? 'true' : 'false'); } catch (e) {}
                    document.documentElement.classList.toggle('compact-mode', on);
                },
                setNoAnim(on) {
                    this.noAnim = on;
                    try { localStorage.setItem('flowmanager:no-animations', on ? 'true' : 'false'); } catch (e) {}
                    document.documentElement.classList.toggle('no-animations', on);
                },
                pickMode(m) {
                    this.mode = m;
                    var dark = m === 'dark' || (m === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    try { m === 'system' ? localStorage.removeItem('flowmanager:theme') : localStorage.setItem('flowmanager:theme', m); } catch (e) {}
                    document.documentElement.classList.toggle('dark', dark);
                    this.dark = dark;
                    window.dispatchEvent(new CustomEvent('flowmanager:theme-changed', { detail: { theme: dark ? 'dark' : 'light' } }));
                },
                pickTabletNav(m) {
                    this.tabletNav = m;
                    try { localStorage.setItem('flowmanager:tablet-nav-mode', m); } catch (e) {}
                    window.FlowSettings && FlowSettings.mergePreferenceGroup && FlowSettings.mergePreferenceGroup('appearance', { tablet_nav_mode: m });
                    window.dispatchEvent(new CustomEvent('flowmanager:tablet-nav-mode-changed', { detail: { mode: m } }));
                },
            };
        };
    </script>

    </x-settings.layout>
</section>
