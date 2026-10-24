<?php

namespace App\Services\Portal;

use App\Http\Controllers\ClientPortalController;
use App\Models\PortalBanner;
use App\Models\PortalCollection;
use App\Models\PortalSetting;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Vitrine do catálogo: monta banners, destaques e coleções para a página inicial
 * do portal. Produtos escolhidos são guardados pelo principal da família e
 * aparecem como no catálogo (a variação à venda); o que acabou some sozinho.
 */
class StorefrontService
{
    /** Fundos dos banners e coleções sem imagem. */
    public const COLORS = [
        'blue'   => ['Azul', 'linear-gradient(135deg,#3483fa 0%,#2968c8 100%)'],
        'violet' => ['Lilás', 'linear-gradient(135deg,#8b5cf6 0%,#6d28d9 100%)'],
        'pink'   => ['Rosa', 'linear-gradient(135deg,#ec4899 0%,#be185d 100%)'],
        'orange' => ['Laranja', 'linear-gradient(135deg,#ff7733 0%,#f23d4f 100%)'],
        'green'  => ['Verde', 'linear-gradient(135deg,#00a650 0%,#047857 100%)'],
        'gold'   => ['Dourado', 'linear-gradient(135deg,#d6a243 0%,#a16207 100%)'],
        'dark'   => ['Escuro', 'linear-gradient(135deg,#1f2937 0%,#0f172a 100%)'],
    ];

    /**
     * Modelos prontos de banner: tudo editável (textos, selo, cores, fotos, layout).
     * look = estilo do desenho, deco = ícone do fundo, pics = quantas fotos, src = de onde vêm as fotos
     * (manual, novos, vendidos, ofertas: as automáticas se atualizam sozinhas), date = data comemorativa
     * (CommercialCalendar) que já preenche o período. c1/c2 = degradê, acc = selo e botão, ink = letras.
     */
    public const TEMPLATES = [
        'oferta' => ['name' => 'Oferta relâmpago', 'group' => 'ofertas', 'look' => 'base', 'deco' => 'bolt', 'pics' => 1, 'src' => 'ofertas', 'date' => null, 'link' => 'offers',
            'badge' => 'ATÉ 40% OFF', 'title' => 'Oferta relâmpago', 'subtitle' => 'Só hoje, enquanto durar o estoque', 'button' => 'Aproveitar', 'c1' => '#ff7733', 'c2' => '#e11d48', 'acc' => '#fff7ed', 'ink' => 'light'],
        'ofertas3' => ['name' => 'Ofertas da semana', 'group' => 'ofertas', 'fmt' => 'leque', 'look' => 'clean', 'deco' => 'tag', 'pics' => 3, 'src' => 'ofertas', 'date' => null, 'link' => 'offers',
            'badge' => 'OFERTAS DA SEMANA', 'title' => 'Preços que você não vai ver de novo', 'subtitle' => 'Os produtos em promoção agora', 'button' => 'Ver ofertas', 'c1' => '#fff1f2', 'c2' => '#ffe4e6', 'acc' => '#e11d48', 'ink' => 'dark'],
        'liquida' => ['name' => 'Queima de estoque', 'group' => 'ofertas', 'fmt' => 'preco', 'look' => 'stripe', 'deco' => 'percent', 'pics' => 1, 'src' => 'ofertas', 'date' => null, 'link' => 'offers',
            'badge' => 'QUEIMA DE ESTOQUE', 'title' => 'Liquidação total', 'subtitle' => 'Últimas unidades com preço baixo', 'button' => 'Ver ofertas', 'c1' => '#dc2626', 'c2' => '#7f1d1d', 'acc' => '#fde047', 'ink' => 'light'],
        'leve3' => ['name' => 'Leve 3 pague 2', 'group' => 'ofertas', 'look' => 'ticket', 'deco' => 'gift', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'LEVE 3 PAGUE 2', 'title' => 'Monte o seu kit e economize', 'subtitle' => 'Válido nos produtos participantes', 'button' => 'Montar kit', 'c1' => '#059669', 'c2' => '#065f46', 'acc' => '#fef9c3', 'ink' => 'light'],
        'frete' => ['name' => 'Frete grátis', 'group' => 'ofertas', 'look' => 'bigicon', 'deco' => 'truck', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'FRETE GRÁTIS', 'title' => 'Entrega grátis no seu bairro', 'subtitle' => 'Em pedidos acima de R$ 150', 'button' => 'Comprar agora', 'c1' => '#10b981', 'c2' => '#047857', 'acc' => '#ecfdf5', 'ink' => 'light'],
        'cupom' => ['name' => 'Cupom de desconto', 'group' => 'ofertas', 'fmt' => 'preco', 'look' => 'ticket', 'deco' => 'tag', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'BEMVINDA10', 'title' => 'Ganhe 10% na primeira compra', 'subtitle' => 'Digite o cupom no carrinho', 'button' => 'Usar cupom', 'c1' => '#6366f1', 'c2' => '#7c3aed', 'acc' => '#ffffff', 'ink' => 'light'],
        'novidade' => ['name' => 'Lançamento', 'group' => 'produtos', 'fmt' => 'diagonal', 'look' => 'round', 'deco' => 'star', 'pics' => 1, 'src' => 'novos', 'date' => null, 'link' => 'news',
            'badge' => 'NOVIDADE', 'title' => 'Chegou a nova coleção', 'subtitle' => 'Conheça os lançamentos da semana', 'button' => 'Ver novidades', 'c1' => '#f5f3ff', 'c2' => '#ddd6fe', 'acc' => '#7c3aed', 'ink' => 'dark'],
        'chegaram' => ['name' => 'Acabaram de chegar', 'group' => 'produtos', 'fmt' => 'polaroid', 'look' => 'clean', 'deco' => 'spark', 'pics' => 3, 'src' => 'novos', 'date' => null, 'link' => 'news',
            'badge' => 'ACABOU DE CHEGAR', 'title' => 'Novidades fresquinhas', 'subtitle' => 'Os últimos produtos que entraram na loja', 'button' => 'Ver novidades', 'c1' => '#ffffff', 'c2' => '#f5f3ff', 'acc' => '#7c3aed', 'ink' => 'dark'],
        'vitrine' => ['name' => 'Mais vendidos', 'group' => 'produtos', 'fmt' => 'mosaico', 'look' => 'clean', 'deco' => null, 'pics' => 3, 'src' => 'vendidos', 'date' => null, 'link' => 'bestsellers',
            'badge' => 'MAIS VENDIDOS', 'title' => 'Os queridinhos da semana', 'subtitle' => 'Os produtos que mais saíram', 'button' => 'Ver todos', 'c1' => '#ffffff', 'c2' => '#f1f5f9', 'acc' => '#3483fa', 'ink' => 'dark'],
        'top' => ['name' => 'Campeão de vendas', 'group' => 'produtos', 'fmt' => 'holofote', 'look' => 'round', 'deco' => 'trophy', 'pics' => 1, 'src' => 'vendidos', 'date' => null, 'link' => 'bestsellers',
            'badge' => 'O MAIS VENDIDO', 'title' => 'Todo mundo está levando', 'subtitle' => 'O campeão de vendas da loja', 'button' => 'Quero o meu', 'c1' => '#fff7ed', 'c2' => '#fed7aa', 'acc' => '#c2410c', 'ink' => 'dark'],
        'destaque' => ['name' => 'Produto em destaque', 'group' => 'produtos', 'fmt' => 'meio', 'look' => 'tilt', 'deco' => 'star', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'product',
            'badge' => 'DESTAQUE', 'title' => 'Escolhido especialmente para você', 'subtitle' => 'Um produto que a loja recomenda', 'button' => 'Ver produto', 'c1' => '#0f766e', 'c2' => '#134e4a', 'acc' => '#ccfbf1', 'ink' => 'light'],
        'kit' => ['name' => 'Kits para presente', 'group' => 'produtos', 'look' => 'tilt', 'deco' => 'gift', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'KITS PRESENTE', 'title' => 'Presentes prontos e lindos', 'subtitle' => 'Embalados com carinho', 'button' => 'Ver kits', 'c1' => '#fdf4ff', 'c2' => '#f5d0fe', 'acc' => '#a21caf', 'ink' => 'dark'],
        'ano_novo' => ['name' => 'Ano Novo', 'group' => 'datas', 'look' => 'serif', 'deco' => 'spark', 'pics' => 1, 'src' => 'manual', 'date' => 'ano_novo', 'link' => 'offers',
            'badge' => 'FELIZ ANO NOVO', 'title' => 'Comece o ano renovada', 'subtitle' => 'Ofertas especiais de Réveillon', 'button' => 'Ver ofertas', 'c1' => '#0f172a', 'c2' => '#1e3a8a', 'acc' => '#fde68a', 'ink' => 'light'],
        'volta_aulas' => ['name' => 'Volta às aulas', 'group' => 'datas', 'look' => 'base', 'deco' => 'book', 'pics' => 1, 'src' => 'manual', 'date' => 'volta_aulas', 'link' => 'none',
            'badge' => 'VOLTA ÀS AULAS', 'title' => 'Tudo para começar o ano', 'subtitle' => 'Kits e presentes com desconto', 'button' => 'Ver produtos', 'c1' => '#fde047', 'c2' => '#f59e0b', 'acc' => '#1e293b', 'ink' => 'dark'],
        'carnaval' => ['name' => 'Carnaval', 'group' => 'datas', 'look' => 'tilt', 'deco' => 'spark', 'pics' => 1, 'src' => 'manual', 'date' => 'carnaval', 'link' => 'offers',
            'badge' => 'CARNAVAL', 'title' => 'Folia com desconto', 'subtitle' => 'Ofertas até a Quarta de Cinzas', 'button' => 'Aproveitar', 'c1' => '#a855f7', 'c2' => '#ec4899', 'acc' => '#fde047', 'ink' => 'light'],
        'mulher' => ['name' => 'Dia da Mulher', 'group' => 'datas', 'look' => 'round', 'deco' => 'flower', 'pics' => 1, 'src' => 'manual', 'date' => 'mulher', 'link' => 'none',
            'badge' => '8 DE MARÇO', 'title' => 'Dia da Mulher', 'subtitle' => 'Presentes para todas elas', 'button' => 'Ver presentes', 'c1' => '#fce7f3', 'c2' => '#f9a8d4', 'acc' => '#be185d', 'ink' => 'dark'],
        'consumidor' => ['name' => 'Dia do Consumidor', 'group' => 'datas', 'look' => 'base', 'deco' => 'percent', 'pics' => 1, 'src' => 'ofertas', 'date' => 'consumidor', 'link' => 'offers',
            'badge' => 'DIA DO CONSUMIDOR', 'title' => 'Semana do Consumidor', 'subtitle' => 'Descontos especiais por tempo limitado', 'button' => 'Ver ofertas', 'c1' => '#0ea5e9', 'c2' => '#2563eb', 'acc' => '#fef08a', 'ink' => 'light'],
        'pascoa' => ['name' => 'Páscoa', 'group' => 'datas', 'look' => 'round', 'deco' => 'egg', 'pics' => 1, 'src' => 'manual', 'date' => 'pascoa', 'link' => 'none',
            'badge' => 'PÁSCOA', 'title' => 'Páscoa cheia de mimos', 'subtitle' => 'Presentes doces para toda a família', 'button' => 'Ver presentes', 'c1' => '#fef3c7', 'c2' => '#fcd34d', 'acc' => '#7c2d12', 'ink' => 'dark'],
        'maes' => ['name' => 'Dia das Mães', 'group' => 'datas', 'fmt' => 'diagonal', 'look' => 'tilt', 'deco' => 'heart', 'pics' => 1, 'src' => 'manual', 'date' => 'maes', 'link' => 'none',
            'badge' => 'PRESENTES', 'title' => 'Dia das Mães', 'subtitle' => 'Kits lindos a partir de R$ 59,90', 'button' => 'Escolher presente', 'c1' => '#f9a8d4', 'c2' => '#db2777', 'acc' => '#ffffff', 'ink' => 'light'],
        'namorados' => ['name' => 'Dia dos Namorados', 'group' => 'datas', 'fmt' => 'revista', 'look' => 'tilt', 'deco' => 'heart', 'pics' => 1, 'src' => 'manual', 'date' => 'namorados', 'link' => 'none',
            'badge' => 'DIA DOS NAMORADOS', 'title' => 'Presente para quem você ama', 'subtitle' => 'Kits a dois com desconto', 'button' => 'Ver presentes', 'c1' => '#f43f5e', 'c2' => '#9f1239', 'acc' => '#fff1f2', 'ink' => 'light'],
        'junina' => ['name' => 'Festa Junina', 'group' => 'datas', 'look' => 'base', 'deco' => 'flag', 'pics' => 1, 'src' => 'manual', 'date' => 'junina', 'link' => 'offers',
            'badge' => 'ARRAIÁ', 'title' => 'Arraiá de ofertas', 'subtitle' => 'Promoções quentinhas o mês todo', 'button' => 'Aproveitar', 'c1' => '#f97316', 'c2' => '#b45309', 'acc' => '#fef3c7', 'ink' => 'light'],
        'pais' => ['name' => 'Dia dos Pais', 'group' => 'datas', 'look' => 'base', 'deco' => 'tie', 'pics' => 1, 'src' => 'manual', 'date' => 'pais', 'link' => 'none',
            'badge' => 'DIA DOS PAIS', 'title' => 'Presentes para o paizão', 'subtitle' => 'Perfumes e kits a partir de R$ 49,90', 'button' => 'Escolher presente', 'c1' => '#1e3a8a', 'c2' => '#0f172a', 'acc' => '#93c5fd', 'ink' => 'light'],
        'cliente' => ['name' => 'Dia do Cliente', 'group' => 'datas', 'look' => 'round', 'deco' => 'star', 'pics' => 1, 'src' => 'ofertas', 'date' => 'cliente', 'link' => 'offers',
            'badge' => 'DIA DO CLIENTE', 'title' => 'Obrigada por estar com a gente', 'subtitle' => 'Ofertas de agradecimento', 'button' => 'Ver ofertas', 'c1' => '#ecfeff', 'c2' => '#a5f3fc', 'acc' => '#0e7490', 'ink' => 'dark'],
        'criancas' => ['name' => 'Dia das Crianças', 'group' => 'datas', 'look' => 'tilt', 'deco' => 'balloon', 'pics' => 1, 'src' => 'manual', 'date' => 'criancas', 'link' => 'none',
            'badge' => 'DIA DAS CRIANÇAS', 'title' => 'Diversão com desconto', 'subtitle' => 'Presentes para a criançada', 'button' => 'Ver presentes', 'c1' => '#22d3ee', 'c2' => '#6366f1', 'acc' => '#fde047', 'ink' => 'light'],
        'black' => ['name' => 'Black Friday', 'group' => 'datas', 'fmt' => 'contagem', 'look' => 'stripe', 'deco' => 'star', 'pics' => 1, 'src' => 'ofertas', 'date' => 'black', 'link' => 'offers',
            'badge' => 'BLACK FRIDAY', 'title' => 'Os menores preços do ano', 'subtitle' => 'Descontos de verdade em toda a loja', 'button' => 'Ver ofertas', 'c1' => '#111111', 'c2' => '#27272a', 'acc' => '#facc15', 'ink' => 'light'],
        'natal' => ['name' => 'Natal', 'group' => 'datas', 'fmt' => 'holofote', 'look' => 'serif', 'deco' => 'snow', 'pics' => 1, 'src' => 'manual', 'date' => 'natal', 'link' => 'none',
            'badge' => 'FELIZ NATAL', 'title' => 'Presentes de Natal', 'subtitle' => 'Embrulhamos para presente sem custo', 'button' => 'Ver presentes', 'c1' => '#b91c1c', 'c2' => '#7f1d1d', 'acc' => '#fde68a', 'ink' => 'light'],
        // Só para lojas de móveis (kinds).
        'pronta' => ['name' => 'Pronta entrega', 'group' => 'produtos', 'kinds' => ['moveis'], 'fmt' => 'polaroid', 'look' => 'clean', 'deco' => 'truck', 'pics' => 3, 'src' => 'novos', 'date' => null, 'link' => 'none',
            'badge' => 'PRONTA ENTREGA', 'title' => 'Leve hoje para casa', 'subtitle' => 'Peças prontas, sem esperar a produção', 'button' => 'Ver peças', 'c1' => '#fafaf9', 'c2' => '#e7e5e4', 'acc' => '#78350f', 'ink' => 'dark'],
        'encomenda' => ['name' => 'Feito sob medida', 'group' => 'produtos', 'kinds' => ['moveis'], 'fmt' => 'meio', 'look' => 'bigicon', 'deco' => 'ruler', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'SOB MEDIDA', 'title' => 'Do tamanho do seu espaço', 'subtitle' => 'Escolha medidas, madeira e cor do ferro', 'button' => 'Fazer orçamento', 'c1' => '#78350f', 'c2' => '#451a03', 'acc' => '#fde68a', 'ink' => 'light'],
        'ambiente' => ['name' => 'Monte o ambiente', 'group' => 'produtos', 'kinds' => ['moveis'], 'fmt' => 'mosaico', 'look' => 'clean', 'deco' => 'lamp', 'pics' => 3, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'AMBIENTES', 'title' => 'Sala, varanda e cozinha completas', 'subtitle' => 'Peças que combinam entre si', 'button' => 'Ver ambientes', 'c1' => '#f5f5f4', 'c2' => '#d6d3d1', 'acc' => '#44403c', 'ink' => 'dark'],
        'artesanal' => ['name' => 'Ferro e madeira', 'group' => 'produtos', 'kinds' => ['moveis'], 'fmt' => 'revista', 'look' => 'serif', 'deco' => 'chair', 'pics' => 1, 'src' => 'vendidos', 'date' => null, 'link' => 'bestsellers',
            'badge' => 'FEITO À MÃO', 'title' => 'Ferro e madeira de verdade', 'subtitle' => 'Móveis artesanais que duram anos', 'button' => 'Conhecer', 'c1' => '#292524', 'c2' => '#0c0a09', 'acc' => '#d6a243', 'ink' => 'light'],
        'parcela' => ['name' => 'Parcelamento', 'group' => 'ofertas', 'kinds' => ['moveis'], 'fmt' => 'preco', 'look' => 'ticket', 'deco' => 'card', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'EM ATÉ 10X', 'title' => 'Parcele sem juros', 'subtitle' => 'No cartão, em todos os móveis', 'button' => 'Ver móveis', 'c1' => '#1e3a8a', 'c2' => '#172554', 'acc' => '#fef08a', 'ink' => 'light'],
        'montagem' => ['name' => 'Entrega e montagem', 'group' => 'ofertas', 'kinds' => ['moveis'], 'look' => 'bigicon', 'deco' => 'wrench', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'MONTAGEM GRÁTIS', 'title' => 'Entregamos e montamos', 'subtitle' => 'Na sua cidade, sem custo extra', 'button' => 'Saiba mais', 'c1' => '#a16207', 'c2' => '#713f12', 'acc' => '#fef3c7', 'ink' => 'light'],
        // Só para lojas de beleza.
        'fragrancia' => ['name' => 'Perfumes', 'group' => 'produtos', 'kinds' => ['beleza'], 'fmt' => 'holofote', 'look' => 'serif', 'deco' => 'drop', 'pics' => 1, 'src' => 'vendidos', 'date' => null, 'link' => 'bestsellers',
            'badge' => 'PERFUMARIA', 'title' => 'Encontre o seu perfume', 'subtitle' => 'Fragrâncias que marcam presença', 'button' => 'Ver perfumes', 'c1' => '#1c1917', 'c2' => '#57534e', 'acc' => '#fcd34d', 'ink' => 'light'],
        'pele' => ['name' => 'Cuidados com a pele', 'group' => 'produtos', 'kinds' => ['beleza'], 'look' => 'round', 'deco' => 'drop', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'SKINCARE', 'title' => 'Pele bonita todo dia', 'subtitle' => 'Hidratantes, séruns e protetores', 'button' => 'Ver cuidados', 'c1' => '#ecfdf5', 'c2' => '#a7f3d0', 'acc' => '#047857', 'ink' => 'dark'],
        'make' => ['name' => 'Maquiagem', 'group' => 'produtos', 'kinds' => ['beleza'], 'fmt' => 'leque', 'look' => 'tilt', 'deco' => 'heart', 'pics' => 3, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'MAKE', 'title' => 'Make para todos os momentos', 'subtitle' => 'Batons, bases e sombras', 'button' => 'Ver maquiagem', 'c1' => '#fb7185', 'c2' => '#be123c', 'acc' => '#fff1f2', 'ink' => 'light'],
        'kitperfume' => ['name' => 'Kit perfume + hidratante', 'group' => 'ofertas', 'kinds' => ['beleza'], 'look' => 'ticket', 'deco' => 'gift', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'KIT ESPECIAL', 'title' => 'Perfume + hidratante com desconto', 'subtitle' => 'Combinação perfeita para presentear', 'button' => 'Montar kit', 'c1' => '#7c3aed', 'c2' => '#4c1d95', 'acc' => '#fde68a', 'ink' => 'light'],
        'brinde' => ['name' => 'Ganhe um brinde', 'group' => 'ofertas', 'kinds' => ['beleza'], 'look' => 'round', 'deco' => 'gift', 'pics' => 1, 'src' => 'manual', 'date' => null, 'link' => 'none',
            'badge' => 'BRINDE', 'title' => 'Compre e ganhe um mimo', 'subtitle' => 'Em compras acima de R$ 100', 'button' => 'Comprar', 'c1' => '#fdf2f8', 'c2' => '#fbcfe8', 'acc' => '#9d174d', 'ink' => 'dark'],
    ];

    /** Modelos que não combinam com o tipo de loja. */
    public const HIDDEN_TEMPLATES = [
        'moveis' => ['volta_aulas', 'carnaval', 'mulher', 'pascoa', 'junina', 'criancas', 'leve3', 'kit'],
    ];

    /** Textos e cores dos modelos ajustados ao tipo de loja (o resto vem do modelo). */
    public const KIND_TEXTS = [
        'moveis' => [
            'oferta'     => ['subtitle' => 'Móveis com preço de fábrica, só hoje'],
            'ofertas3'   => ['title' => 'Móveis com preço especial', 'subtitle' => 'As peças em promoção agora'],
            'liquida'    => ['badge' => 'SALDÃO', 'title' => 'Saldão de mostruário', 'subtitle' => 'Peças de exposição com desconto'],
            'frete'      => ['title' => 'Entrega grátis na cidade', 'subtitle' => 'Para móveis acima de R$ 500'],
            'cupom'      => ['badge' => 'MOVEIS10', 'title' => 'Ganhe 10% no primeiro móvel'],
            'novidade'   => ['title' => 'Novos modelos na loja', 'subtitle' => 'Mesas, aparadores e estantes', 'button' => 'Ver modelos', 'c1' => '#fafaf9', 'c2' => '#e7e5e4', 'acc' => '#78350f'],
            'chegaram'   => ['badge' => 'NOVOS MODELOS', 'title' => 'Acabaram de sair da oficina', 'subtitle' => 'As últimas peças que fizemos', 'acc' => '#78350f'],
            'vitrine'    => ['title' => 'Os móveis mais pedidos', 'subtitle' => 'As peças que mais saíram', 'acc' => '#78350f'],
            'top'        => ['title' => 'A peça que todo mundo quer', 'subtitle' => 'O móvel mais vendido da loja', 'button' => 'Quero a minha'],
            'destaque'   => ['title' => 'Peça em destaque', 'subtitle' => 'Ferro e madeira, feita para durar', 'c1' => '#44403c', 'c2' => '#1c1917', 'acc' => '#d6a243'],
            'ano_novo'   => ['title' => 'Ano novo, casa nova', 'subtitle' => 'Renove os ambientes com desconto'],
            'consumidor' => ['subtitle' => 'Móveis com desconto por tempo limitado'],
            'maes'       => ['badge' => 'DIA DAS MÃES', 'title' => 'Uma casa mais bonita para ela', 'subtitle' => 'Aparadores, mesas e cantinhos do café', 'button' => 'Ver presentes', 'c1' => '#e8c39e', 'c2' => '#a0522d', 'acc' => '#fff7ed'],
            'namorados'  => ['title' => 'O cantinho de vocês', 'subtitle' => 'Mesas para dois e bancos de varanda', 'c1' => '#9f1239', 'c2' => '#4c0519'],
            'pais'       => ['title' => 'O canto dele em casa', 'subtitle' => 'Bares, nichos e churrasqueiras', 'c1' => '#44403c', 'c2' => '#1c1917', 'acc' => '#d6a243'],
            'cliente'    => ['title' => 'Obrigado por confiar no nosso trabalho', 'subtitle' => 'Condições especiais em toda a loja'],
            'black'      => ['title' => 'Black Friday dos móveis', 'subtitle' => 'Os menores preços do ano em ferro e madeira'],
            'natal'      => ['title' => 'Natal em casa', 'subtitle' => 'Mesa posta e casa pronta para a família', 'button' => 'Ver móveis', 'c1' => '#14532d', 'c2' => '#052e16'],
        ],
    ];

    /** Um modelo com os textos do tipo de loja. */
    public static function template(string $key, ?string $kind = null): ?array
    {
        $t = self::TEMPLATES[$key] ?? null;

        return $t ? array_merge($t, self::KIND_TEXTS[$kind][$key] ?? []) : null;
    }

    /** Todos os modelos que servem para o tipo de loja. */
    public static function templates(?string $kind = null): Collection
    {
        $hidden = self::HIDDEN_TEMPLATES[$kind] ?? [];

        return collect(self::TEMPLATES)
            ->filter(fn ($t, $k) => ! in_array($k, $hidden, true) && (empty($t['kinds']) || in_array($kind, $t['kinds'], true)))
            ->map(fn ($t, $k) => self::template($k, $kind));
    }

    /** Grupos da galeria de modelos. */
    public const TEMPLATE_GROUPS = [
        'datas'    => ['Datas comemorativas', 'bi-calendar-heart'],
        'produtos' => ['Produtos', 'bi-box-seam'],
        'ofertas'  => ['Ofertas e avisos', 'bi-percent'],
    ];

    /** Modelos de um grupo; os de data na ordem em que as datas chegam. */
    public static function templatesIn(string $group, ?string $kind = null): Collection
    {
        $list = self::templates($kind)->where('group', $group);
        if ($group === 'datas') {
            $list = $list->sortBy(fn ($t) => \App\Services\Promotions\CommercialCalendar::next($t['date'])['start']->timestamp);
        }

        return $list;
    }

    /** De onde vêm as fotos do modelo. */
    public const PHOTO_SOURCES = [
        'manual'   => ['Eu escolho', 'bi-hand-index'],
        'novos'    => ['Produtos novos', 'bi-stars'],
        'vendidos' => ['Mais vendidos', 'bi-trophy'],
        'ofertas'  => ['Em oferta', 'bi-percent'],
    ];

    /** Animações do banner: as que trocam as fotos usam até 3 fotos por espaço. */
    public const ANIMATIONS = [
        'none'     => ['Parado', 'bi-pause-circle', false],
        'fotos'    => ['Troca as fotos', 'bi-arrow-repeat', true],
        'deslizar' => ['Fotos deslizando', 'bi-arrow-left-right', true],
        'zoom'     => ['Zoom nas fotos', 'bi-zoom-in', true],
        'entrada'  => ['Textos entrando', 'bi-type', false],
        'vivo'     => ['Tudo animado', 'bi-stars', true],
    ];

    /**
     * Formatos do banner: mudam o desenho de verdade (onde fica a foto, quantas, o tamanho do texto),
     * não só as cores. pics = quantas fotos o formato usa (null = as do modelo).
     * Completos no Topo; na faixa e na lateral os formatos pesados ficam mais simples.
     */
    public const FORMATS = [
        'padrao'   => ['Clássico', 'bi-layout-text-sidebar-reverse', null],
        'meio'     => ['Meio a meio', 'bi-layout-split', 1],
        'diagonal' => ['Diagonal', 'bi-slash-square', 1],
        'holofote' => ['Holofote', 'bi-brightness-high', 1],
        'revista'  => ['Revista', 'bi-type-bold', 1],
        'preco'    => ['Preço grande', 'bi-tag', 1],
        'contagem' => ['Contagem', 'bi-stopwatch', 1],
        'mosaico'  => ['Mosaico', 'bi-grid-1x2', 4],
        'leque'    => ['Leque', 'bi-files', 3],
        'polaroid' => ['Polaroides', 'bi-images', 3],
    ];

    /** Formato válido ('padrao' quando não tem). */
    public static function format(?string $fmt): string
    {
        return array_key_exists((string) $fmt, self::FORMATS) ? $fmt : 'padrao';
    }

    /** Quantas fotos aparecem ao mesmo tempo: as do formato, ou as do modelo. */
    public static function picsFor(?string $tpl, ?string $fmt = null): int
    {
        return self::FORMATS[self::format($fmt)][2] ?? (self::TEMPLATES[$tpl ?? '']['pics'] ?? 1);
    }

    /** Quantas fotos o banner usa: as que aparecem, ou 3 por espaço quando a animação troca as fotos. */
    public static function photoCap(?string $tpl, ?string $anim, ?string $fmt = null): int
    {
        $pics = self::picsFor($tpl, $fmt);

        return (self::ANIMATIONS[$anim ?? 'none'][2] ?? false) ? $pics * 3 : $pics;
    }

    /** Layouts do modelo. */
    public const LAYOUTS = [
        'esq'    => ['Texto à esquerda', 'bi-layout-text-sidebar-reverse'],
        'dir'    => ['Foto à esquerda', 'bi-layout-text-sidebar'],
        'centro' => ['Só texto, no centro', 'bi-text-center'],
    ];

    /** Combinações de cores para os modelos: fundo início, fundo fim, selo/botão, letras. */
    public const PALETTES = [
        ['#ff7733', '#e11d48', '#fff7ed', 'light'],
        ['#3483fa', '#2968c8', '#ffffff', 'light'],
        ['#ec4899', '#be185d', '#fff1f2', 'light'],
        ['#8b5cf6', '#6d28d9', '#fde68a', 'light'],
        ['#10b981', '#047857', '#ecfdf5', 'light'],
        ['#111111', '#27272a', '#facc15', 'light'],
        ['#b91c1c', '#7f1d1d', '#fde68a', 'light'],
        ['#fdf2f8', '#fbcfe8', '#db2777', 'dark'],
        ['#fffbeb', '#fde68a', '#b45309', 'dark'],
        ['#f0f9ff', '#bae6fd', '#0369a1', 'dark'],
        ['#ffffff', '#f1f5f9', '#3483fa', 'dark'],
    ];

    public const LINK_TYPES = [
        'none'       => 'Sem link',
        'offers'     => 'Ofertas',
        'news'       => 'Novidades',
        'bestsellers'=> 'Mais vendidos',
        'collection' => 'Uma coleção',
        'category'   => 'Uma categoria',
        'product'    => 'Um produto',
        'url'        => 'Outro endereço',
    ];

    public static function gradient(?string $color): string
    {
        return (self::COLORS[$color] ?? self::COLORS['blue'])[1];
    }

    public static function ready(): bool
    {
        static $ok;

        return $ok ??= Schema::hasTable('portal_banners') && Schema::hasTable('portal_settings');
    }

    /** Banners valendo hoje, por lugar. */
    public static function banners(int $ownerId): Collection
    {
        if (! self::ready()) {
            return collect();
        }

        return PortalBanner::where('user_id', $ownerId)->live()->orderBy('sort')->orderBy('id')->get()->groupBy('placement');
    }

    /** Endereço para onde o banner leva (null = sem link). */
    public static function href(PortalBanner $b, int $ownerId): ?string
    {
        $v = trim((string) $b->link_value);

        return match ($b->link_type) {
            'offers'     => route('portal.catalog', ['userId' => $ownerId, 'ofertas' => 1]),
            'news'       => route('portal.catalog', ['userId' => $ownerId, 'ordem' => 'novos']),
            'bestsellers'=> route('portal.catalog', ['userId' => $ownerId, 'ordem' => 'vendidos']),
            'collection' => $v !== '' ? route('portal.catalog', ['userId' => $ownerId, 'colecao' => $v]) : null,
            'category'   => $v !== '' ? route('portal.catalog', ['userId' => $ownerId, 'category' => $v]) : null,
            'product'    => (int) $v > 0 ? route('portal.product', ['userId' => $ownerId, 'product' => (int) $v]) : null,
            'url'        => preg_match('~^https?://~i', $v) ? $v : null,
            default      => null,
        };
    }

    /** Link de fora da loja abre em outra aba. */
    public static function external(?string $href): bool
    {
        return $href && ! str_starts_with($href, url('/'));
    }

    public static function settings(int $ownerId): ?PortalSetting
    {
        return self::ready() ? PortalSetting::where('user_id', $ownerId)->first() : null;
    }

    /**
     * Produtos à venda das famílias escolhidas, na ordem escolhida
     * (uma variação por família, a mesma que o catálogo mostra).
     */
    public static function productsFor(int $ownerId, array $roots, int $limit = 24): Collection
    {
        $roots = array_values(array_unique(array_filter(array_map('intval', $roots))));
        if (! $roots) {
            return collect();
        }
        $found = Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $ownerId)
            ->availableForSale()
            ->whereIn('status', ['active', 'ativo'])
            ->topOfFamily()
            ->where(fn ($q) => $q->whereIn('parent_id', $roots)->orWhere(fn ($w) => $w->whereNull('parent_id')->whereIn('id', $roots)))
            ->with(['category', 'activePromotion', 'images'])
            ->get()
            ->keyBy(fn ($p) => (int) ($p->parent_id ?: $p->id));

        return collect($roots)->map(fn ($r) => $found[$r] ?? null)->filter()->take($limit)->values();
    }

    /**
     * Produtos para as fotos automáticas dos modelos: os mais novos, os mais vendidos
     * (90 dias) ou os em promoção. Só os que estão à venda e têm foto; sem nenhum
     * vendido ou em oferta, ficam os mais novos.
     */
    public static function autoProducts(int $ownerId, string $src, int $n = 3): Collection
    {
        static $memo = [];
        $key = "$ownerId:$src:$n";
        if (isset($memo[$key])) {
            return $memo[$key];
        }
        $base = fn () => Product::withoutGlobalScope('team_visibility')
            ->where('user_id', $ownerId)
            ->availableForSale()
            ->whereIn('status', ['active', 'ativo'])
            ->topOfFamily()
            ->whereNotNull('image')->where('image', '!=', '')->where('image', '!=', 'product-placeholder.png');
        $since = now()->subDays(90);
        $list = match ($src) {
            'vendidos' => $base()->withSum(['saleItems as sold_qty' => fn ($q) => $q->where('created_at', '>=', $since)], 'quantity')
                ->whereHas('saleItems', fn ($q) => $q->where('created_at', '>=', $since))
                ->orderByDesc('sold_qty')->limit($n)->get(),
            'ofertas'  => $base()->whereHas('promotions', fn ($q) => $q->where('status', \App\Models\Promotion::ATIVA))
                ->orderByDesc('updated_at')->limit($n)->get(),
            default    => collect(),
        };
        if ($list->isEmpty()) {
            $list = $base()->orderByDesc('id')->limit($n)->get();
        }

        return $memo[$key] = $list->values();
    }

    /** Como a coleção escolhe os produtos: manual ou uma regra que se atualiza sozinha. */
    public const COLLECTION_RULES = [
        'manual'    => ['Eu escolho', 'bi-hand-index', 'Você escolhe os produtos, na ordem que quiser.'],
        'categoria' => ['Por categoria', 'bi-tags', 'Todos os produtos das categorias escolhidas.'],
        'ofertas'   => ['Em promoção', 'bi-percent', 'Os produtos com promoção ativa agora.'],
        'novos'     => ['Mais novos', 'bi-stars', 'Os últimos produtos cadastrados.'],
        'vendidos'  => ['Mais vendidos', 'bi-trophy', 'Os que mais venderam nos últimos 90 dias.'],
        'preco'     => ['Faixa de preço', 'bi-cash-coin', 'Produtos entre o menor e o maior preço que você colocar.'],
        'busca'     => ['Por palavra', 'bi-search', 'Produtos com a palavra no nome (ex.: perfume, mesa).'],
        'pronta'    => ['Pronta entrega', 'bi-truck', 'Só o que tem em estoque agora.'],
        'encomenda' => ['Sob encomenda', 'bi-rulers', 'Só os produtos feitos sob encomenda.'],
    ];

    /** Ideias de coleção para começar, por tipo de loja: [nome, frase, regra, dados, modelo da capa]. */
    public const COLLECTION_IDEAS = [
        'beleza' => [
            ['Perfumes', 'Fragrâncias para todos os estilos', 'busca', ['term' => 'perfume'], 'fragrancia'],
            ['Kits para presente', 'Presentes prontos e lindos', 'busca', ['term' => 'kit'], 'kit'],
            ['Mais vendidos', 'Os queridinhos das clientes', 'vendidos', [], 'vitrine'],
            ['Novidades', 'Acabaram de chegar', 'novos', [], 'chegaram'],
            ['Em promoção', 'Preços especiais por pouco tempo', 'ofertas', [], 'ofertas3'],
            ['Até R$ 50', 'Mimos que cabem no bolso', 'preco', ['max' => 50], 'cupom'],
        ],
        'moveis' => [
            ['Mesas', 'Mesas de jantar, de centro e bistrô', 'busca', ['term' => 'mesa'], 'artesanal'],
            ['Aparadores e buffets', 'Para a sala e o hall de entrada', 'busca', ['term' => 'aparador'], 'ambiente'],
            ['Pronta entrega', 'Leve hoje, sem esperar', 'pronta', [], 'pronta'],
            ['Sob medida', 'Feitos no tamanho do seu espaço', 'encomenda', [], 'encomenda'],
            ['Mais vendidos', 'As peças mais pedidas', 'vendidos', [], 'vitrine'],
            ['Novos modelos', 'Acabaram de sair da oficina', 'novos', [], 'chegaram'],
        ],
        'geral' => [
            ['Mais vendidos', 'Os que mais saíram', 'vendidos', [], 'vitrine'],
            ['Novidades', 'Acabaram de chegar', 'novos', [], 'chegaram'],
            ['Em promoção', 'Preços especiais por pouco tempo', 'ofertas', [], 'ofertas3'],
            ['Pronta entrega', 'Leve hoje', 'pronta', [], 'pronta'],
        ],
    ];

    /** Principais das famílias que entram numa coleção automática, já na ordem da regra. */
    public static function ruleRoots(int $ownerId, string $rule, array $data = [], int $limit = 60): array
    {
        if ($rule === 'manual' || ! isset(self::COLLECTION_RULES[$rule])) {
            return [];
        }
        $q = Product::withoutGlobalScope('team_visibility')
            ->where('products.user_id', $ownerId)
            ->availableForSale()
            ->whereIn('status', ['active', 'ativo'])
            ->topOfFamily();
        $since = now()->subDays(90);
        match ($rule) {
            'categoria' => $q->whereIn('category_id', array_map('intval', (array) ($data['cats'] ?? [])) ?: [0])->orderBy('name'),
            'ofertas'   => $q->whereHas('promotions', fn ($p) => $p->where('status', \App\Models\Promotion::ATIVA))->orderByDesc('updated_at'),
            'novos'     => $q->orderByDesc('id'),
            'vendidos'  => $q->withSum(['saleItems as sold_qty' => fn ($w) => $w->where('created_at', '>=', $since)], 'quantity')
                ->whereHas('saleItems', fn ($w) => $w->where('created_at', '>=', $since))->orderByDesc('sold_qty'),
            'preco'     => $q->when(($data['min'] ?? '') !== '' && $data['min'] !== null, fn ($w) => $w->where('price_sale', '>=', (float) $data['min']))
                ->when(($data['max'] ?? '') !== '' && $data['max'] !== null, fn ($w) => $w->where('price_sale', '<=', (float) $data['max']))->orderBy('price_sale'),
            'busca'     => $q->where(function ($w) use ($data) {
                $words = preg_split('/\s+/', trim((string) ($data['term'] ?? ''))) ?: [];
                $words = array_filter($words, fn ($x) => mb_strlen($x) >= 2) ?: ['§'];
                foreach ($words as $word) {
                    $w->where('name', 'like', '%' . $word . '%');
                }
            })->orderBy('name'),
            'pronta'    => $q->where('stock_quantity', '>', 0)->orderByDesc('id'),
            'encomenda' => $q->where('made_to_order', true)->orderBy('name'),
        };

        return $q->limit($limit * 2)->get(['products.id', 'products.parent_id'])
            ->map(fn ($p) => (int) ($p->parent_id ?: $p->id))->unique()->take($limit)->values()->all();
    }

    /** Fotos da capa da coleção: dos próprios produtos dela. */
    public static function collectionPhotos(PortalCollection $c, int $ownerId, int $n): array
    {
        $items = $c->relationLoaded('items') ? $c->items : self::productsFor($ownerId, $c->rootIds($n * 2), $n * 2);

        return $items->map(fn ($p) => $p->image && $p->image !== 'product-placeholder.png' ? $p->image_url : null)
            ->filter()->unique()->take($n)->values()->all();
    }

    /** Capas das coleções de categoria: um modelo diferente para cada uma, na ordem. */
    public const CATEGORY_COVERS = ['destaque', 'vitrine', 'artesanal', 'top', 'fragrancia', 'chegaram', 'ambiente', 'make', 'novidade', 'pele', 'pronta', 'kit', 'ofertas3', 'encomenda'];

    /** Produtos à venda (principal da família) por categoria. */
    public static function categoryCounts(int $ownerId): Collection
    {
        return Product::withoutGlobalScope('team_visibility')
            ->where('products.user_id', $ownerId)->availableForSale()->whereIn('status', ['active', 'ativo'])->topOfFamily()
            ->whereNotNull('category_id')
            ->selectRaw('category_id, count(*) as n')->groupBy('category_id')
            ->pluck('n', 'category_id')->map(fn ($v) => (int) $v);
    }

    /** Categorias com produto à venda que ainda não têm a coleção delas. */
    public static function categoriesWithoutCollection(int $ownerId): Collection
    {
        if (! Schema::hasColumn('portal_collections', 'category_id')) {
            return collect();
        }
        $counts = self::categoryCounts($ownerId);
        $have = PortalCollection::where('user_id', $ownerId)->whereNotNull('category_id')->pluck('category_id')->map(fn ($v) => (int) $v)->all();

        return \App\Models\Category::where('user_id', $ownerId)->whereIn('id_category', $counts->keys())->whereNotIn('id_category', $have)
            ->orderBy('name')->get(['id_category', 'name'])
            ->map(fn ($c) => ['id' => (int) $c->id_category, 'name' => trim((string) $c->name), 'count' => (int) ($counts[$c->id_category] ?? 0)])
            ->filter(fn ($c) => $c['name'] !== '')->values();
    }

    /**
     * Cria uma coleção automática (regra "categoria") para cada categoria que ainda não tem,
     * cada uma com capa de um modelo diferente. As 4 maiores vão para a página inicial.
     */
    public static function createCategoryCollections(int $ownerId, ?string $kind = null): int
    {
        $cats = self::categoriesWithoutCollection($ownerId);
        if ($cats->isEmpty()) {
            return 0;
        }
        $kind ??= \App\Services\Promotions\StoreKind::for($ownerId);
        $avail = self::templates($kind);
        $covers = array_values(array_filter(self::CATEGORY_COVERS, fn ($k) => $avail->has($k))) ?: ['destaque'];
        $used = PortalCollection::where('user_id', $ownerId)->whereNotNull('category_id')->count();
        $onHome = PortalCollection::where('user_id', $ownerId)->where('show_on_home', true)->count();
        $big = $cats->sortByDesc('count')->take(max(0, 4 - $onHome))->pluck('id')->all();
        $sort = (int) PortalCollection::where('user_id', $ownerId)->max('sort');
        foreach ($cats as $i => $cat) {
            $tpl = $covers[($used + $i) % count($covers)];
            $t = self::template($tpl, $kind);
            $c = PortalCollection::create([
                'user_id'      => $ownerId,
                'name'         => mb_substr($cat['name'], 0, 80),
                'slug'         => self::freeSlug($ownerId, $cat['name']),
                'description'  => 'Toda a linha de ' . mb_strtolower($cat['name']) . ' em um só lugar',
                'color'        => array_keys(self::COLORS)[($used + $i) % count(self::COLORS)],
                'active'       => true,
                'show_on_home' => in_array($cat['id'], $big, true),
                'sort'         => ++$sort,
                'rule'         => 'categoria',
                'rule_data'    => ['cats' => [$cat['id']], 'limit' => 12],
                'category_id'  => $cat['id'],
                'design'       => ['tpl' => $tpl, 'badge' => 'COLEÇÃO', 'c1' => $t['c1'], 'c2' => $t['c2'], 'acc' => $t['acc'], 'ink' => $t['ink'],
                    'lay' => ($used + $i) % 2 ? 'dir' : 'esq', 'anim' => 'fotos'],
            ]);
            $c->syncProducts([]);
        }

        return $cats->count();
    }

    public static function freeSlug(int $ownerId, string $name, ?int $ignore = null): string
    {
        $base = \Illuminate\Support\Str::slug($name) ?: 'colecao';
        $slug = $base;
        $n = 2;
        while (PortalCollection::where('user_id', $ownerId)->where('slug', $slug)->when($ignore, fn ($q) => $q->where('id', '!=', $ignore))->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    /** Coleções ligadas, com os produtos à venda (as vazias ficam de fora). */
    public static function collections(int $ownerId, bool $homeOnly = true): Collection
    {
        if (! Schema::hasTable('portal_collections')) {
            return collect();
        }

        return PortalCollection::where('user_id', $ownerId)->where('active', true)
            ->when($homeOnly, fn ($q) => $q->where('show_on_home', true))
            ->orderBy('sort')->orderBy('id')->get()
            ->map(function (PortalCollection $c) use ($ownerId) {
                $c->setRelation('items', self::productsFor($ownerId, $c->rootIds(30), (int) (($c->rule_data['limit'] ?? null) ?: 15)));

                return $c;
            })
            ->filter(fn ($c) => $c->items->isNotEmpty())
            ->values();
    }

    public static function collectionBySlug(int $ownerId, string $slug): ?PortalCollection
    {
        if ($slug === '' || ! Schema::hasTable('portal_collections')) {
            return null;
        }

        return PortalCollection::where('user_id', $ownerId)->where('active', true)->where('slug', $slug)->first();
    }

    /** Principal da família de cada produto (o que a vitrine guarda). */
    public static function rootsOf(int $ownerId, array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (! $ids) {
            return [];
        }
        $map = Product::withoutGlobalScope('team_visibility')->where('user_id', $ownerId)->whereIn('id', $ids)
            ->get(['id', 'parent_id'])->mapWithKeys(fn ($p) => [$p->id => (int) ($p->parent_id ?: $p->id)]);

        return array_values(array_unique(array_filter(array_map(fn ($id) => $map[$id] ?? null, $ids))));
    }

    /** Dados do card (o mesmo do catálogo) para cada produto. */
    public static function cards(Collection $products): array
    {
        $out = [];
        foreach ($products as $p) {
            $out[$p->id] = ClientPortalController::cardInfo($p);
        }

        return $out;
    }
}
