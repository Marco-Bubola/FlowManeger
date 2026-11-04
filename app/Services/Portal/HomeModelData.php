<?php

namespace App\Services\Portal;

/**
 * Dados das páginas de apresentação da página inicial (Vitrine › Página inicial › Modelo: Editorial e Elegante).
 * Recebe as variáveis do catalog.blade e devolve as $hm* usadas nos partials portal/partials/home-*.
 * Só usa dados reais da loja (produtos, categorias, banner do topo, avaliações e a página "Sobre").
 */
class HomeModelData
{
    public static function build(array $v): array
    {
        $owner = $v['owner'];
        $products = $v['products'];
        $all = $v['all'];
        $featured = $v['featured'];
        $offers = $v['offers'];
        $categories = $v['categories'];
        $countByCategory = $v['countByCategory'] ?? [];
        $heroBanners = $v['heroBanners'];
        $notice = $v['notice'] ?? null;
        $stockMap = $v['stockMap'];
        $hmId = \App\Services\Portal\StoreIdentity::for((int) $owner);
        $hmWithImg = fn ($c) => collect($c)->filter(fn ($d) => ! empty($d['img']))->values();
        // Novidades: os mais recentes da primeira página
        $hmNew = $hmWithImg(collect($products->items())->sortByDesc('created_at')->map(fn ($p) => $all[$p->id] ?? null)->filter())->take(8);
        // Mais amados: os destaques da Vitrine; sem eles, as ofertas; sem elas, as novidades
        $hmLoved = $hmWithImg($featured->map(fn ($p) => $all[$p->id] ?? null)->filter());
        if ($hmLoved->count() < 3) {
            $hmLoved = $hmLoved->merge($hmWithImg($offers->map(fn ($p) => $all[$p->id] ?? null)->filter()))->unique('id')->values();
        }
        if ($hmLoved->count() < 3) {
            $hmLoved = $hmLoved->merge($hmWithImg(collect($products->items())->map(fn ($p) => $all[$p->id] ?? null)->filter()))->unique('id')->values();
        }
        $hmLoved = $hmLoved->take(6);
        // Topo: até 4 produtos com foto girando
        $hmSpin = $hmLoved->merge($hmNew)->unique('id')->take(4)->values();
        // Categorias com a foto de um produto delas
        $hmPool = $hmWithImg($all->values());
        $hmCats = $categories->map(function ($c) use ($hmPool, $owner, $countByCategory) {
        $d = $hmPool->first(fn ($d) => ($d['cat'] ?? null) === $c->name);
        return ['name' => $c->name, 'img' => $d['img'] ?? null, 'n' => $countByCategory[$c->getKey()] ?? 0,
        'url' => route('portal.catalog', ['userId' => $owner, 'category' => $c->getKey()])];
        })->values();
        // Banner do topo da Vitrine (título, texto, foto), quando tiver
        $hmBanner = $heroBanners->first();
        $hmBannerImg = $hmBanner?->imageUrl();
        $hmHref = $hmBanner ? (\App\Services\Portal\StorefrontService::href($hmBanner, (int) $owner) ?: '#todos') : '#todos';
        $hmTitle = trim((string) ($hmBanner?->title ?: ($hmId['tagline'] ?: $hmId['name'])));
        $hmSub = trim((string) ($hmBanner?->subtitle ?: ($hmBanner ? '' : ($hmId['tagline'] ? $hmId['name'] : ''))));
        $hmBtn = $hmBanner?->button_text ?: 'Ver produtos';
        // Avaliações aprovadas com comentário
        $hmReviews = collect();
        $hmRate = null;
        if (\App\Services\Portal\ReviewService::ready()) {
            $hmQ = \App\Models\PortalReview::where('user_id', (int) $owner)->where('hidden', false);
            $hmRate = (clone $hmQ)->selectRaw('AVG(rating) a, COUNT(*) n')->first();
            $hmRate = $hmRate && $hmRate->n ? ['avg' => round((float) $hmRate->a, 1), 'n' => (int) $hmRate->n] : null;
            $hmReviews = (clone $hmQ)->whereNotNull('comment')->where('comment', '!=', '')->where('rating', '>=', 4)->latest()->take(6)->get();
        }
        $hmAbout = trim((string) ($hmId['pages']['sobre'] ?? ''));
        $hmAboutShort = $hmAbout !== '' ? \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', $hmAbout), 260) : '';
        $hmNewest = $hmNew->first();
        $hmMoney = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
        $hmStrip = collect([$notice?->notice_text])->filter()
        ->merge($hmCats->pluck('name')->take(4)->map(fn ($n) => $n))
        ->push($stockMap->count() . ' produtos disponíveis')->take(6)->values();

        return compact('hmId', 'hmNew', 'hmLoved', 'hmSpin', 'hmCats', 'hmBanner', 'hmBannerImg', 'hmHref', 'hmTitle', 'hmSub', 'hmBtn', 'hmReviews', 'hmRate', 'hmAbout', 'hmAboutShort', 'hmNewest', 'hmMoney', 'hmStrip');
    }
}
