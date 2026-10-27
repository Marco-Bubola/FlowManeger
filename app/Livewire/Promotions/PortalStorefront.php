<?php

namespace App\Livewire\Promotions;

use App\Models\Category;
use App\Models\PortalBanner;
use App\Models\PortalCollection;
use App\Models\PortalSetting;
use App\Models\Product;
use App\Services\Portal\HomeLayout;
use App\Services\Portal\StorefrontService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Vitrine do catálogo: banners (topo, faixa e laterais), produtos em destaque,
 * coleções com banner e o aviso no topo. Tudo aparece na página inicial do portal.
 */
class PortalStorefront extends Component
{
    use WithFileUploads;

    public string $tab = 'geral';

    // Página inicial (montagem): ordem dos blocos, escondidos, cores [topo 1, topo 2, principal], cantos e tema
    public array $home_order = [];
    public array $home_off = [];
    public array $home_colors = [];
    /** Cores das áreas: [fundo da página, cards e seções] */
    public array $home_surface = [];
    public string $home_style = 'padrao';
    public array $home_look = [];
    /** Vitrine › Automações (HomeLayout::AUTO_DEFAULT). */
    public array $auto = [];
    public string $home_theme = 'padrao';
    public string $pendingTheme = '';
    public bool $withBanner = true;
    public bool $withNotice = true;
    public int $previewKey = 0;

    /** Grupo aberto na fileira de modelos prontos. */
    public string $galleryGroup = 'datas';
    public string $kind = 'beleza';

    // Destaques
    public string $featured_title = '';
    public array $featuredIds = [];

    // Identidade da loja (logo, nome, contatos, fonte) e as páginas Sobre / Trocas / Entrega
    public array $identity = [];
    public array $pages = [];
    public $logoUpload = null;

    // Aviso no topo
    public string $notice_text = '';
    public string $notice_link = '';
    public bool $notice_active = false;

    // Escolher produtos (destaques, coleção ou link do banner)
    public string $pickFor = '';
    public string $pickSearch = '';
    /** Filtro da janela: todos, vendidos, ofertas, novos, estoque */
    public string $pickFilter = 'todos';
    public int $pickCat = 0;

    protected $queryString = ['tab' => ['except' => 'geral']];

    public function mount()
    {
        // Link antigo "?modelo=": o banner agora tem página própria.
        if (($m = (string) request()->query('modelo')) && isset(StorefrontService::TEMPLATES[$m])) {
            return $this->redirectRoute('promotions.storefront.banner.create', ['modelo' => $m], navigate: false);
        }
        $this->kind = \App\Services\Promotions\StoreKind::for(Auth::id());
        $s = PortalSetting::for(Auth::id());
        $this->featured_title = (string) $s->featured_title;
        $this->featuredIds = array_values(array_map('intval', $s->featured_ids ?? []));
        $this->notice_text = (string) $s->notice_text;
        $this->notice_link = (string) $s->notice_link;
        $this->notice_active = (bool) $s->notice_active;
        $this->loadHome($s);
        $this->auto = HomeLayout::auto($s);
        $this->loadIdentity($s);
        // Uma coleção pronta para cada categoria, criada uma vez; as categorias novas pelo botão.
        if (\Illuminate\Support\Facades\Schema::hasColumn('portal_settings', 'home') && empty(($s->home ?? [])['cats_done'])) {
            $made = StorefrontService::createCategoryCollections(Auth::id(), $this->kind);
            $s->home = array_merge($s->home ?? [], ['cats_done' => true]);
            $s->save();
            if ($made) {
                session()->flash('success', $made === 1 ? 'Criamos 1 coleção para a sua categoria.' : 'Criamos ' . $made . ' coleções, uma para cada categoria.');
            }
        }
    }

    /* ---------- Página inicial ---------- */

    private function loadHome(?PortalSetting $s = null): void
    {
        $h = HomeLayout::get($s ?? PortalSetting::for(Auth::id()));
        $this->home_order = $h['order'];
        $this->home_off = $h['off'];
        $this->home_colors = $h['colors'];
        $this->home_surface = $h['surface'];
        $this->home_style = $h['style'];
        $this->home_look = $h['look'];
        $this->home_theme = $h['theme'];
    }

    private function saveHome(): void
    {
        if (! HomeLayout::ready()) {
            return;
        }
        HomeLayout::save(Auth::id(), [
            'theme' => $this->home_theme, 'order' => array_values($this->home_order), 'off' => array_values($this->home_off),
            'colors' => $this->home_colors, 'style' => $this->home_style, 'surface' => $this->home_surface,
            'look' => HomeLayout::look($this->home_theme, $this->home_look),
        ]);
        $this->loadHome();
        $this->previewKey++;
    }

    public function moveSection(string $key, int $dir): void
    {
        $i = array_search($key, $this->home_order, true);
        $j = $i === false ? false : $i + $dir;
        if ($j === false || $j < 0 || $j >= count($this->home_order)) {
            return;
        }
        [$this->home_order[$i], $this->home_order[$j]] = [$this->home_order[$j], $this->home_order[$i]];
        $this->saveHome();
    }

    public function toggleSection(string $key): void
    {
        if (! isset(HomeLayout::SECTIONS[$key])) {
            return;
        }
        $this->home_off = in_array($key, $this->home_off, true)
            ? array_values(array_diff($this->home_off, [$key]))
            : array_merge($this->home_off, [$key]);
        $this->saveHome();
    }

    public function setStyle(string $style): void
    {
        if (isset(HomeLayout::STYLES[$style])) {
            $this->home_style = $style;
            $this->saveHome();
        }
    }

    /* ---------- Automações ---------- */

    public function updatedAuto(): void
    {
        $this->saveAuto();
    }

    public function setAuto(string $key, $value): void
    {
        if (array_key_exists($key, HomeLayout::AUTO_DEFAULT) && $key !== 'skip') {
            $this->auto[$key] = $value;
            $this->saveAuto();
        }
    }

    /** Liga ou desliga uma data na troca de tema automática. */
    public function toggleAutoDate(string $date): void
    {
        $skip = (array) ($this->auto['skip'] ?? []);
        $this->auto['skip'] = in_array($date, $skip, true) ? array_values(array_diff($skip, [$date])) : array_merge($skip, [$date]);
        $this->saveAuto();
    }

    private function saveAuto(): void
    {
        if (! HomeLayout::ready()) {
            return;
        }
        $this->auto = HomeLayout::auto(new PortalSetting(['home' => ['auto' => $this->auto]]));
        HomeLayout::save(Auth::id(), ['auto' => $this->auto]);
        foreach (array_keys(HomeLayout::AUTO_FEATURED) as $src) {
            \Illuminate\Support\Facades\Cache::forget('portal-featured-' . Auth::id() . '-' . $src);
        }
        \Illuminate\Support\Facades\Cache::forget('portal-cats-auto-' . Auth::id());
        $this->previewKey++;
        $this->dispatch('notify', type: 'success', message: 'Automação salva.');
    }

    /** Estilo do site: uma escolha de cada parte (topo, cards, fundo…). */
    public function setLook(string $part, string $value): void
    {
        if (isset(HomeLayout::LOOKS[$part][1][$value])) {
            $this->home_look[$part] = $value;
            $this->saveHome();
        }
    }

    /** Volta o estilo para o que o tema traz. */
    public function resetLook(): void
    {
        $this->home_look = HomeLayout::look($this->home_theme);
        $this->saveHome();
    }

    public function updatedHomeColors(): void
    {
        $this->home_colors = array_map(fn ($c, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $c) ? strtolower($c) : $d, array_values($this->home_colors) + HomeLayout::BASE, HomeLayout::BASE);
        $this->saveHome();
    }

    public function updatedHomeSurface(): void
    {
        $def = HomeLayout::SURFACES['padrao'];
        $this->home_surface = array_map(fn ($c, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $c) ? strtolower($c) : $d, array_values($this->home_surface) + $def, $def);
        $this->saveHome();
    }

    public function pickTheme(string $key): void
    {
        $this->pendingTheme = isset(HomeLayout::THEMES[$key]) && $this->pendingTheme !== $key ? $key : '';
    }

    public function applyTheme(): void
    {
        $key = $this->pendingTheme;
        if (! isset(HomeLayout::THEMES[$key]) || ! HomeLayout::ready()) {
            return;
        }
        $done = HomeLayout::apply(Auth::id(), $key, $this->kind, $this->withBanner, $this->withNotice);
        $s = PortalSetting::for(Auth::id());
        $this->featured_title = (string) $s->featured_title;
        $this->notice_text = (string) $s->notice_text;
        $this->notice_active = (bool) $s->notice_active;
        $this->loadHome($s);
        $this->pendingTheme = '';
        $this->previewKey++;
        $t = HomeLayout::THEMES[$key];
        $extra = array_filter([in_array('banner', $done, true) ? 'banner no topo' : null, in_array('aviso', $done, true) ? 'aviso' : null]);
        $this->dispatch('notify', type: 'success', message: 'Tema ' . $t['name'] . ' aplicado' . ($extra ? ' (com ' . implode(' e ', $extra) . ')' : '') . '. Mude o que quiser abaixo.');
    }

    public function createCategoryCollections(): void
    {
        $made = StorefrontService::createCategoryCollections(Auth::id(), $this->kind);
        $this->dispatch('notify', type: 'success', message: $made === 1 ? '1 coleção criada.' : $made . ' coleções criadas.');
    }

    public function setKind(string $kind): void
    {
        $this->kind = \App\Services\Promotions\StoreKind::set(Auth::id(), $kind);
    }

    /** Produtos com foto para os modelos já abrirem bonitos (destaques primeiro). */
    private function samplePhotos(int $n): array
    {
        $uid = Auth::id();
        $real = fn ($q) => $q->whereNotNull('image')->where('image', '!=', 'product-placeholder.png')->where('image', 'not like', 'ml_%');
        $ids = collect($this->featuredIds)->map(fn ($v) => (int) $v);
        $withImg = $ids->isEmpty() ? collect() : Product::where('user_id', $uid)->whereIn('id', $ids)->where($real)->pluck('id');
        $ordered = $ids->filter(fn ($id) => $withImg->contains($id));
        if ($ordered->count() < $n) {
            $more = Product::where('user_id', $uid)->whereNull('parent_id')->whereIn('status', ['active', 'ativo'])->where($real)
                ->where(fn ($q) => $q->where('stock_quantity', '>', 0)->orWhere('made_to_order', true))
                ->whereNotIn('id', $ordered->all())->orderByDesc('id')->limit($n)->pluck('id');
            $ordered = $ordered->concat($more);
        }

        return $ordered->take($n)->map(fn ($id) => ['p' => (int) $id])->values()->all();
    }

    public function toggleBanner(int $id): void
    {
        if ($b = $this->banner($id)) {
            $b->update(['active' => ! $b->active]);
        }
    }

    public function moveBanner(int $id, int $dir): void
    {
        $b = $this->banner($id);
        if (! $b) {
            return;
        }
        $list = PortalBanner::where('user_id', Auth::id())->where('placement', $b->placement)->orderBy('sort')->orderBy('id')->get()->values();
        $this->swap($list, $list->search(fn ($x) => $x->id === $b->id), $dir);
    }

    public function deleteBanner(int $id): void
    {
        if ($b = $this->banner($id)) {
            $this->dropFiles(array_merge([$b->image, $b->image_mobile], $b->designFiles()));
            $b->delete();
            $this->dispatch('notify', type: 'success', message: 'Banner excluído.');
        }
    }

    /* ---------- Identidade da loja ---------- */

    private function loadIdentity(PortalSetting $s): void
    {
        $id = \App\Services\Portal\StoreIdentity::for(Auth::id());
        $this->identity = array_intersect_key($id, array_flip(\App\Services\Portal\StoreIdentity::FIELDS));
        $this->identity['payments'] = array_values((array) ($this->identity['payments'] ?? []));
        $this->identity['show_name'] = (bool) ($this->identity['show_name'] ?? true);
        foreach (array_keys(\App\Services\Portal\StoreIdentity::allPages()) as $k) {
            $this->pages[$k] = (string) (($s->pages ?? [])[$k] ?? '');
        }
        $this->identity['privacy_bar'] = (bool) ($this->identity['privacy_bar'] ?? true);
    }

    public function updatedLogoUpload(): void
    {
        $this->validate(['logoUpload' => ['image', 'max:3072']], [], ['logoUpload' => 'logo']);
        $path = $this->logoUpload->store('portal-banners/logos', 'public');
        $this->dropFiles(array_filter([$this->identity['logo'] ?? null]));
        $this->identity['logo'] = $path;
        $this->logoUpload = null;
        $this->saveIdentity(true);
    }

    public function removeLogo(): void
    {
        $this->dropFiles(array_filter([$this->identity['logo'] ?? null]));
        $this->identity['logo'] = null;
        $this->saveIdentity(true);
    }

    public function setFont(string $font): void
    {
        if (isset(\App\Services\Portal\StoreIdentity::FONTS[$font])) {
            $this->identity['font'] = $font;
            $this->saveIdentity(true);
        }
    }

    public function saveIdentity(bool $quiet = false): void
    {
        $this->validate([
            'identity.name' => ['required', 'string', 'max:60'],
            'identity.tagline' => ['nullable', 'string', 'max:80'],
            'identity.whatsapp' => ['nullable', 'string', 'max:30'],
            'identity.phone' => ['nullable', 'string', 'max:30'],
            'identity.email' => ['nullable', 'email', 'max:120'],
            'identity.instagram' => ['nullable', 'string', 'max:120'],
            'identity.facebook' => ['nullable', 'string', 'max:120'],
            'identity.tiktok' => ['nullable', 'string', 'max:120'],
            'identity.address' => ['nullable', 'string', 'max:200'],
            'identity.hours' => ['nullable', 'string', 'max:200'],
            'identity.legal_name' => ['nullable', 'string', 'max:120'],
            'identity.document' => ['nullable', 'string', 'max:30'],
            'pages.*' => ['nullable', 'string', 'max:5000'],
        ], [], ['identity.name' => 'nome da loja', 'identity.email' => 'e-mail', 'pages.*' => 'texto']);
        $data = array_intersect_key($this->identity, array_flip(\App\Services\Portal\StoreIdentity::FIELDS));
        $data = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $data);
        $data['payments'] = array_values(array_intersect(array_keys(\App\Services\Portal\StoreIdentity::PAYMENTS), (array) ($data['payments'] ?? [])));
        $data['show_name'] = (bool) ($data['show_name'] ?? true);
        $data['privacy_bar'] = (bool) ($data['privacy_bar'] ?? true);
        $s = PortalSetting::for(Auth::id());
        $s->identity = $data;
        $s->pages = array_map(fn ($v) => trim((string) $v), array_intersect_key($this->pages, \App\Services\Portal\StoreIdentity::allPages()));
        $s->save();
        \App\Services\Portal\StoreIdentity::forget(Auth::id());
        $this->previewKey++;
        if (! $quiet) {
            $this->dispatch('notify', type: 'success', message: 'Identidade da loja salva.');
        }
    }

    /** Site dentro da lei: põe o texto pronto no campo para a loja revisar e trocar o que quiser. */
    public function useLegalText(string $page): void
    {
        if ($page === 'trocas' || isset(\App\Services\Portal\StoreIdentity::LEGAL_PAGES[$page])) {
            $id = array_merge(\App\Services\Portal\StoreIdentity::for(Auth::id()), $this->identity);
            $id['document_text'] = \App\Services\Portal\StoreIdentity::formatDocument($id['document'] ?? '');
            $this->pages[$page] = \App\Services\Portal\StoreIdentity::legalDefault($page, $id);
            $this->saveIdentity(true);
            $this->dispatch('notify', type: 'success', message: 'Texto pronto colocado. Revise e salve se mudar algo.');
        }
    }

    public function togglePrivacyBar(): void
    {
        $this->identity['privacy_bar'] = ! ($this->identity['privacy_bar'] ?? true);
        $this->saveIdentity(true);
    }

    /**
     * O que a lei pede de quem vende pela internet e como a loja está em cada ponto.
     * Cada item: key, ícone, título, texto, situação (ok | falta | aviso), lei, botão/ação.
     */
    private function legalChecks(int $uid): array
    {
        $id = $this->identity;
        $doc = preg_replace('/\D+/', '', (string) ($id['document'] ?? ''));
        $docOk = in_array(strlen($doc), [11, 14], true);
        $hasData = filled($id['legal_name'] ?? null) && $docOk && filled($id['address'] ?? null);
        $contact = filled($id['email'] ?? null) && (filled($id['whatsapp'] ?? null) || filled($id['phone'] ?? null));
        $own = fn ($k) => trim((string) ($this->pages[$k] ?? '')) !== '';
        $trocasOk = ! $own('trocas') || preg_match('/7\s*\(?sete\)?\s*dias|7 dias|sete dias/iu', (string) $this->pages['trocas']);
        $pay = \App\Models\PaymentSetting::for($uid);

        return [
            ['dados', 'bi-building', 'Nome, CPF/CNPJ e endereço da loja', $hasData ? 'Aparecem no rodapé de todas as páginas.' : 'Quem vende pela internet precisa mostrar o nome (ou razão social), o CPF ou CNPJ e o endereço. Preencha abaixo.', $hasData ? 'ok' : 'falta', 'Decreto 7.962/2013, art. 2º', 'dados'],
            ['contato', 'bi-headset', 'Contato fácil de achar', $contact ? 'E-mail e WhatsApp/telefone no rodapé.' : 'Coloque um e-mail e um WhatsApp ou telefone na Identidade da loja.', $contact ? 'ok' : 'falta', 'Decreto 7.962/2013, art. 2º', 'identidade'],
            ['privacidade', 'bi-person-lock', 'Política de privacidade', $own('privacidade') ? 'Com o seu texto, no rodapé.' : 'No ar com o texto pronto (explica que dados guardamos e para quê). Revise se quiser.', $own('privacidade') ? 'ok' : 'aviso', 'LGPD, Lei 13.709/2018', 'privacidade'],
            ['termos', 'bi-file-earmark-text', 'Termos de compra', $own('termos') ? 'Com o seu texto, no rodapé.' : 'No ar com o texto pronto (preços, pedido, pagamento, entrega). Revise se quiser.', $own('termos') ? 'ok' : 'aviso', 'Código de Defesa do Consumidor', 'termos'],
            ['trocas', 'bi-arrow-counterclockwise', 'Arrependimento em 7 dias', $trocasOk ? ($own('trocas') ? 'Seu texto de trocas fala dos 7 dias.' : 'No ar com o texto pronto de trocas e arrependimento.') : 'O seu texto de trocas não fala do direito de desistir em 7 dias. Use o texto pronto ou acrescente.', $trocasOk ? 'ok' : 'falta', 'CDC, art. 49', 'trocas'],
            ['barra', 'bi-cookie', 'Aviso de privacidade no site', ($id['privacy_bar'] ?? true) ? 'Quem entra vê um aviso curto com o link da política.' : 'Desligado. Recomendo ligar.', ($id['privacy_bar'] ?? true) ? 'ok' : 'aviso', 'LGPD, art. 9º', 'barra'],
            ['aceite', 'bi-check2-square', 'Aceite no cadastro', 'Quem cria conta marca que leu os Termos e a Política de privacidade.', 'ok', 'LGPD, art. 7º e 8º', null],
            ['resumo', 'bi-receipt', 'Resumo antes de enviar o pedido', 'O carrinho mostra produtos, quantidades, frete, desconto e total antes de enviar, e o pedido fica em Meus pedidos.', 'ok', 'Decreto 7.962/2013, art. 4º', null],
            ['conta', 'bi-person-x', 'Cliente pode encerrar a conta', 'Em Meu perfil o cliente vê os dados dele e pode pedir para encerrar a conta; você recebe um aviso.', 'ok', 'LGPD, art. 18', null],
            ['pagamento', 'bi-shield-lock', 'Pagamento seguro', $pay && $pay->enabled ? 'Os dados do cartão vão direto para o Mercado Pago; o site não guarda.' : 'Sem pagamento online ligado: o pagamento é combinado com você.', 'ok', 'LGPD, art. 46', null],
            ['nota', 'bi-journal-text', 'Nota fiscal', 'Isso o site não faz por você: emita a nota das vendas (MEI e empresas) como pede a prefeitura ou o estado.', 'aviso', 'Lembrete', null],
        ];
    }

    /* ---------- Destaques ---------- */

    /** O catálogo mostra até este tanto na fileira. */
    public const FEATURED_MAX = 15;

    /** Mudou a lista: já fica salva (sem botão de salvar). */
    private function persistFeatured(bool $quiet = true): void
    {
        $this->featuredIds = array_values(array_unique(array_map('intval', $this->featuredIds)));
        $s = PortalSetting::for(Auth::id());
        $s->featured_ids = $this->featuredIds;
        $s->save();
        if (! $quiet) {
            $this->dispatch('notify', type: 'success', message: 'Destaques salvos.');
        }
    }

    public function saveFeatured(): void
    {
        $this->validate(['featured_title' => ['nullable', 'string', 'max:60']], [], ['featured_title' => 'título']);
        $s = PortalSetting::for(Auth::id());
        $s->featured_title = trim($this->featured_title) ?: null;
        $s->save();
        $this->persistFeatured(false);
    }

    public function updatedFeaturedTitle(): void
    {
        $this->validate(['featured_title' => ['nullable', 'string', 'max:60']], [], ['featured_title' => 'título']);
        $s = PortalSetting::for(Auth::id());
        $s->featured_title = trim($this->featured_title) ?: null;
        $s->save();
    }

    public function moveFeatured(int $index, int $dir): void
    {
        $this->featuredIds = $this->moved($this->featuredIds, $index, $dir);
        $this->persistFeatured();
    }

    public function removeFeatured(int $id): void
    {
        $this->featuredIds = array_values(array_filter($this->featuredIds, fn ($v) => (int) $v !== $id));
        $this->persistFeatured();
    }

    public function clearFeatured(): void
    {
        $this->featuredIds = [];
        $this->persistFeatured(false);
    }

    /** Completa a fileira com os mais vendidos, as promoções ou os novos (sem repetir). */
    public function fillFeatured(string $src): void
    {
        if (! in_array($src, ['vendidos', 'ofertas', 'novos'], true)) {
            return;
        }
        $room = self::FEATURED_MAX - count($this->featuredIds);
        $add = array_values(array_diff(StorefrontService::ruleRoots(Auth::id(), $src, [], self::FEATURED_MAX * 2), $this->featuredIds));
        if ($room <= 0 || ! $add) {
            $this->dispatch('notify', type: 'info', message: $room <= 0 ? 'A fileira já está cheia (' . self::FEATURED_MAX . ' produtos).' : 'Nenhum produto novo para colocar.');

            return;
        }
        $add = array_slice($add, 0, $room);
        $this->featuredIds = array_merge($this->featuredIds, $add);
        $this->persistFeatured();
        $this->dispatch('notify', type: 'success', message: count($add) === 1 ? '1 produto colocado nos destaques.' : count($add) . ' produtos colocados nos destaques.');
    }

    /* ---------- Coleções ---------- */

    public function toggleCollection(int $id, string $field = 'active'): void
    {
        if (($c = $this->collection($id)) && in_array($field, ['active', 'show_on_home'], true)) {
            $c->update([$field => ! $c->{$field}]);
        }
    }

    public function moveCollection(int $id, int $dir): void
    {
        $list = PortalCollection::where('user_id', Auth::id())->orderBy('sort')->orderBy('id')->get()->values();
        $this->swap($list, $list->search(fn ($x) => $x->id === $id), $dir);
    }

    public function deleteCollection(int $id): void
    {
        if ($c = $this->collection($id)) {
            $this->dropFiles([$c->image]);
            $c->delete();
            $this->dispatch('notify', type: 'success', message: 'Coleção excluída.');
        }
    }

    /* ---------- Aviso no topo ---------- */

    public function saveNotice(): void
    {
        $this->validate([
            'notice_text' => array_filter(['nullable', 'string', 'max:160', $this->notice_active ? 'required' : null]),
            'notice_link' => ['nullable', 'url:http,https', 'max:255'],
        ], [
            'notice_text.required' => 'Escreva o aviso para ligar.',
            'notice_link.url' => 'Cole o endereço completo, começando com https://',
        ], ['notice_text' => 'aviso', 'notice_link' => 'link']);
        $s = PortalSetting::for(Auth::id());
        $s->notice_text = trim($this->notice_text) ?: null;
        $s->notice_link = trim($this->notice_link) ?: null;
        $s->notice_active = $this->notice_active;
        $s->save();
        $this->dispatch('notify', type: 'success', message: 'Aviso salvo.');
    }

    /* ---------- Escolher produtos ---------- */

    public function openPicker(string $for): void
    {
        $this->pickFor = $for === 'featured' ? $for : '';
        $this->pickSearch = '';
        $this->pickFilter = 'todos';
        $this->pickCat = 0;
    }

    public function closePicker(): void
    {
        $this->pickFor = '';
    }

    public function pick(int $id): void
    {
        $root = StorefrontService::rootsOf(Auth::id(), [$id])[0] ?? null;
        if (! $root) {
            return;
        }
        $list = array_map('intval', $this->featuredIds);
        if (in_array($root, $list, true)) {
            $this->featuredIds = array_values(array_filter($list, fn ($v) => $v !== $root));
        } elseif (count($list) >= self::FEATURED_MAX) {
            $this->dispatch('notify', type: 'warning', message: 'O catálogo mostra até ' . self::FEATURED_MAX . ' destaques. Tire um para colocar outro.');

            return;
        } else {
            $this->featuredIds = array_merge($list, [$root]);
        }
        $this->persistFeatured();
    }

    /* ---------- Apoio ---------- */

    private function banner(int $id): ?PortalBanner
    {
        return PortalBanner::where('user_id', Auth::id())->find($id);
    }

    private function collection(int $id): ?PortalCollection
    {
        return PortalCollection::where('user_id', Auth::id())->find($id);
    }

    private function swap($list, $pos, int $dir): void
    {
        if ($pos === false) {
            return;
        }
        $to = $pos + ($dir < 0 ? -1 : 1);
        if ($to < 0 || $to >= $list->count()) {
            return;
        }
        $items = $list->all();
        [$items[$pos], $items[$to]] = [$items[$to], $items[$pos]];
        foreach (array_values($items) as $i => $m) {
            if ($m->sort !== $i) {
                $m->update(['sort' => $i]);
            }
        }
    }

    private function moved(array $ids, int $index, int $dir): array
    {
        $ids = array_values($ids);
        $to = $index + ($dir < 0 ? -1 : 1);
        if (! isset($ids[$index], $ids[$to])) {
            return $ids;
        }
        [$ids[$index], $ids[$to]] = [$ids[$to], $ids[$index]];

        return $ids;
    }

    /** Apaga do disco as imagens antigas que não estão mais em uso. */
    private function dropFiles(array $old, array $keep = []): void
    {
        foreach (array_filter($old) as $path) {
            if (! in_array($path, $keep, true) && str_starts_with($path, 'portal-banners/')) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /** Produtos (principal da família) para mostrar nas listas escolhidas. */
    private function productsById(array $ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (! $ids) {
            return collect();
        }
        $found = Product::where('user_id', Auth::id())->whereIn('id', $ids)->with('activePromotion')->get()->keyBy('id');
        // Estoque somado da família, para avisar o que está esgotado (e não aparece no catálogo).
        $stock = Product::where('user_id', Auth::id())
            ->where(fn ($q) => $q->whereIn('parent_id', $ids)->orWhereIn('id', $ids))
            ->whereIn('status', ['active', 'ativo'])
            ->get(['id', 'parent_id', 'stock_quantity', 'made_to_order'])
            ->groupBy(fn ($p) => (int) ($p->parent_id ?: $p->id))
            ->map(fn ($g) => $g->contains(fn ($p) => $p->made_to_order || $p->stock_quantity > 0));

        return collect($ids)->map(function ($id) use ($found, $stock) {
            $p = $found[$id] ?? null;
            if ($p) {
                $p->setAttribute('sf_available', (bool) ($stock[$id] ?? false));
            }

            return $p;
        })->filter()->values();
    }

    public function render()
    {
        $uid = Auth::id();
        $banners = PortalBanner::where('user_id', $uid)->orderBy('sort')->orderBy('id')->get()->groupBy('placement');
        $collections = PortalCollection::where('user_id', $uid)->orderBy('sort')->orderBy('id')->get();
        // Quantos produtos à venda cada coleção mostra agora (as automáticas pela regra).
        $counts = $this->tab === 'colecoes'
            ? $collections->mapWithKeys(fn ($c) => [$c->id => StorefrontService::productsFor($uid, $c->rootIds(60), 60)->count()])
            : collect();

        $results = collect();
        if ($this->pickFor !== '') {
            $term = trim($this->pickSearch);
            $ruled = in_array($this->pickFilter, ['vendidos', 'ofertas', 'novos'], true)
                ? StorefrontService::ruleRoots($uid, $this->pickFilter, [], 60) : null;
            $results = Product::where('user_id', $uid)
                ->whereNull('parent_id')
                ->whereIn('status', ['active', 'ativo'])
                ->with('activePromotion')
                ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('product_code', 'like', "%{$term}%")))
                ->when($ruled !== null, fn ($q) => $q->whereIn('id', $ruled ?: [0]))
                ->when($this->pickFilter === 'estoque', fn ($q) => $q->where(fn ($w) => $w->where('stock_quantity', '>', 0)->orWhere('made_to_order', true)))
                ->when($this->pickCat > 0, fn ($q) => $q->where('category_id', $this->pickCat))
                ->when($ruled === null, fn ($q) => $q->orderByRaw('CASE WHEN stock_quantity > 0 OR made_to_order = 1 THEN 0 ELSE 1 END')->orderBy('name'))
                ->limit(40)->get();
            if ($ruled) {
                $results = $results->sortBy(fn ($p) => array_search($p->id, $ruled, true))->values();
            }
        }
        $picked = match ($this->pickFor) {
            'featured'   => array_map('intval', $this->featuredIds),
            default      => [],
        };

        // Fotos de exemplo para a fileira de modelos prontos.
        $samples = $this->tab === 'banners' ? PortalBanner::photoUrls($uid, $this->samplePhotos(3)) : [];
        $autoPhotos = [];
        if ($this->tab === 'banners') {
            foreach (['novos', 'vendidos', 'ofertas'] as $src) {
                $autoPhotos[$src] = PortalBanner::autoPhotoUrls($uid, $src, 3);
            }
        }

        return view('livewire.promotions.portal-storefront', [
            'samples'       => $samples,
            'autoPhotos'    => $autoPhotos,
            'banners'       => $banners,
            'collections'   => $collections,
            'counts'        => $counts,
            'missingCats'   => $this->tab === 'colecoes' ? StorefrontService::categoriesWithoutCollection($uid) : collect(),
            'featured'      => $this->productsById($this->featuredIds),
            'results'       => $results,
            'picked'        => $picked,
            'pickCats'      => $this->pickFor !== '' ? \App\Models\Category::where('user_id', $uid)->whereIn('id_category', StorefrontService::categoryCounts($uid)->keys())->orderBy('name')->get(['id_category', 'name']) : collect(),
            'catalogUrl'    => route('portal.catalog', ['userId' => $uid]),
            'themes'        => $this->tab === 'inicio' ? HomeLayout::themes($this->kind) : [],
            'dateThemes'    => $this->tab === 'automacoes' ? HomeLayout::dateThemes($this->kind) : [],
            'secInfo'       => $this->tab === 'inicio' ? $this->sectionInfo($uid, $banners, $collections) : [],
            'navInfo'       => $this->navInfo($banners, $collections),
            'overview'      => $this->tab === 'geral' ? $this->overview($uid, $banners, $collections) : [],
            'legal'         => in_array($this->tab, ['leis', 'geral'], true) ? $this->legalChecks($uid) : [],
        ]);
    }

    /**
     * Visão geral: o que já está pronto na loja online e o que falta, cada um com o botão que leva lá.
     * Cada item: key, ícone, título, texto, pronto, botão, destino (aba da Vitrine ou rota), opcional.
     */
    private function overview(int $uid, $banners, $collections): array
    {
        $today = now()->startOfDay();
        $liveHero = ($banners['hero'] ?? collect())->filter(fn ($b) => $b->active && (! $b->ends_at || $today->lte($b->ends_at)) && (! $b->starts_at || $today->gte($b->starts_at)))->count();
        $pay = \App\Models\PaymentSetting::for($uid);
        $zones = \Illuminate\Support\Facades\Schema::hasTable('portal_shipping_zones') ? \App\Models\PortalShippingZone::where('user_id', $uid)->count() : 0;
        $coupons = \Illuminate\Support\Facades\Schema::hasTable('portal_coupons') ? \App\Models\PortalCoupon::where('user_id', $uid)->where('active', true)->count() : 0;
        $reviews = \Illuminate\Support\Facades\Schema::hasTable('portal_reviews') ? \App\Models\PortalReview::where('user_id', $uid)->count() : 0;
        $id = $this->identity;
        $contacts = collect(['whatsapp', 'instagram', 'phone', 'email'])->filter(fn ($k) => filled($id[$k] ?? null))->count();
        $pages = collect(array_intersect_key($this->pages, \App\Services\Portal\StoreIdentity::PAGES))->filter(fn ($v) => trim($v) !== '')->count();
        $autoOn = collect(['theme', 'featured', 'newbadge', 'sort', 'cats'])->filter(fn ($k) => ! empty($this->auto[$k]))->count();
        $styled = $this->home_theme !== 'padrao' || HomeLayout::look('padrao', $this->home_look) !== HomeLayout::look('padrao');
        $n = fn ($v, $one, $many) => $v === 1 ? "1 {$one}" : "{$v} {$many}";
        $tab = fn ($t) => ['tab', $t];
        $route = fn ($r) => ['route', route($r)];

        return [
            'Deixe a loja com a sua cara' => [
                ['logo', 'bi-image', 'Logo e nome da loja', ($id['logo'] ?? null) ? 'Sua logo aparece no topo do site.' : 'Coloque a sua logo e o nome no topo do site.', (bool) ($id['logo'] ?? null), 'Colocar logo', $tab('identidade'), false],
                ['contatos', 'bi-whatsapp', 'WhatsApp e redes', $contacts ? $n($contacts, 'contato', 'contatos') . ' no rodapé do site.' : 'O cliente acha você pelo WhatsApp e pelas redes no rodapé.', $contacts > 0, 'Pôr contatos', $tab('identidade'), false],
                ['tema', 'bi-palette', 'Tema e estilo', $styled ? 'Tema: ' . (HomeLayout::THEMES[$this->home_theme]['name'] ?? 'Cores da loja') . '.' : 'Escolha um dos 30 temas ou monte o seu estilo.', $styled, 'Escolher tema', $tab('inicio'), false],
                ['paginas', 'bi-file-earmark-text', 'Sobre, trocas e entrega', $pages ? $pages . ' de 3 páginas escritas.' : 'Páginas que passam confiança para quem compra a primeira vez.', $pages > 0, 'Escrever', $tab('identidade'), true],
            ],
            'Monte a vitrine' => [
                ['banner', 'bi-images', 'Banner no topo', $liveHero ? $n($liveHero, 'banner', 'banners') . ' no ar no carrossel.' : 'O carrossel do topo é a primeira coisa que o cliente vê.', $liveHero > 0, 'Criar banner', $tab('banners'), false],
                ['destaques', 'bi-star', 'Produtos em destaque', count($this->featuredIds) || ! empty($this->auto['featured']) ? (count($this->featuredIds) ? count($this->featuredIds) . ' escolhidos' : 'Completando sozinho') . '.' : 'Uma fileira com o que você mais quer vender.', count($this->featuredIds) > 0 || ! empty($this->auto['featured']), 'Escolher', $tab('destaques'), false],
                ['colecoes', 'bi-collection', 'Coleções', ($c = $collections->where('active', true)->count()) ? $n($c, 'coleção ligada', 'coleções ligadas') . '.' : 'Grupos de produtos com capa, como Presentes ou Mais vendidos.', $c > 0, 'Criar coleção', $tab('colecoes'), false],
                ['aviso', 'bi-megaphone', 'Aviso no topo', $this->notice_active && trim($this->notice_text) !== '' ? '“' . \Illuminate\Support\Str::limit($this->notice_text, 40) . '”' : 'Uma linha em todas as páginas, como frete grátis.', $this->notice_active && trim($this->notice_text) !== '', 'Escrever aviso', $tab('aviso'), true],
            ],
            'Venda pelo site' => [
                ['pagamento', 'bi-credit-card', 'Pagamento online', $pay && $pay->enabled ? 'Pix e cartão ligados pelo Mercado Pago.' : 'Receba no Pix e no cartão direto pelo site.', (bool) ($pay && $pay->enabled), 'Ligar pagamento', $route('settings.payments'), false],
                ['entrega', 'bi-truck', 'Entrega e retirada', $pay && ($pay->delivery_enabled || $pay->pickup_enabled) ? trim(($pay->delivery_enabled ? 'Entrega' . ($zones ? ' (' . $n($zones, 'região de frete', 'regiões de frete') . ')' : '') : '') . ($pay->delivery_enabled && $pay->pickup_enabled ? ' e ' : '') . ($pay->pickup_enabled ? 'retirada' : '')) . '.' : 'Diga se entrega, quanto custa o frete e se pode retirar.', (bool) ($pay && ($pay->delivery_enabled || $pay->pickup_enabled)), 'Configurar', $route('settings.payments'), false],
                ['cupom', 'bi-ticket-perforated', 'Cupom de desconto', $coupons ? $n($coupons, 'cupom ativo', 'cupons ativos') . '.' : 'Um código como BEMVINDO10 ajuda a fechar a primeira compra.', $coupons > 0, 'Criar cupom', $route('promotions.coupons'), true],
                ['kit', 'bi-gift', 'Monte seu kit', \App\Services\Portal\KitService::enabled($uid) ? 'Ligado: o cliente monta cestas e caixas.' : 'O cliente monta cestas e caixas de presente.', \App\Services\Portal\KitService::enabled($uid), 'Criar kits', $route('promotions.kits'), true],
            ],
            'Dentro da lei' => [
                ['leis', 'bi-shield-check', 'Site dentro da lei', ($lf = collect($this->legalChecks($uid))->where(4, 'falta')->count()) ? $n($lf, 'ponto', 'pontos') . ' para arrumar (dados da loja, trocas, privacidade).' : 'Dados da loja, privacidade, termos e trocas em dia.', $lf === 0, 'Arrumar', $tab('leis'), false],
            ],
            'Deixe no automático' => [
                ['automacoes', 'bi-robot', 'Automações', $autoOn ? $n($autoOn, 'ligada', 'ligadas') . '.' : 'Tema das datas, destaques e selo de novidade sozinhos.', $autoOn > 0, 'Ligar', $tab('automacoes'), false],
                ['carrinhos', 'bi-cart-x', 'Carrinho abandonado', \App\Services\Portal\CartRecovery::ready() && \App\Services\Portal\CartRecovery::settingsFor($uid)['on'] ? 'Ligado: quem não fecha o pedido recebe uma lembrança.' : 'Lembre no WhatsApp quem deixou produtos no carrinho.', \App\Services\Portal\CartRecovery::ready() && \App\Services\Portal\CartRecovery::settingsFor($uid)['on'], 'Ligar', $route('promotions.carts'), false],
                ['relatorio', 'bi-graph-up-arrow', 'Relatório do site', 'Veja visitas, de onde vêm os clientes e os produtos mais vistos.', \App\Services\Portal\SiteStats::ready() && \App\Models\PortalPageView::where('user_id', $uid)->exists(), 'Ver', $route('promotions.report'), true],
                ['avaliacoes', 'bi-star-half', 'Avaliações', $reviews ? $n($reviews, 'avaliação', 'avaliações') . ' dos clientes.' : 'Quem compra pode deixar estrelas e comentário.', $reviews > 0, 'Ver', $route('promotions.reviews'), true],
            ],
        ];
    }

    /** Situação de cada parte no menu das configurações. */
    private function navInfo($banners, $collections): array
    {
        $today = now()->startOfDay();
        $live = $banners->flatten()->filter(fn ($b) => $b->active && (! $b->ends_at || $today->lte($b->ends_at)) && (! $b->starts_at || $today->gte($b->starts_at)))->count();
        $n = fn ($v, $one, $many) => $v === 1 ? "1 {$one}" : "{$v} {$many}";
        $theme = HomeLayout::THEMES[$this->home_theme]['name'] ?? null;

        return [
            'geral'     => null,
            'identidade' => ($this->identity['logo'] ?? null) ? 'Com a sua logo' : 'Logo, nome, contatos e páginas',
            'inicio'    => $theme ? 'Tema: ' . $theme : null,
            'banners'   => $n($live, 'banner no ar', 'banners no ar'),
            'destaques' => $n(count($this->featuredIds), 'produto escolhido', 'produtos escolhidos'),
            'colecoes'  => $n($collections->where('active', true)->count(), 'coleção ligada', 'coleções ligadas'),
            'aviso'     => $this->notice_active && trim($this->notice_text) !== '' ? 'Ligado' : 'Desligado',
            'leis'      => ($lf = collect($this->legalChecks(Auth::id()))->where(4, 'falta')->count()) ? ($lf === 1 ? '1 ponto para arrumar' : $lf . ' pontos para arrumar') : 'Tudo em dia',
            'automacoes'=> ($on = collect(['theme', 'featured', 'newbadge', 'sort', 'cats'])->filter(fn ($k) => ! empty($this->auto[$k]))->count())
                ? ($on === 1 ? '1 ligada' : $on . ' ligadas') : 'Nenhuma ligada',
        ];
    }

    /** Uma linha de cada bloco: quanto tem e onde muda. */
    private function sectionInfo(int $uid, $banners, $collections): array
    {
        $live = fn ($place) => ($banners[$place] ?? collect())->filter(fn ($b) => $b->active && (! $b->ends_at || now()->startOfDay()->lte($b->ends_at)) && (! $b->starts_at || now()->startOfDay()->gte($b->starts_at)))->count();
        $home = $collections->where('active', true)->where('show_on_home', true)->count();
        $offers = \App\Models\Promotion::where('user_id', $uid)->where('status', \App\Models\Promotion::ATIVA)->count();
        $n = fn ($v, $one, $many) => $v === 1 ? "1 {$one}" : "{$v} {$many}";

        return [
            'hero'        => [$n($live('hero'), 'banner no ar', 'banners no ar'), 'banners'],
            'cats'        => [$n(\App\Models\Category::where('user_id', $uid)->whereIn('id_category', StorefrontService::categoryCounts($uid)->keys())->count(), 'categoria', 'categorias'), null],
            'featured'    => [$n(count($this->featuredIds), 'produto escolhido', 'produtos escolhidos'), 'destaques'],
            'offers'      => [$n($offers, 'promoção ativa', 'promoções ativas'), null],
            'collections' => [$n($home, 'coleção', 'coleções') . ' e ' . $n($live('strip'), 'faixa', 'faixas'), 'colecoes'],
            'recent'      => ['Só aparece para quem já abriu algum produto', null],
        ];
    }
}
