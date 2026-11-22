<?php

namespace App\Services\Portal;

use App\Models\PortalProductPage;
use Illuminate\Support\Facades\Schema;

/**
 * Página especial de um produto: blocos (banner, foto com texto, destaques,
 * galeria, vídeo, medidas, perguntas, depoimentos, contagem, chamada…) e
 * aparência própria. Fica no produto principal da família, então vale para
 * todas as variações.
 */
class ProductPageService
{
    public const MAX_BLOCKS = 30;

    /** Tipos de bloco: nome, ícone (Bootstrap Icons), explicação e valores iniciais. */
    public const BLOCKS = [
        'banner' => ['Banner', 'bi-image', 'Faixa grande com título, foto e botão', [
            'badge' => 'Novidade', 'title' => 'Título do banner', 'text' => 'Uma frase que convence o cliente.', 'image' => null,
            'c1' => '#7c3aed', 'c2' => '#db2777', 'ink' => 'light', 'lay' => 'dir', 'button' => 'Quero o meu', 'action' => 'comprar', 'where' => 'meio',
        ]],
        'faixa' => ['Faixa de aviso', 'bi-megaphone', 'Uma linha colorida (frete, prazo, brinde)', [
            'text' => 'Frete grátis para a sua cidade', 'icon' => 'bi-truck', 'c1' => '#16a34a', 'where' => 'meio',
        ]],
        'texto' => ['Texto', 'bi-text-paragraph', 'Título e parágrafos', [
            'title' => 'Sobre este produto', 'text' => 'Conte a história do produto, para quem ele é e por que vale a pena.', 'align' => 'esq',
        ]],
        'foto_texto' => ['Foto com texto', 'bi-layout-split', 'Foto de um lado e texto do outro', [
            'image' => null, 'photo' => 1, 'title' => 'Feito para durar', 'text' => 'Explique um detalhe importante do produto.', 'side' => 'esq',
        ]],
        'destaques' => ['Destaques', 'bi-stars', 'Ícones com vantagens do produto', [
            'title' => 'Por que escolher', 'items' => [
                ['icon' => 'bi-award', 'title' => 'Qualidade', 'text' => 'Material escolhido a dedo.'],
                ['icon' => 'bi-truck', 'title' => 'Entrega rápida', 'text' => 'Enviamos em poucos dias.'],
                ['icon' => 'bi-shield-check', 'title' => 'Garantia', 'text' => 'Troca fácil se precisar.'],
            ],
        ]],
        'galeria' => ['Galeria de fotos', 'bi-images', 'Fotos extras, que também ampliam', [
            'title' => 'Mais fotos', 'photos' => [], 'cols' => 3, 'product_photos' => true,
        ]],
        'video' => ['Vídeo', 'bi-play-btn', 'YouTube, Vimeo, Instagram ou MP4', [
            'title' => 'Veja de perto', 'url' => '',
        ]],
        'medidas' => ['Medidas e ficha', 'bi-rulers', 'Tabela de medidas, material e cuidados', [
            'title' => 'Medidas', 'image' => null, 'rows' => [['k' => 'Altura', 'v' => ''], ['k' => 'Largura', 'v' => ''], ['k' => 'Profundidade', 'v' => '']],
        ]],
        'perguntas' => ['Perguntas frequentes', 'bi-question-circle', 'Perguntas e respostas que abrem e fecham', [
            'title' => 'Dúvidas frequentes', 'items' => [['q' => 'Qual o prazo de entrega?', 'a' => 'Respondemos o prazo certinho no WhatsApp.']],
        ]],
        'depoimentos' => ['Depoimentos', 'bi-chat-quote', 'O que os clientes disseram', [
            'title' => 'Quem comprou aprovou', 'items' => [['text' => 'Chegou rapidinho e é lindo pessoalmente!', 'name' => 'Cliente']],
        ]],
        'contagem' => ['Contagem regressiva', 'bi-alarm', 'Oferta que acaba numa data', [
            'title' => 'A oferta acaba em', 'text' => 'Garanta o seu antes que acabe.', 'ends' => '', 'c1' => '#dc2626',
        ]],
        'chamada' => ['Botão de compra', 'bi-bag-check', 'Chamada final com botão', [
            'title' => 'Gostou?', 'text' => 'Garanta o seu agora.', 'button' => 'Comprar agora', 'action' => 'comprar', 'c1' => '#3483fa',
        ]],
        'espaco' => ['Espaço / linha', 'bi-distribute-vertical', 'Respiro ou linha entre blocos', [
            'size' => 'm', 'line' => false,
        ]],
    ];

    /** Estilo geral da página. */
    public const DEFAULT_SETTINGS = [
        'acc' => '#7c3aed',      // cor de destaque (títulos, ícones, botões)
        'bg' => '',              // fundo das seções ('' = o do tema do site)
        'style' => 'cards',      // cards | inteiro (seções coladas, sem caixa)
        'round' => 'm',          // cantos: s | m | l
        'title_size' => 'm',     // títulos: s | m | l
        'hide_about' => false,   // esconder "Descrição e características"
        'hide_together' => false,
        'hide_related' => false,
        'hide_reviews' => false,
        'variants' => '',        // '' = igual à loja | botoes | lista
        'variants_theme' => '',  // '' = igual à loja | chave de VARIANT_THEMES
    ];

    /** Como as variações aparecem na página do produto. */
    public const VARIANT_MODES = [
        'botoes' => ['Botões', 'bi-grid-3x2-gap', 'Todas as opções à mostra, lado a lado'],
        'lista' => ['Lista suspensa', 'bi-menu-button-wide', 'Um botão que abre um menu colorido com as opções'],
    ];

    /** Cores do menu de variações: nome, cor do alto, cor de baixo. */
    public const VARIANT_THEMES = [
        'aurora' => ['Rosa e roxo', '#e8577e', '#584a8e'],
        'oceano' => ['Oceano', '#22b8cf', '#3b4cca'],
        'sol' => ['Pôr do sol', '#f59e0b', '#e11d48'],
        'floresta' => ['Floresta', '#34d399', '#0f766e'],
        'madeira' => ['Madeira', '#c2853d', '#3f2a1d'],
        'noite' => ['Noite', '#475569', '#0f172a'],
        'uva' => ['Uva', '#c084fc', '#6d28d9'],
    ];

    /** Escolha da loja para todas as páginas de produto. */
    public static function storeOptions(int $ownerId): array
    {
        static $memo = [];
        if (! isset($memo[$ownerId])) {
            $home = Schema::hasTable('portal_settings') ? (\App\Models\PortalSetting::where('user_id', $ownerId)->value('home') ?? []) : [];
            $home = is_string($home) ? (json_decode($home, true) ?: []) : (array) $home;
            $o = (array) ($home['product_page'] ?? []);
            $memo[$ownerId] = [
                'variants' => isset(self::VARIANT_MODES[$o['variants'] ?? '']) ? $o['variants'] : 'botoes',
                'variants_theme' => isset(self::VARIANT_THEMES[$o['variants_theme'] ?? '']) ? $o['variants_theme'] : 'aurora',
            ];
        }

        return $memo[$ownerId];
    }

    /** Variações desta página: a escolha da página especial vence a da loja. */
    public static function variantsFor(int $ownerId, ?array $special): array
    {
        $o = self::storeOptions($ownerId);
        $s = $special['settings'] ?? [];
        $mode = isset(self::VARIANT_MODES[$s['variants'] ?? '']) ? $s['variants'] : $o['variants'];
        $theme = isset(self::VARIANT_THEMES[$s['variants_theme'] ?? '']) ? $s['variants_theme'] : $o['variants_theme'];

        return ['mode' => $mode, 'theme' => $theme, 'c1' => self::VARIANT_THEMES[$theme][1], 'c2' => self::VARIANT_THEMES[$theme][2]];
    }

    public const PALETTE = ['#7c3aed', '#db2777', '#e11d48', '#ea580c', '#d97706', '#16a34a', '#0d9488', '#0284c7', '#3483fa', '#4f46e5', '#0f172a', '#78350f'];

    public const ICONS = ['bi-award', 'bi-truck', 'bi-shield-check', 'bi-heart', 'bi-star', 'bi-gem', 'bi-tree', 'bi-hammer', 'bi-rulers', 'bi-box-seam', 'bi-gift', 'bi-lightning', 'bi-droplet', 'bi-flower1', 'bi-sun', 'bi-patch-check', 'bi-clock', 'bi-cash-coin', 'bi-credit-card', 'bi-whatsapp', 'bi-house-heart', 'bi-recycle', 'bi-palette', 'bi-emoji-smile'];

    /** Modelos prontos: blocos para começar (tudo editável depois). */
    public static function templates(): array
    {
        $b = fn (string $type, array $over = []) => ['type' => $type] + $over + self::BLOCKS[$type][3];

        return [
            'lancamento' => ['Lançamento', 'bi-rocket-takeoff', 'Banner de novidade, destaques, fotos e perguntas', ['acc' => '#7c3aed'], [
                $b('banner', ['badge' => 'Lançamento', 'title' => 'Chegou a novidade', 'text' => 'O {nome} que você estava esperando.', 'where' => 'meio']),
                $b('destaques'),
                $b('foto_texto'),
                $b('galeria'),
                $b('perguntas'),
                $b('chamada'),
            ]],
            'oferta' => ['Oferta relâmpago', 'bi-lightning-charge', 'Contagem regressiva e chamada forte', ['acc' => '#dc2626'], [
                $b('faixa', ['text' => 'Oferta por tempo limitado', 'icon' => 'bi-lightning', 'c1' => '#dc2626', 'where' => 'topo']),
                $b('contagem', ['ends' => now()->addDays(3)->format('Y-m-d\TH:i')]),
                $b('destaques', ['title' => 'Aproveite']),
                $b('depoimentos'),
                $b('chamada', ['title' => 'Últimas unidades', 'button' => 'Garantir o meu', 'c1' => '#dc2626']),
            ]],
            'moveis' => ['Móvel sob medida', 'bi-house-gear', 'Medidas, material, fotos do ambiente e prazo', ['acc' => '#78350f'], [
                $b('banner', ['badge' => 'Feito para você', 'title' => 'Do tamanho do seu espaço', 'text' => 'Ferro e madeira, feito sob medida depois do pedido.', 'c1' => '#78350f', 'c2' => '#1c1917', 'where' => 'meio']),
                $b('foto_texto', ['title' => 'Do jeito que você imaginou', 'text' => 'Escolha o tamanho e o acabamento. Fazemos a peça depois do pedido.']),
                $b('medidas', ['rows' => [['k' => 'Altura', 'v' => ''], ['k' => 'Largura', 'v' => ''], ['k' => 'Profundidade', 'v' => ''], ['k' => 'Material', 'v' => 'Ferro e madeira'], ['k' => 'Prazo', 'v' => 'Combinado no pedido']]]),
                $b('destaques', ['items' => [
                    ['icon' => 'bi-hammer', 'title' => 'Feito à mão', 'text' => 'Produção própria, peça por peça.'],
                    ['icon' => 'bi-rulers', 'title' => 'Sob medida', 'text' => 'Vários tamanhos e formatos.'],
                    ['icon' => 'bi-tree', 'title' => 'Madeira de verdade', 'text' => 'Acabamento resistente.'],
                ]]),
                $b('galeria', ['title' => 'Em ambientes']),
                $b('perguntas', ['items' => [['q' => 'Faz em outro tamanho?', 'a' => 'Faz sim. Chame no WhatsApp com a medida.'], ['q' => 'Qual o prazo?', 'a' => 'Combinamos no pedido, conforme o tamanho.']]]),
                $b('chamada', ['title' => 'Quer em outra medida?', 'text' => 'Fale com a gente e receba o orçamento.', 'button' => 'Chamar no WhatsApp', 'action' => 'whatsapp', 'c1' => '#16a34a']),
            ]],
            'presente' => ['Presente', 'bi-gift', 'Para datas especiais, com depoimentos', ['acc' => '#db2777'], [
                $b('banner', ['badge' => 'Presente perfeito', 'title' => 'Surpreenda quem você ama', 'text' => 'Um presente que emociona de verdade.', 'c1' => '#db2777', 'c2' => '#f59e0b', 'where' => 'meio']),
                $b('texto', ['title' => 'Um presente que emociona', 'align' => 'centro']),
                $b('galeria'),
                $b('depoimentos'),
                $b('chamada', ['button' => 'Quero presentear']),
            ]],
            'beleza' => ['Beleza e cuidados', 'bi-flower1', 'Benefícios, modo de usar e vídeo', ['acc' => '#be185d'], [
                $b('banner', ['badge' => 'Mais vendido', 'title' => 'O cuidado que você merece', 'text' => 'Conheça o {nome}.', 'c1' => '#be185d', 'c2' => '#7c3aed', 'where' => 'meio']),
                $b('destaques', ['title' => 'Benefícios', 'items' => [
                    ['icon' => 'bi-droplet', 'title' => 'Hidrata', 'text' => 'Sensação de pele macia.'],
                    ['icon' => 'bi-flower1', 'title' => 'Perfume marcante', 'text' => 'Fragrância que dura.'],
                    ['icon' => 'bi-patch-check', 'title' => 'Original', 'text' => 'Produto com nota.'],
                ]]),
                $b('texto', ['title' => 'Como usar', 'text' => 'Passo 1: ...\nPasso 2: ...']),
                $b('video'),
                $b('depoimentos'),
            ]],
            'branco' => ['Em branco', 'bi-file-earmark', 'Comece do zero e adicione os blocos', ['acc' => '#3483fa'], []],
        ];
    }

    public static function ready(): bool
    {
        static $ok = null;

        return $ok ??= Schema::hasTable('portal_product_pages');
    }

    /** Página ativa do produto (ou a salva, mesmo desligada, na prévia do dono). */
    public static function for(int $ownerId, int $rootId, bool $preview = false): ?array
    {
        if (! self::ready()) {
            return null;
        }
        $page = PortalProductPage::where('user_id', $ownerId)->where('product_id', $rootId)->first();
        if (! $page || (! $page->active && ! $preview)) {
            return null;
        }

        return ['settings' => self::settings($page->settings), 'blocks' => self::blocks($page->blocks ?? [])];
    }

    public static function settings(?array $s): array
    {
        $s = array_intersect_key((array) $s, self::DEFAULT_SETTINGS) + self::DEFAULT_SETTINGS;
        $s['acc'] = self::color($s['acc'], self::DEFAULT_SETTINGS['acc']);
        $s['bg'] = $s['bg'] ? self::color($s['bg'], '') : '';
        $s['style'] = in_array($s['style'], ['cards', 'inteiro'], true) ? $s['style'] : 'cards';
        foreach (['round', 'title_size'] as $k) {
            $s[$k] = in_array($s[$k], ['s', 'm', 'l'], true) ? $s[$k] : 'm';
        }
        foreach (['hide_about', 'hide_together', 'hide_related', 'hide_reviews'] as $k) {
            $s[$k] = (bool) $s[$k];
        }
        $s['variants'] = isset(self::VARIANT_MODES[$s['variants']]) ? $s['variants'] : '';
        $s['variants_theme'] = isset(self::VARIANT_THEMES[$s['variants_theme']]) ? $s['variants_theme'] : '';

        return $s;
    }

    /** Limpa os blocos salvos: só tipos conhecidos, com os campos completos. */
    public static function blocks(array $blocks): array
    {
        return collect($blocks)
            ->filter(fn ($b) => is_array($b) && isset(self::BLOCKS[$b['type'] ?? '']))
            ->map(fn ($b) => ['type' => $b['type'], 'id' => (string) ($b['id'] ?? uniqid('b'))] + ['hidden' => (bool) ($b['hidden'] ?? false)] + array_intersect_key($b, self::BLOCKS[$b['type']][3]) + self::BLOCKS[$b['type']][3])
            ->take(self::MAX_BLOCKS)
            ->values()->all();
    }

    public static function block(string $type, array $over = []): array
    {
        return ['type' => $type, 'id' => uniqid('b'), 'hidden' => false] + $over + self::BLOCKS[$type][3];
    }

    public static function color(?string $c, string $fallback): string
    {
        return preg_match('/^#[0-9a-f]{6}$/i', (string) $c) ? strtolower($c) : $fallback;
    }

    /** Texto do dono com {nome} trocado pelo nome do produto. */
    public static function text(?string $v, string $name): string
    {
        return str_replace('{nome}', $name, (string) $v);
    }

    public static function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
    }

    /** Endereço para embutir o vídeo (YouTube, Vimeo, Instagram) ou arquivo MP4. */
    public static function video(?string $url): ?array
    {
        $url = trim((string) $url);
        if ($url === '' || ! preg_match('~^https?://~i', $url)) {
            return null;
        }
        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([\w-]{6,20})~i', $url, $m)) {
            return ['kind' => 'frame', 'src' => 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0', 'tall' => str_contains($url, '/shorts/')];
        }
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $url, $m)) {
            return ['kind' => 'frame', 'src' => 'https://player.vimeo.com/video/' . $m[1], 'tall' => false];
        }
        if (preg_match('~instagram\.com/(?:p|reel|reels|tv)/([\w-]+)~i', $url, $m)) {
            return ['kind' => 'frame', 'src' => 'https://www.instagram.com/p/' . $m[1] . '/embed', 'tall' => true];
        }
        if (preg_match('~\.(mp4|webm|mov)(\?.*)?$~i', $url)) {
            return ['kind' => 'file', 'src' => $url, 'tall' => false];
        }

        return null;
    }
}
