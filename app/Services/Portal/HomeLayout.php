<?php

namespace App\Services\Portal;

use App\Models\PortalBanner;
use App\Models\PortalSetting;
use App\Services\Promotions\CommercialCalendar;
use Illuminate\Support\Facades\Schema;

/**
 * Montagem da página inicial do catálogo (Vitrine › Página inicial): a ordem dos blocos,
 * quais aparecem, as cores do topo e o estilo dos cantos. Os temas de evento (Black Friday,
 * Natal, Dia das Mães…) preenchem tudo de uma vez; depois a loja muda o que quiser.
 * Fica em portal_settings.home: theme, order, off, colors [header1, header2, primary], style.
 */
class HomeLayout
{
    /** Blocos da página inicial que dá para mudar de lugar ou esconder. */
    public const SECTIONS = [
        'hero'        => ['Banners grandes', 'bi-images', 'O carrossel do topo.'],
        'cats'        => ['Categorias', 'bi-grid-3x3-gap', 'Os atalhos redondos das categorias.'],
        'featured'    => ['Destaques', 'bi-star', 'Os produtos escolhidos na aba Destaques.'],
        'offers'      => ['Ofertas do dia', 'bi-lightning-charge', 'Os produtos com promoção ativa.'],
        'kits'        => ['Monte seu kit', 'bi-gift', 'Os kits que o cliente monta (aparece quando tem kit ligado).'],
        'collections' => ['Coleções e faixas', 'bi-collection', 'Uma fileira para cada coleção, com as faixas entre elas.'],
        'recent'      => ['Vistos por último', 'bi-clock-history', 'Os produtos que o cliente abriu por último.'],
    ];

    public const DEFAULT_ORDER = ['hero', 'cats', 'featured', 'kits', 'offers', 'collections', 'recent'];

    /** Cantos dos cards e banners. */
    public const STYLES = [
        'padrao' => ['Padrão', '8px', 'bi-square'],
        'suave'  => ['Arredondado', '16px', 'bi-app'],
        'reto'   => ['Reto', '2px', 'bi-stop'],
    ];

    /**
     * Cores das áreas de cada tema: [fundo da página, fundo dos cards e das seções].
     * O texto (escuro ou claro) sai sozinho da cor de fundo.
     */
    public const SURFACES = [
        'padrao'     => ['#f0f0f3', '#ffffff'],
        'black'      => ['badge' => 'canto', 'addbtn' => 'cheio', '#0b0b0d', '#1c1c21'],
        'natal'      => ['cardbg' => 'faixa', 'bgtype' => 'topo', '#f6efe3', '#fffdf8'],
        'maes'       => ['cardbg' => 'suave', 'badge' => 'pilula', '#fcecf3', '#fffafc'],
        'namorados'  => ['#fde8ec', '#fffafb'],
        'pais'       => ['#e8edf3', '#ffffff'],
        'criancas'   => ['cardbg' => 'cores', 'badge' => 'circulo', '#fff6d6', '#ffffff'],
        'consumidor' => ['#e8f0ff', '#ffffff'],
        'cliente'    => ['#efeaff', '#ffffff'],
        'pascoa'     => ['#f5eee2', '#fffcf6'],
        'liquida'    => ['badge' => 'circulo', 'price' => 'cor', '#fff0dc', '#ffffff'],
        'ano_novo'   => ['#f7f3ea', '#ffffff'],
        'volta_aulas'=> ['#eef4ff', '#ffffff'],
        'carnaval'   => ['#fbf0ff', '#ffffff'],
        'mulher'     => ['#faf0ff', '#ffffff'],
        'junina'     => ['#fff3e0', '#fffaf2'],
        'minimalista'=> ['#fafafa', '#ffffff'],
        'luxo'       => ['price' => 'cor', 'addbtn' => 'cheio', '#0c0a09', '#1c1917'],
        'natural'    => ['#f4efe6', '#fffdf8'],
        'pastel'     => ['cardbg' => 'cores', 'bgtype' => 'malha', '#fdf4ff', '#ffffff'],
        'neon'       => ['cards' => 'brilho', 'bgtype' => 'brilho', '#0f0a1e', '#1a1230'],
        'oceano'     => ['bgtype' => 'degrade', 'cardbg' => 'degrade', '#ecfeff', '#ffffff'],
        'floral'     => ['#fff1f5', '#ffffff'],
        'rustico'    => ['pattern' => 'madeira', 'patternop' => 'suave', '#f5efe6', '#fffaf3'],
        'escandinavo'=> ['#f5f5f4', '#ffffff'],
        'vintage'    => ['#f7f0e1', '#fffbf0'],
        'tropical'   => ['cardbg' => 'colorido', 'addbtn' => 'barra', '#f0fdf4', '#ffffff'],
        'noite'      => ['#0b1020', '#151a2e'],
        'cafe'       => ['#f3ebe3', '#fffaf5'],
        'lavanda'    => ['#f5f3ff', '#ffffff'],
        'esportivo'  => ['badge' => 'canto', 'price' => 'grande', '#f3f4f6', '#ffffff'],
    ];

    /** Cores da logo do app: o tema "Cores da loja". */
    public const BASE = ['#ec4899', '#3b82f6', '#8b5cf6'];

    /**
     * Temas: name, emoji, header1/header2 (degradê do topo), primary (botões e links), style,
     * order (blocos), tpl (modelo do banner do evento), date (CommercialCalendar), notice
     * [beleza e outras, móveis] e featured (título dos destaques).
     */
    public const THEMES = [
        'padrao'     => ['name' => 'Cores da loja', 'emoji' => '🏪', 'colors' => self::BASE, 'style' => 'padrao', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null],
        'black'      => ['name' => 'Black Friday', 'emoji' => '🖤', 'colors' => ['#0a0a0a', '#3f3f46', '#111111'], 'style' => 'reto',
            'order' => ['hero', 'offers', 'featured', 'collections', 'cats', 'recent'], 'tpl' => 'black', 'date' => 'black',
            'notice' => ['BLACK FRIDAY: descontos em toda a loja, só até domingo', 'BLACK FRIDAY DOS MÓVEIS: preços que não voltam'], 'featured' => 'Os mais desejados da Black'],
        'natal'      => ['name' => 'Natal', 'emoji' => '🎄', 'colors' => ['#b91c1c', '#166534', '#b91c1c'], 'style' => 'suave',
            'order' => ['hero', 'featured', 'collections', 'offers', 'cats', 'recent'], 'tpl' => 'natal', 'date' => 'natal',
            'notice' => ['Presentes de Natal com entrega antes do dia 24', 'Natal em casa nova: peças prontas para entregar antes do dia 24'], 'featured' => 'Presentes de Natal'],
        'maes'       => ['name' => 'Dia das Mães', 'emoji' => '💐', 'colors' => ['#ec4899', '#be185d', '#db2777'], 'style' => 'suave',
            'order' => ['hero', 'featured', 'collections', 'offers', 'cats', 'recent'], 'tpl' => 'maes', 'date' => 'maes',
            'notice' => ['Presente para a mãe com embalagem caprichada', 'Dia das Mães: uma casa mais bonita para ela'], 'featured' => 'Presentes para as mães'],
        'namorados'  => ['name' => 'Dia dos Namorados', 'emoji' => '💘', 'colors' => ['#e11d48', '#9f1239', '#e11d48'], 'style' => 'suave',
            'order' => ['hero', 'featured', 'offers', 'collections', 'cats', 'recent'], 'tpl' => 'namorados', 'date' => 'namorados',
            'notice' => ['Presente de Dia dos Namorados que encanta', 'O cantinho de vocês mais bonito no Dia dos Namorados'], 'featured' => 'Presentes para o seu amor'],
        'pais'       => ['name' => 'Dia dos Pais', 'emoji' => '👔', 'colors' => ['#1e3a8a', '#0f172a', '#1d4ed8'], 'style' => 'padrao',
            'order' => ['hero', 'featured', 'collections', 'offers', 'cats', 'recent'], 'tpl' => 'pais', 'date' => 'pais',
            'notice' => ['Presentes para o Dia dos Pais', 'Dia dos Pais: o canto dele em casa'], 'featured' => 'Presentes para os pais'],
        'criancas'   => ['name' => 'Dia das Crianças', 'emoji' => '🎈', 'colors' => ['#f59e0b', '#ec4899', '#f97316'], 'style' => 'suave',
            'order' => ['hero', 'offers', 'featured', 'collections', 'cats', 'recent'], 'tpl' => 'criancas', 'date' => 'criancas',
            'notice' => ['Dia das Crianças com presentes e mimos', 'Dia das Crianças'], 'featured' => 'Para os pequenos'],
        'consumidor' => ['name' => 'Semana do Consumidor', 'emoji' => '🛍️', 'colors' => ['#ff7733', '#e11d48', '#ea580c'], 'style' => 'padrao',
            'order' => ['hero', 'offers', 'featured', 'collections', 'cats', 'recent'], 'tpl' => 'consumidor', 'date' => 'consumidor',
            'notice' => ['Semana do Consumidor: ofertas por tempo limitado', 'Semana do Consumidor: ofertas por tempo limitado'], 'featured' => 'Ofertas da semana'],
        'cliente'    => ['name' => 'Dia do Cliente', 'emoji' => '🤝', 'colors' => ['#0ea5e9', '#6366f1', '#4f46e5'], 'style' => 'suave',
            'order' => ['hero', 'offers', 'featured', 'collections', 'cats', 'recent'], 'tpl' => 'cliente', 'date' => 'cliente',
            'notice' => ['Dia do Cliente: um agradecimento especial para você', 'Dia do Cliente: um agradecimento especial para você'], 'featured' => 'Escolhidos para você'],
        'pascoa'     => ['name' => 'Páscoa', 'emoji' => '🐣', 'colors' => ['#a78bfa', '#f472b6', '#8b5cf6'], 'style' => 'suave',
            'order' => ['hero', 'featured', 'collections', 'offers', 'cats', 'recent'], 'tpl' => 'pascoa', 'date' => 'pascoa',
            'notice' => ['Páscoa cheia de mimos', 'Páscoa em família'], 'featured' => 'Mimos de Páscoa'],
        'liquida'    => ['name' => 'Liquidação', 'emoji' => '🔥', 'colors' => ['#f97316', '#dc2626', '#dc2626'], 'style' => 'reto',
            'order' => ['hero', 'offers', 'collections', 'featured', 'cats', 'recent'], 'tpl' => 'liquida', 'date' => null,
            'notice' => ['LIQUIDAÇÃO: preços baixos enquanto durar o estoque', 'QUEIMA DE ESTOQUE: peças com preço de fábrica'], 'featured' => 'Últimas peças'],
        'ano_novo'   => ['name' => 'Ano Novo', 'emoji' => '🥂', 'colors' => ['#111827', '#a16207', '#ca8a04'], 'style' => 'suave',
            'order' => ['hero', 'featured', 'offers', 'collections', 'cats', 'recent'], 'tpl' => 'ano_novo', 'date' => 'ano_novo',
            'notice' => ['Ofertas de Ano Novo: comece o ano se cuidando', 'Casa nova para o Ano Novo: peças com entrega rápida'], 'featured' => 'Para brilhar no Ano Novo'],
        'volta_aulas'=> ['name' => 'Volta às aulas', 'emoji' => '📚', 'colors' => ['#2563eb', '#f59e0b', '#2563eb'], 'style' => 'suave',
            'order' => ['hero', 'offers', 'featured', 'collections', 'cats', 'recent'], 'tpl' => 'volta_aulas', 'date' => 'volta_aulas',
            'notice' => ['Volta às aulas com preço bom', 'Volta às aulas: o cantinho de estudos'], 'featured' => 'Para a volta às aulas'],
        'carnaval'   => ['name' => 'Carnaval', 'emoji' => '🎭', 'colors' => ['#7c3aed', '#db2777', '#f59e0b'], 'style' => 'suave',
            'order' => ['hero', 'offers', 'featured', 'collections', 'cats', 'recent'], 'tpl' => 'carnaval', 'date' => 'carnaval',
            'notice' => ['Carnaval: maquiagem e brilho para curtir a folia', 'Ofertas de Carnaval'], 'featured' => 'Para a folia'],
        'mulher'     => ['name' => 'Dia da Mulher', 'emoji' => '🌷', 'colors' => ['#a855f7', '#ec4899', '#9333ea'], 'style' => 'suave',
            'order' => ['hero', 'featured', 'collections', 'offers', 'cats', 'recent'], 'tpl' => 'mulher', 'date' => 'mulher',
            'notice' => ['Dia da Mulher: presentes para quem você admira', 'Dia da Mulher: uma casa do jeito dela'], 'featured' => 'Para elas'],
        'junina'     => ['name' => 'Festa Junina', 'emoji' => '🌽', 'colors' => ['#c2410c', '#a16207', '#ea580c'], 'style' => 'suave',
            'order' => ['hero', 'offers', 'featured', 'collections', 'cats', 'recent'], 'tpl' => 'junina', 'date' => 'junina',
            'notice' => ['Arraiá de ofertas: só em junho', 'Arraiá de ofertas: só em junho'], 'featured' => 'Ofertas do arraiá'],
        'minimalista' => ['name' => 'Minimalista', 'emoji' => '◻️', 'colors' => ['#111827', '#374151', '#111827'], 'style' => 'reto', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Branco, preto e nada sobrando'],
        'luxo' => ['name' => 'Luxo', 'emoji' => '👑', 'colors' => ['#0c0a09', '#292524', '#d4a017'], 'style' => 'reto', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Preto com dourado, ar de boutique'],
        'natural' => ['name' => 'Natural', 'emoji' => '🌿', 'colors' => ['#4d7c0f', '#a16207', '#4d7c0f'], 'style' => 'suave', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Verde e bege, folhas no fundo'],
        'pastel' => ['name' => 'Pastel', 'emoji' => '🧁', 'colors' => ['#f472b6', '#818cf8', '#ec4899'], 'style' => 'suave', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Rosa e lilás clarinhos, fofo'],
        'neon' => ['name' => 'Neon', 'emoji' => '⚡', 'colors' => ['#7c3aed', '#db2777', '#22d3ee'], 'style' => 'padrao', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Escuro com brilho, bem moderno'],
        'oceano' => ['name' => 'Oceano', 'emoji' => '🌊', 'colors' => ['#0369a1', '#0d9488', '#0284c7'], 'style' => 'suave', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Azul e verde-água com ondas'],
        'floral' => ['name' => 'Floral', 'emoji' => '🌸', 'colors' => ['#db2777', '#9333ea', '#db2777'], 'style' => 'suave', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Flores no fundo, romântico'],
        'rustico' => ['name' => 'Rústico', 'emoji' => '🪵', 'colors' => ['#78350f', '#44403c', '#b45309'], 'style' => 'reto', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Madeira e ferro, combina com móveis'],
        'escandinavo' => ['name' => 'Escandinavo', 'emoji' => '🛋️', 'colors' => ['#a8a29e', '#78716c', '#57534e'], 'style' => 'suave', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Claro, neutro e aconchegante'],
        'vintage' => ['name' => 'Vintage', 'emoji' => '📻', 'colors' => ['#9a3412', '#854d0e', '#9a3412'], 'style' => 'suave', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Tons de antigamente, títulos clássicos'],
        'tropical' => ['name' => 'Tropical', 'emoji' => '🌴', 'colors' => ['#16a34a', '#f59e0b', '#ea580c'], 'style' => 'suave', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Verde e laranja, cheio de energia'],
        'noite' => ['name' => 'Noite elegante', 'emoji' => '🌙', 'colors' => ['#1e1b4b', '#312e81', '#818cf8'], 'style' => 'suave', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Azul bem escuro com estrelas'],
        'cafe' => ['name' => 'Café', 'emoji' => '☕', 'colors' => ['#6f4e37', '#3e2723', '#8d6e63'], 'style' => 'suave', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Marrom e creme, quentinho'],
        'lavanda' => ['name' => 'Lavanda', 'emoji' => '💜', 'colors' => ['#8b5cf6', '#c084fc', '#7c3aed'], 'style' => 'suave', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Lilás com flores, delicado'],
        'esportivo' => ['name' => 'Esportivo', 'emoji' => '🏁', 'colors' => ['#dc2626', '#111827', '#dc2626'], 'style' => 'reto', 'order' => self::DEFAULT_ORDER,
            'tpl' => null, 'date' => null, 'notice' => null, 'featured' => null, 'group' => 'estilo', 'desc' => 'Vermelho e preto, forte'],
    ];

    /** Desenhos do fundo: [máscara CSS, tamanho do desenho, opacidade ou null]. Usados no catálogo e nas miniaturas da Vitrine. */
    public const PATTERNS = [
        'pontos' => ['radial-gradient(circle, #000 2px, transparent 2.5px)', '22px 22px', null],
        'listras' => ['repeating-linear-gradient(135deg, #000 0 10px, transparent 10px 28px)', null, '.06'],
        'coracoes' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'90\' height=\'90\'%3E%3Cpath d=\'M22 32c-5-6-15-3-15 5 0 7 15 15 15 15s15-8 15-15c0-8-10-11-15-5z\'/%3E%3Cpath transform=\'translate(48 46) scale(.6)\' d=\'M22 32c-5-6-15-3-15 5 0 7 15 15 15 15s15-8 15-15c0-8-10-11-15-5z\'/%3E%3C/svg%3E")', '90px 90px', null],
        'estrelas' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'110\' height=\'110\'%3E%3Cpath d=\'M25 8l5 11 12 1-9 8 3 12-11-6-11 6 3-12-9-8 12-1z\'/%3E%3Cpath transform=\'translate(58 56) scale(.55)\' d=\'M25 8l5 11 12 1-9 8 3 12-11-6-11 6 3-12-9-8 12-1z\'/%3E%3Ccircle cx=\'85\' cy=\'20\' r=\'2.5\'/%3E%3Ccircle cx=\'18\' cy=\'88\' r=\'2\'/%3E%3C/svg%3E")', '110px 110px', null],
        'confete' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\'%3E%3Crect x=\'10\' y=\'14\' width=\'12\' height=\'5\' rx=\'2\' transform=\'rotate(30 16 16)\'/%3E%3Crect x=\'70\' y=\'8\' width=\'12\' height=\'5\' rx=\'2\' transform=\'rotate(-25 76 10)\'/%3E%3Ccircle cx=\'45\' cy=\'50\' r=\'4\'/%3E%3Crect x=\'92\' y=\'60\' width=\'12\' height=\'5\' rx=\'2\' transform=\'rotate(60 98 62)\'/%3E%3Ccircle cx=\'20\' cy=\'92\' r=\'3\'/%3E%3Crect x=\'55\' y=\'96\' width=\'12\' height=\'5\' rx=\'2\' transform=\'rotate(-50 61 98)\'/%3E%3Ccircle cx=\'104\' cy=\'106\' r=\'3\'/%3E%3C/svg%3E")', '120px 120px', '.14'],
        'neve' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\' stroke=\'%23000\' stroke-width=\'2\' stroke-linecap=\'round\'%3E%3Cg transform=\'translate(28 30)\'%3E%3Cpath d=\'M0-12V12M-10.4-6 10.4 6M-10.4 6 10.4-6\'/%3E%3C/g%3E%3Cg transform=\'translate(86 84) scale(.65)\'%3E%3Cpath d=\'M0-12V12M-10.4-6 10.4 6M-10.4 6 10.4-6\'/%3E%3C/g%3E%3Ccircle cx=\'90\' cy=\'22\' r=\'2.5\' stroke=\'none\'/%3E%3Ccircle cx=\'30\' cy=\'96\' r=\'3\' stroke=\'none\'/%3E%3Ccircle cx=\'60\' cy=\'60\' r=\'1.8\' stroke=\'none\'/%3E%3C/svg%3E")', '120px 120px', '.16'],
        'ondas' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'32\' fill=\'none\' stroke=\'%23000\' stroke-width=\'2.5\'%3E%3Cpath d=\'M0 16c10 0 10-10 20-10s10 10 20 10 10-10 20-10 10 10 20 10\'/%3E%3C/svg%3E")', '80px 32px', null],
        'flores' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'110\' height=\'110\'%3E%3Cg transform=\'translate%2830 30%29\'%3E%3Ccircle cy=\'-9\' r=\'6\'/%3E%3Ccircle cx=\'8.5\' cy=\'-3\' r=\'6\'/%3E%3Ccircle cx=\'5.3\' cy=\'7.5\' r=\'6\'/%3E%3Ccircle cx=\'-5.3\' cy=\'7.5\' r=\'6\'/%3E%3Ccircle cx=\'-8.5\' cy=\'-3\' r=\'6\'/%3E%3C/g%3E%3Cg transform=\'translate%2882 80%29 scale%28.6%29\'%3E%3Ccircle cy=\'-9\' r=\'6\'/%3E%3Ccircle cx=\'8.5\' cy=\'-3\' r=\'6\'/%3E%3Ccircle cx=\'5.3\' cy=\'7.5\' r=\'6\'/%3E%3Ccircle cx=\'-5.3\' cy=\'7.5\' r=\'6\'/%3E%3Ccircle cx=\'-8.5\' cy=\'-3\' r=\'6\'/%3E%3C/g%3E%3Ccircle cx=\'86\' cy=\'22\' r=\'2.5\'/%3E%3Ccircle cx=\'22\' cy=\'88\' r=\'2\'/%3E%3C/svg%3E")', '110px 110px', null],
        'folhas' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\'%3E%3Cpath d=\'M20 50C20 26 40 14 58 12 58 34 44 52 20 50z\'/%3E%3Cpath transform=\'translate%2870 66%29 rotate%2840%29 scale%28.7%29\' d=\'M0 30C0 8 18-4 36-6 36 16 22 32 0 30z\'/%3E%3Ccircle cx=\'100\' cy=\'24\' r=\'2.5\'/%3E%3Ccircle cx=\'30\' cy=\'100\' r=\'2\'/%3E%3C/svg%3E")', '120px 120px', null],
        'xadrez' => ['repeating-conic-gradient(#000 0 25%, transparent 0 50%)', '36px 36px', '.05'],
        'losangos' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'48\' fill=\'none\' stroke=\'%23000\' stroke-width=\'2\'%3E%3Cpath d=\'M24 4 44 24 24 44 4 24z\'/%3E%3C/svg%3E")', '48px 48px', null],
        'hexagonos' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'56\' height=\'98\' fill=\'none\' stroke=\'%23000\' stroke-width=\'2\'%3E%3Cpath d=\'M28 66 0 50V17L28 1l28 16v33L28 66zM28 66v33\'/%3E%3C/svg%3E")', '56px 98px', null],
        'triangulos' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'80\'%3E%3Cpath d=\'M20 10 32 30H8z\'/%3E%3Cpath d=\'M60 50 70 66H50z\'/%3E%3Ccircle cx=\'64\' cy=\'16\' r=\'2.5\'/%3E%3Ccircle cx=\'16\' cy=\'62\' r=\'2\'/%3E%3C/svg%3E")', '80px 80px', null],
        'bolhas' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'140\' height=\'140\'%3E%3Ccircle cx=\'30\' cy=\'34\' r=\'18\'/%3E%3Ccircle cx=\'98\' cy=\'88\' r=\'28\'/%3E%3Ccircle cx=\'110\' cy=\'22\' r=\'8\'/%3E%3Ccircle cx=\'34\' cy=\'112\' r=\'10\'/%3E%3C/svg%3E")', '140px 140px', '.06'],
        'grade' => ['linear-gradient(#000 1px, transparent 1px), linear-gradient(90deg, #000 1px, transparent 1px)', '28px 28px', '.07'],
        'rabiscos' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\' fill=\'none\' stroke=\'%23000\' stroke-width=\'2.5\' stroke-linecap=\'round\'%3E%3Cpath d=\'M10 30c8-10 16 10 24 0s16 10 24 0\'/%3E%3Cpath d=\'M64 86c8-10 16 10 24 0s16 10 24 0\'/%3E%3Ccircle cx=\'96\' cy=\'28\' r=\'6\'/%3E%3Cpath d=\'M22 90l10 10M32 90 22 100\'/%3E%3C/svg%3E")', '120px 120px', null],
        'presentes' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\'%3E%3Cpath d=\'M14 30h28v24H14zM10 22h36v8H10z\'/%3E%3Cpath d=\'M28 22c-6-10-14-6-10 0zM28 22c6-10 14-6 10 0z\'/%3E%3Cpath transform=\'translate(62 60) scale(.7)\' d=\'M14 30h28v24H14zM10 22h36v8H10z\'/%3E%3Ccircle cx=\'96\' cy=\'24\' r=\'2.5\'/%3E%3Ccircle cx=\'24\' cy=\'98\' r=\'2\'/%3E%3C/svg%3E")', '120px 120px', null],
        'madeira' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'60\' fill=\'none\' stroke=\'%23000\' stroke-width=\'1.6\'%3E%3Cpath d=\'M0 12c40-8 80 8 120 0s60-6 80 0\'/%3E%3Cpath d=\'M0 30c30 6 70-6 110 2s70 2 90-2\'/%3E%3Cpath d=\'M0 48c50-6 90 6 130-2s50 0 70 2\'/%3E%3Cellipse cx=\'150\' cy=\'30\' rx=\'10\' ry=\'4\'/%3E%3C/svg%3E")', '200px 60px', '.08'],
        'diamantes' => ['url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\'%3E%3Cpath d=\'M25 10l10 14-10 14-10-14z\'/%3E%3Cpath d=\'M75 60l7 10-7 10-7-10z\'/%3E%3Ccircle cx=\'78\' cy=\'20\' r=\'2\'/%3E%3Ccircle cx=\'20\' cy=\'78\' r=\'2.5\'/%3E%3C/svg%3E")', '100px 100px', null],
    ];

    /**
     * Estilo do site (Vitrine › Página inicial): cada parte tem as suas escolhas [nome, ícone bootstrap].
     * Viram classes no <body> do catálogo (lk-{parte}-{escolha}); o primeiro de cada é o padrão.
     */
    public const LOOKS = [
        'header'  => ['Topo do site', [
            'degrade' => ['Degradê', 'bi-rainbow'], 'solido' => ['Cor única', 'bi-square-fill'], 'vidro' => ['Vidro', 'bi-window'],
            'claro' => ['Claro', 'bi-sun'], 'escuro' => ['Escuro', 'bi-moon-stars-fill'],
        ]],
        'cards'   => ['Cards dos produtos', [
            'sombra' => ['Sombra leve', 'bi-card-heading'], 'elevado' => ['Flutuando', 'bi-layers-fill'], 'borda' => ['Com borda', 'bi-bounding-box'],
            'contorno' => ['Borda colorida', 'bi-bounding-box-circles'], 'plano' => ['Sem borda', 'bi-square'],
            'brilho' => ['Sombra colorida', 'bi-stars'],
        ]],
        'pattern' => ['Desenho no fundo', [
            'nenhum' => ['Nenhum', 'bi-slash-circle'], 'pontos' => ['Bolinhas', 'bi-dot'], 'coracoes' => ['Corações', 'bi-heart-fill'],
            'estrelas' => ['Estrelas', 'bi-stars'], 'confete' => ['Confete', 'bi-balloon-fill'], 'neve' => ['Neve', 'bi-snow'],
            'listras' => ['Listras', 'bi-slash-lg'], 'ondas' => ['Ondas', 'bi-water'], 'flores' => ['Flores', 'bi-flower1'],
            'folhas' => ['Folhas', 'bi-tree-fill'], 'xadrez' => ['Xadrez', 'bi-grid-3x3'], 'losangos' => ['Losangos', 'bi-diamond'],
            'hexagonos' => ['Colmeia', 'bi-hexagon'], 'triangulos' => ['Triângulos', 'bi-triangle'], 'bolhas' => ['Bolhas', 'bi-circle'],
            'grade' => ['Quadriculado', 'bi-border-all'], 'rabiscos' => ['Rabiscos', 'bi-pencil'], 'presentes' => ['Presentes', 'bi-gift'],
            'madeira' => ['Madeira', 'bi-tree'], 'diamantes' => ['Brilhos', 'bi-gem'],
        ]],
        'patternsize' => ['Tamanho do desenho', [
            'normal' => ['Normal', 'bi-circle'], 'pequeno' => ['Pequeno', 'bi-dot'], 'grande' => ['Grande', 'bi-circle-fill'],
        ]],
        'patternop' => ['Força do desenho', [
            'normal' => ['Normal', 'bi-circle-half'], 'suave' => ['Bem leve', 'bi-circle'], 'forte' => ['Mais forte', 'bi-circle-fill'],
        ]],
        'bgtype' => ['Fundo da página', [
            'cor' => ['Cor única', 'bi-square-fill'], 'degrade' => ['Degradê', 'bi-rainbow'], 'brilho' => ['Luzes de cor', 'bi-brightness-high'],
            'malha' => ['Colorido suave', 'bi-palette'], 'topo' => ['Cor no alto', 'bi-align-top'],
        ]],
        'cardbg' => ['Cor dos cards', [
            'liso' => ['Uma cor só', 'bi-square'], 'suave' => ['Toque de cor', 'bi-droplet'], 'degrade' => ['Degradê', 'bi-rainbow'],
            'colorido' => ['Parte de baixo colorida', 'bi-layout-text-window-reverse'], 'faixa' => ['Faixa no alto', 'bi-dash-lg'],
            'cores' => ['Cada um de uma cor', 'bi-palette2'], 'vidro' => ['Vidro', 'bi-window'],
        ]],
        'price' => ['Preço', [
            'normal' => ['Normal', 'bi-currency-dollar'], 'cor' => ['Colorido', 'bi-palette-fill'], 'etiqueta' => ['Etiqueta', 'bi-tag-fill'],
            'grande' => ['Grande', 'bi-type-bold'],
        ]],
        'badge' => ['Selo de desconto', [
            'etiqueta' => ['Etiqueta', 'bi-tag'], 'pilula' => ['Pílula', 'bi-capsule'], 'circulo' => ['Círculo', 'bi-circle-fill'],
            'canto' => ['Faixa no canto', 'bi-bookmark-fill'],
        ]],
        'addbtn' => ['Botão do carrinho nos cards', [
            'redondo' => ['Redondo branco', 'bi-circle'], 'cheio' => ['Redondo colorido', 'bi-circle-fill'], 'quadrado' => ['Quadrado', 'bi-square-fill'],
            'barra' => ['Faixa "Adicionar"', 'bi-cart-plus'],
        ]],
        'buttons' => ['Botões', [
            'arredondado' => ['Arredondado', 'bi-app'], 'pilula' => ['Pílula', 'bi-capsule'], 'reto' => ['Reto', 'bi-square'],
        ]],
        'density' => ['Espaço entre os produtos', [
            'normal' => ['Normal', 'bi-grid'], 'compacto' => ['Mais produtos', 'bi-grid-3x3-gap-fill'], 'espacoso' => ['Mais espaço', 'bi-grid-1x2'],
        ]],
        'photo'   => ['Fotos dos produtos', [
            'quadrado' => ['Quadrada inteira', 'bi-square'], 'cheia' => ['Quadrada cheia', 'bi-image-fill'], 'retrato' => ['Em pé', 'bi-phone'],
        ]],
        'text'    => ['Fonte do texto', [
            'padrao' => ['Padrão (Inter)', 'bi-fonts'], 'titulos' => ['A mesma dos títulos', 'bi-type-h1'],
        ]],
        'titles'  => ['Títulos das seções', [
            'normal' => ['Normal', 'bi-type'], 'maiusculas' => ['MAIÚSCULAS', 'bi-alphabet-uppercase'], 'linha' => ['Com risco embaixo', 'bi-type-underline'],
            'centro' => ['No meio', 'bi-text-center'],
        ]],
        'width'   => ['Largura do site no computador', [
            'normal' => ['Normal', 'bi-aspect-ratio'], 'larga' => ['Mais larga', 'bi-arrows'], 'cheia' => ['Tela toda', 'bi-arrows-fullscreen'],
        ]],
        'anim'    => ['Animações', [
            'sim' => ['Com animação', 'bi-magic'], 'nao' => ['Sem animação', 'bi-pause-circle'],
        ]],
    ];

    /** Partes do Estilo do site em abas, para não virar uma lista enorme. */
    public const LOOK_GROUPS = [
        'cards' => ['Cards dos produtos', 'bi-card-heading', ['cardbg', 'cards', 'price', 'badge', 'addbtn', 'photo', 'density']],
        'fundo' => ['Fundo', 'bi-image', ['bgtype', 'pattern', 'patternsize', 'patternop']],
        'topo'  => ['Topo e largura', 'bi-window-desktop', ['header', 'width']],
        'texto' => ['Botões e textos', 'bi-fonts', ['buttons', 'text', 'titles', 'anim']],
    ];

    /** Cores de cada parte (vazio = sai das cores da loja). Ficam em portal_settings.home.paint. */
    public const PAINTS = [
        'price'   => ['Preço', 'cards'],
        'deal'    => ['Selo de desconto e "Oferta"', 'cards'],
        'add'     => ['Botão do carrinho', 'cards'],
        'photo'   => ['Fundo das fotos', 'cards'],
        'bg2'     => ['Segunda cor do fundo', 'fundo'],
        'pattern' => ['Cor do desenho', 'fundo'],
    ];

    /** Paletas prontas: [nome, topo início, topo fim, botões, fundo da página, cards]. */
    public const PALETTES = [
        ['Rosa chiclete', '#ec4899', '#f472b6', '#db2777', '#fdf2f8', '#ffffff'],
        ['Algodão doce', '#f9a8d4', '#a5b4fc', '#ec4899', '#fdf4ff', '#ffffff'],
        ['Pêssego', '#fb923c', '#fda4af', '#ea580c', '#fff7ed', '#ffffff'],
        ['Pôr do sol', '#f97316', '#db2777', '#e11d48', '#fff7ed', '#ffffff'],
        ['Vinho', '#7f1d1d', '#9f1239', '#be123c', '#fff1f2', '#ffffff'],
        ['Lilás', '#7c3aed', '#a78bfa', '#6d28d9', '#f5f3ff', '#ffffff'],
        ['Azul oceano', '#0369a1', '#0ea5e9', '#0284c7', '#f0f9ff', '#ffffff'],
        ['Turquesa', '#0d9488', '#06b6d4', '#0f766e', '#f0fdfa', '#ffffff'],
        ['Verde menta', '#059669', '#34d399', '#047857', '#ecfdf5', '#ffffff'],
        ['Floresta', '#14532d', '#166534', '#15803d', '#f0fdf4', '#ffffff'],
        ['Madeira', '#78350f', '#92400e', '#b45309', '#f5efe6', '#fffbf5'],
        ['Café com leite', '#6f4e37', '#a47148', '#8d6e63', '#f7f1ea', '#fffdf9'],
        ['Terracota', '#9a3412', '#c2410c', '#b45309', '#fdf6ee', '#fffaf5'],
        ['Areia', '#a8a29e', '#d6d3d1', '#78716c', '#f5f5f4', '#ffffff'],
        ['Grafite', '#18181b', '#3f3f46', '#18181b', '#f4f4f5', '#ffffff'],
        ['Dourado', '#1c1917', '#44403c', '#ca8a04', '#fafaf9', '#ffffff'],
        ['Noite azul', '#0f172a', '#1e293b', '#38bdf8', '#020617', '#0f172a'],
        ['Preto e dourado', '#0a0a0a', '#262626', '#eab308', '#0a0a0a', '#171717'],
    ];

    /** O que cada tema já traz de estilo (o resto fica no padrão). */
    public const THEME_LOOKS = [
        'black'      => ['header' => 'escuro', 'cards' => 'borda', 'buttons' => 'reto'],
        'natal'      => ['pattern' => 'neve', 'cards' => 'elevado'],
        'maes'       => ['pattern' => 'coracoes', 'buttons' => 'pilula', 'cards' => 'elevado'],
        'namorados'  => ['pattern' => 'coracoes', 'buttons' => 'pilula', 'cards' => 'elevado'],
        'pais'       => ['header' => 'solido', 'cards' => 'borda'],
        'criancas'   => ['pattern' => 'confete', 'buttons' => 'pilula'],
        'consumidor' => ['cards' => 'contorno'],
        'cliente'    => ['pattern' => 'estrelas', 'header' => 'vidro'],
        'pascoa'     => ['pattern' => 'pontos', 'buttons' => 'pilula'],
        'liquida'    => ['pattern' => 'listras', 'cards' => 'contorno', 'buttons' => 'reto'],
        'ano_novo'   => ['pattern' => 'estrelas', 'header' => 'escuro', 'text' => 'titulos'],
        'volta_aulas'=> ['pattern' => 'pontos', 'buttons' => 'pilula'],
        'carnaval'   => ['pattern' => 'confete', 'buttons' => 'pilula', 'cards' => 'elevado'],
        'mulher'     => ['pattern' => 'flores', 'buttons' => 'pilula', 'cards' => 'elevado'],
        'junina'     => ['pattern' => 'listras', 'cards' => 'borda'],
        'minimalista'=> ['header' => 'claro', 'cards' => 'borda', 'buttons' => 'reto', 'titles' => 'maiusculas', 'photo' => 'cheia'],
        'luxo'       => ['header' => 'escuro', 'cards' => 'contorno', 'text' => 'titulos', 'titles' => 'centro', 'buttons' => 'reto'],
        'natural'    => ['pattern' => 'folhas', 'cards' => 'plano', 'buttons' => 'pilula'],
        'pastel'     => ['pattern' => 'pontos', 'buttons' => 'pilula', 'cards' => 'elevado'],
        'neon'       => ['header' => 'vidro', 'cards' => 'contorno', 'pattern' => 'estrelas'],
        'oceano'     => ['pattern' => 'ondas', 'cards' => 'elevado'],
        'floral'     => ['pattern' => 'flores', 'buttons' => 'pilula', 'cards' => 'elevado', 'text' => 'titulos'],
        'rustico'    => ['cards' => 'borda', 'photo' => 'cheia', 'text' => 'titulos', 'titles' => 'linha'],
        'escandinavo'=> ['header' => 'claro', 'cards' => 'plano', 'buttons' => 'pilula', 'density' => 'espacoso', 'photo' => 'cheia'],
        'vintage'    => ['pattern' => 'listras', 'text' => 'titulos', 'titles' => 'linha', 'cards' => 'borda'],
        'tropical'   => ['pattern' => 'folhas', 'buttons' => 'pilula', 'cards' => 'elevado'],
        'noite'      => ['header' => 'escuro', 'cards' => 'borda', 'pattern' => 'estrelas', 'text' => 'titulos'],
        'cafe'       => ['cards' => 'elevado', 'titles' => 'linha', 'pattern' => 'pontos'],
        'lavanda'    => ['pattern' => 'flores', 'buttons' => 'pilula', 'cards' => 'elevado'],
        'esportivo'  => ['header' => 'escuro', 'cards' => 'contorno', 'buttons' => 'reto', 'titles' => 'maiusculas', 'pattern' => 'listras'],
    ];

    /**
     * Automações (Vitrine › Automações), em portal_settings.home.auto. Nada roda escondido:
     * tudo é decidido na hora em que o cliente abre o catálogo, então não depende do cron.
     * theme: troca para o tema da data comercial enquanto ela dura (banner e aviso juntos, se ligados);
     * skip: datas que ficam de fora; featured: completa a fileira de destaques (vendidos|ofertas|novos);
     * newbadge: selo "Novidade" nos produtos cadastrados há até N dias; sort: ordem da lista de produtos;
     * cats: cria sozinha a coleção de cada categoria nova.
     */
    public const AUTO_DEFAULT = ['theme' => false, 'banner' => true, 'notice' => true, 'skip' => [], 'featured' => '', 'newbadge' => 0, 'sort' => '', 'cats' => false];

    public const AUTO_FEATURED = ['' => 'Não, só os que eu escolher', 'vendidos' => 'Com os mais vendidos', 'ofertas' => 'Com os produtos em oferta', 'novos' => 'Com os mais novos'];

    public const AUTO_SORT = ['' => 'Os atualizados por último', 'vendidos' => 'Mais vendidos primeiro', 'novos' => 'Mais novos primeiro', 'menor' => 'Menor preço primeiro', 'nome' => 'Por nome (A a Z)'];

    public const AUTO_NEW = [0 => 'Desligado', 7 => '7 dias', 15 => '15 dias', 30 => '30 dias'];

    /** Automações da loja, completas. */
    public static function auto(?PortalSetting $s): array
    {
        $a = array_merge(self::AUTO_DEFAULT, (array) (($s?->home ?? [])['auto'] ?? []));

        return [
            'theme'    => (bool) $a['theme'],
            'banner'   => (bool) $a['banner'],
            'notice'   => (bool) $a['notice'],
            'skip'     => array_values(array_intersect((array) $a['skip'], array_keys(CommercialCalendar::DATES))),
            'featured' => isset(self::AUTO_FEATURED[$a['featured']]) ? (string) $a['featured'] : '',
            'newbadge' => isset(self::AUTO_NEW[(int) $a['newbadge']]) ? (int) $a['newbadge'] : 0,
            'sort'     => isset(self::AUTO_SORT[$a['sort']]) ? (string) $a['sort'] : '',
            'cats'     => (bool) $a['cats'],
        ];
    }

    private static array $autoMemo = [];

    /** Automações já lidas do banco, uma vez por pedido. */
    public static function autoFor(int $ownerId): array
    {
        return self::$autoMemo[$ownerId] ??= self::auto(self::ready() ? PortalSetting::where('user_id', $ownerId)->first() : null);
    }

    /** Temas de data (que existem e cabem na loja) com o período deles: [key => próxima ocorrência]. */
    public static function dateThemes(?string $kind = null): array
    {
        $hidden = CommercialCalendar::HIDDEN[$kind] ?? [];
        $out = [];
        foreach (self::THEMES as $key => $t) {
            if ($t['date'] && ! in_array($t['date'], $hidden, true) && ($n = CommercialCalendar::next($t['date']))) {
                $out[$key] = $n;
            }
        }
        uasort($out, fn ($a, $b) => $a['start'] <=> $b['start']);

        return $out;
    }

    /** Tema de data que a automação está usando agora: [tema, com banner, com aviso] ou null. */
    public static function autoTheme(?int $ownerId): ?array
    {
        if (! $ownerId || ! self::ready()) {
            return null;
        }
        $a = self::autoFor($ownerId);
        if (! $a['theme']) {
            return null;
        }
        foreach (self::dateThemes(\App\Services\Promotions\StoreKind::for($ownerId)) as $key => $when) {
            if ($when['now'] && ! in_array(self::THEMES[$key]['date'], $a['skip'], true)) {
                return [$key, $a['banner'], $a['notice']];
            }
        }

        return null;
    }

    /** O que o catálogo mostra no lugar do tema salvo: a prévia do dono ou a data da automação. */
    public static function override(?int $ownerId): ?array
    {
        return self::previewing($ownerId) ?? self::autoTheme($ownerId);
    }

    /** Selo "Novidade": produto cadastrado há até N dias (automação). */
    public static function isNew(\App\Models\Product $p): bool
    {
        $days = self::autoFor((int) $p->user_id)['newbadge'];

        return $days > 0 && $p->created_at && $p->created_at->gte(now()->subDays($days));
    }

    /** Estilo completo: padrão, por cima o do tema e por cima o que a loja escolheu. */
    public static function look(string $theme, $saved = null): array
    {
        $out = array_map(fn ($l) => array_key_first($l[1]), self::LOOKS);
        $out = array_merge($out, self::THEME_LOOKS[$theme] ?? []);
        foreach ((array) $saved as $k => $v) {
            if (isset(self::LOOKS[$k][1][$v])) {
                $out[$k] = $v;
            }
        }

        return $out;
    }

    /** Classes do <body> do catálogo para o estilo. */
    public static function lookClasses(?array $look): string
    {
        return collect($look ?? [])->map(fn ($v, $k) => "lk-{$k}-{$v}")->implode(' ');
    }

    public static function ready(): bool
    {
        return Schema::hasTable('portal_settings') && Schema::hasColumn('portal_settings', 'home');
    }

    /** Temas que combinam com o tipo de loja, com o período da data quando tiver. */
    public static function themes(?string $kind = null): array
    {
        $hidden = CommercialCalendar::HIDDEN[$kind] ?? [];
        $out = [];
        foreach (self::THEMES as $key => $t) {
            if ($t['date'] && in_array($t['date'], $hidden, true)) {
                continue;
            }
            $t['when'] = $t['date'] ? CommercialCalendar::next($t['date']) : null;
            $out[$key] = $t;
        }

        return $out;
    }

    /**
     * Prévia de um tema sem aplicar (Vitrine › Página inicial): o catálogo aberto com ?tema=
     * pelo próprio dono, logado no app, mostra tudo como vai ficar. Para os outros, nada muda.
     * Retorna [tema, com banner, com aviso] ou null.
     */
    public static function previewing(?int $ownerId): ?array
    {
        $key = (string) request()->query('tema', '');
        if (! $ownerId || $key === '' || ! isset(self::THEMES[$key]) || (int) \Illuminate\Support\Facades\Auth::guard('web')->id() !== $ownerId) {
            return null;
        }

        return [$key, request()->query('com_banner', '1') !== '0', request()->query('com_aviso', '1') !== '0'];
    }

    /** Montagem salva, completa (blocos novos entram no fim). Na prévia de tema, a do tema. */
    public static function get(?PortalSetting $s, bool $live = false): array
    {
        $h = ($s?->home ?? []) + ['theme' => 'padrao', 'order' => [], 'off' => [], 'colors' => null, 'style' => 'padrao', 'surface' => null, 'look' => null, 'paint' => null];
        $owner = $s?->user_id ? (int) $s->user_id : null;
        if ($pv = $live ? self::override($owner) : self::previewing($owner)) {
            $t = self::THEMES[$pv[0]];
            $h = ['theme' => $pv[0], 'order' => $t['order'], 'off' => [], 'colors' => $t['colors'], 'style' => $t['style'], 'surface' => self::SURFACES[$pv[0]] ?? null, 'look' => null, 'paint' => null];
        }
        $order = array_values(array_intersect((array) $h['order'], array_keys(self::SECTIONS)));
        // Bloco novo entra logo depois do que vem antes dele na ordem padrão.
        foreach (self::DEFAULT_ORDER as $i => $k) {
            if (! in_array($k, $order, true)) {
                $prev = $i > 0 ? array_search(self::DEFAULT_ORDER[$i - 1], $order, true) : -1;
                array_splice($order, $prev === false ? count($order) : $prev + 1, 0, [$k]);
            }
        }
        $colors = is_array($h['colors']) && count($h['colors']) === 3 ? array_values($h['colors']) : (self::THEMES[$h['theme']]['colors'] ?? self::BASE);
        $colors = array_map(fn ($c, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $c) ? strtolower($c) : $d, $colors, self::BASE);
        $theme = isset(self::THEMES[$h['theme']]) ? $h['theme'] : 'padrao';
        $surface = is_array($h['surface']) && count($h['surface']) === 2 ? array_values($h['surface']) : (self::SURFACES[$theme] ?? self::SURFACES['padrao']);
        $surface = array_map(fn ($c, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $c) ? strtolower($c) : $d, $surface, self::SURFACES['padrao']);

        return [
            'theme'   => $theme,
            'order'   => $order,
            'off'     => array_values(array_intersect((array) $h['off'], array_keys(self::SECTIONS))),
            'colors'  => $colors,
            'style'   => isset(self::STYLES[$h['style']]) ? $h['style'] : 'padrao',
            'surface' => $surface,
            'look'    => self::look($theme, $h['look']),
            'paint'   => self::paint($h['paint']),
        ];
    }

    /** Só as cores de parte válidas; o resto fica de fora (= automático). */
    public static function paint($saved): array
    {
        $out = [];
        foreach ((array) $saved as $k => $v) {
            if (isset(self::PAINTS[$k]) && preg_match('/^#[0-9a-fA-F]{6}$/', (string) $v)) {
                $out[$k] = strtolower($v);
            }
        }

        return $out;
    }

    public static function save(int $ownerId, array $layout): void
    {
        $s = PortalSetting::for($ownerId);
        $s->home = array_merge($s->home ?? [], array_intersect_key($layout, array_flip(['theme', 'order', 'off', 'colors', 'style', 'surface', 'look', 'paint', 'auto'])));
        $s->save();
    }

    /** Cores e cantos do catálogo (portal-catalog-layout). Sem montagem salva, null = cores da logo. */
    public static function cssTheme(?int $ownerId): ?array
    {
        if (! $ownerId || ! self::ready()) {
            return null;
        }
        $s = PortalSetting::where('user_id', $ownerId)->first();
        $preview = self::override($ownerId);
        if (! $preview && (! $s || empty($s->home['colors']) && empty($s->home['surface']) && empty($s->home['look']) && empty($s->home['paint']) && ($s->home['style'] ?? 'padrao') === 'padrao' && ($s->home['theme'] ?? 'padrao') === 'padrao')) {
            return null;
        }
        $h = self::get($s ?? new PortalSetting(['user_id' => $ownerId]), true);
        [$a, $b, $p] = $h['colors'];
        // Cards escuros com botões escuros somem: os links e botões ficam dourados.
        if (self::isDark($h['surface'][1]) && self::isDark($p)) {
            $p = '#f59e0b';
        }

        return array_merge([
            'header' => "linear-gradient(90deg, {$a} 0%, {$b} 100%)",
            'hero'   => "linear-gradient(135deg, {$a} 0%, {$b} 100%)",
            'primary'=> $p,
            'dark'   => self::shade($p, -0.18),
            'soft'   => self::shade($p, 0.88),
            'meta'   => $a,
            'radius' => self::STYLES[$h['style']][1],
            'a'      => $a,
            'look'   => $h['look'],
            'b'      => $b,
            'paint'  => $h['paint'],
        ], self::surfaceVars($h['surface'], $p));
    }

    /** Fundo, cards e as cores de texto que combinam (claro em fundo escuro). */
    public static function surfaceVars(array $surface, string $primary): array
    {
        [$bg, $card] = $surface;
        $dark = self::isDark($card);
        $darkBg = self::isDark($bg);

        return [
            'bg'        => $bg,
            'card'      => $card,
            'text'      => $dark ? '#f4f4f5' : '#333333',
            'muted'     => $dark ? '#a1a1aa' : '#737373',
            'line'      => $dark ? '#2e2e35' : self::shade($bg, -0.06),
            'page_text' => $darkBg ? '#e4e4e7' : '#555555',
            'chip'      => $dark ? self::shade($card, 0.08) : '#ffffff',
            'soft'      => $dark ? self::shade($primary, -0.62) : self::shade($primary, 0.88),
            'dark_mode' => $dark,
        ];
    }

    public static function isDark(string $hex): bool
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%02x%02x%02x');

        return (0.299 * $r + 0.587 * $g + 0.114 * $b) < 128;
    }

    /** Clareia (amount > 0, até 1) ou escurece (amount < 0) uma cor. */
    public static function shade(string $hex, float $amount): string
    {
        $rgb = sscanf(ltrim($hex, '#'), '%02x%02x%02x');
        $rgb = array_map(fn ($v) => (int) round($amount >= 0 ? $v + (255 - $v) * $amount : $v * (1 + $amount)), $rgb);

        return sprintf('#%02x%02x%02x', ...array_map(fn ($v) => max(0, min(255, $v)), $rgb));
    }

    /**
     * Aplica um tema: cores, cantos, ordem dos blocos e o título dos destaques; e, se pedido,
     * cria o banner do evento (no começo do carrossel, no período da data) e troca o aviso do topo.
     */
    public static function apply(int $ownerId, string $key, string $kind, bool $banner, bool $notice): array
    {
        $t = self::THEMES[$key] ?? self::THEMES['padrao'];
        $s = PortalSetting::for($ownerId);
        $s->home = array_merge($s->home ?? [], ['theme' => $key, 'order' => $t['order'], 'off' => [], 'colors' => $t['colors'], 'style' => $t['style'], 'surface' => self::SURFACES[$key] ?? null, 'look' => null, 'paint' => null]);
        if ($t['featured']) {
            $s->featured_title = $t['featured'];
        } elseif ($key === 'padrao') {
            $s->featured_title = null;
        }
        $done = [];
        $moveis = $kind === 'moveis';
        if ($notice && $t['notice']) {
            $s->notice_text = $t['notice'][$moveis ? 1 : 0];
            $s->notice_active = true;
            $done[] = 'aviso';
        }
        $s->save();

        if ($banner && ($data = self::bannerData($ownerId, $key, $kind))) {
            $exists = PortalBanner::where('user_id', $ownerId)->where('placement', 'hero')
                ->where('design->tpl', $t['tpl'])->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', now()->toDateString()))->first();
            if ($exists) {
                $exists->update(['active' => true, 'sort' => $data['sort']]);
            } else {
                PortalBanner::create($data);
            }
            $done[] = 'banner';
        }

        return $done;
    }

    /** O banner que o tema cria (no começo do carrossel, no período da data). Também usado na prévia. */
    public static function bannerData(int $ownerId, string $key, string $kind): ?array
    {
        $t = self::THEMES[$key] ?? null;
        if (! $t || ! $t['tpl'] || ! ($tpl = StorefrontService::template($t['tpl'], $kind))) {
            return null;
        }
        $when = $t['date'] ? CommercialCalendar::next($t['date']) : null;

        return [
            'user_id'     => $ownerId,
            'placement'   => 'hero',
            'title'       => $tpl['title'],
            'subtitle'    => $tpl['subtitle'],
            'button_text' => $tpl['button'],
            'link_type'   => $tpl['link'] ?? 'none',
            'link_value'  => null,
            'sort'        => (int) PortalBanner::where('user_id', $ownerId)->where('placement', 'hero')->min('sort') - 1,
            'active'      => true,
            'starts_at'   => null,
            'ends_at'     => $when ? $when['end']->toDateString() : now()->addDays(7)->toDateString(),
            'design'      => ['tpl' => $t['tpl'], 'badge' => $tpl['badge'], 'c1' => $tpl['c1'], 'c2' => $tpl['c2'], 'acc' => $tpl['acc'], 'ink' => $tpl['ink'],
                'lay' => 'esq', 'src' => in_array($tpl['src'] ?? 'manual', ['novos', 'vendidos', 'ofertas'], true) ? $tpl['src'] : 'vendidos',
                'anim' => 'vivo', 'fmt' => StorefrontService::format($tpl['fmt'] ?? null), 'photos' => []],
        ];
    }
}
