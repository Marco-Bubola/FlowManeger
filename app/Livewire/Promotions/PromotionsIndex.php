<?php

namespace App\Livewire\Promotions;

use App\Models\Client;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionSetting;
use App\Services\Products\OrderPdfParser;
use App\Services\Products\PromotionService;
use App\Traits\HasNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Área de Promoções: pôr e retirar produtos de promoção, editar preço "de" e
 * "por" (respeitando o lucro mínimo sobre o "a pagar"), datas e a mensagem de
 * WhatsApp de cada produto.
 */
class PromotionsIndex extends Component
{
    use HasNotifications, WithPagination;

    public string $tab = 'ativas'; // ativas | sugestoes | agendadas | encerradas
    public string $search = '';
    public string $sort = 'desconto'; // desconto | validade | recentes | nome
    public int $perPage = 24;

    // Aba Sugestões: tipo e quantos mostrar
    public string $suggest = 'tabela'; // ver PromotionService::SUGGESTION_TYPES
    public int $suggestLimit = 10;

    /** @var array<int> ids selecionados (promoções; ou produtos na aba Sugestões) */
    public array $selected = [];

    // Modal de edição (nova promoção tem página própria: promotions.create)
    public bool $showEditModal = false;
    public ?int $editingPromotionId = null;
    public ?int $editingProductId = null;
    public $originalPrice = '';
    public $promoPrice = '';
    public string $startsAt = '';
    public string $endsAt = '';
    public string $message = '';

    // Ações em massa
    public bool $showBulkModal = false;
    public string $bulkAction = ''; // desconto | validade
    public $bulkPercent = '';
    public string $bulkEndsAt = '';

    // WhatsApp
    public bool $showShareModal = false;
    public array $shareIds = [];
    public ?int $shareClientId = null;
    public string $shareText = '';

    // Configurações
    public bool $showSettingsModal = false;
    public array $settingsForm = [];

    protected $queryString = [
        'tab'    => ['except' => 'ativas'],
        'search' => ['except' => ''],
        'suggest' => ['except' => 'tabela'],
    ];

    public function mount(PromotionService $service): void
    {
        $service->refreshStatuses(Auth::id());

        // Vindo do produto (/promotions?produto=ID): abre a promoção dele ou uma nova.
        if ($productId = (int) request()->query('produto')) {
            $current = Promotion::ofUser(Auth::id())
                ->where('product_id', $productId)
                ->whereIn('status', [Promotion::ATIVA, Promotion::AGENDADA])
                ->latest('id')->first();

            if ($current) {
                $this->openEdit($current->id);
            } elseif (Product::where('user_id', Auth::id())->whereKey($productId)->exists()) {
                $this->redirectRoute('promotions.create', ['produto' => $productId]);
            }
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingSort(): void { $this->resetPage(); }

    public function setTab(string $tab): void
    {
        if (!in_array($tab, ['ativas', 'sugestoes', 'agendadas', 'encerradas'], true)) {
            return;
        }
        $this->tab = $tab;
        $this->selected = [];
        $this->resetPage();
    }

    public function setSuggest(string $type): void
    {
        if (isset(PromotionService::SUGGESTION_TYPES[$type])) {
            $this->suggest = $type;
            $this->selected = [];
        }
    }

    public function setSuggestLimit(int $limit): void
    {
        $this->suggestLimit = in_array($limit, [10, 20, 50], true) ? $limit : 10;
    }

    public function toggleSelect(int $id): void
    {
        $this->selected = in_array($id, $this->selected, true)
            ? array_values(array_diff($this->selected, [$id]))
            : [...$this->selected, $id];
    }

    public function selectAllOnPage(): void
    {
        $ids = collect($this->items->items())->pluck('id')->all();
        $this->selected = count(array_diff($ids, $this->selected)) === 0
            ? array_values(array_diff($this->selected, $ids))
            : array_values(array_unique([...$this->selected, ...$ids]));
    }

    // ─── Pôr em promoção / editar ─────────────────────────────────

    public function openEdit(int $promotionId): void
    {
        $promo = $this->ownPromotion($promotionId);
        $this->resetEditForm();
        $this->editingPromotionId = $promo->id;
        $this->editingProductId = $promo->product_id;
        $this->originalPrice = $this->fmt((float) $promo->original_price);
        $this->promoPrice = $this->fmt((float) $promo->promo_price);
        $this->startsAt = $promo->starts_at?->format('Y-m-d') ?? '';
        $this->endsAt = $promo->ends_at?->format('Y-m-d') ?? '';
        $this->message = (string) $promo->message;
        $this->showEditModal = true;
    }

    /** Botões −/+ do modal: muda o preço "por" em passos de 1%, sem passar do mínimo. */
    public function nudgeDiscount(int $direction): void
    {
        $original = $this->num($this->originalPrice);
        $product = $this->editingProduct;
        if ($original <= 0 || !$product) {
            return;
        }
        $this->promoPrice = $this->fmt(app(PromotionService::class)->stepPrice($product, $original, $this->num($this->promoPrice), $direction, $this->settings));
    }

    public function setDiscountPercent($percent): void
    {
        $original = $this->num($this->originalPrice);
        $product = $this->editingProduct;
        if ($original <= 0 || !$product) {
            return;
        }
        $percent = max(1, min(99, (float) $percent));
        $this->promoPrice = $this->fmt(app(PromotionService::class)->priceForPercent($product, $original, $percent, $this->settings));
    }

    /** Usa o menor preço permitido pelo lucro mínimo. */
    public function useMinPrice(): void
    {
        if ($product = $this->editingProduct) {
            $service = app(PromotionService::class);
            $min = $service->minPromoPrice($product, $this->settings);
            $this->promoPrice = $this->fmt($service->withEnding($min, $min, $this->settings));
        }
    }

    public function saveEdit(PromotionService $service): void
    {
        $product = $this->editingProduct;
        if (!$product) {
            $this->notifyError('Escolha o produto.');
            return;
        }

        $data = [
            'original_price' => $this->num($this->originalPrice),
            'promo_price'    => $this->num($this->promoPrice),
            'starts_at'      => $this->startsAt ?: null,
            'ends_at'        => $this->endsAt ?: null,
            'message'        => $this->message,
        ];

        try {
            if ($this->editingPromotionId) {
                $service->update($this->ownPromotion($this->editingPromotionId), $data);
                $this->notifySuccess('Promoção atualizada.');
            } else {
                $promo = $service->start($product, $data);
                $this->notifySuccess($promo->status === Promotion::AGENDADA
                    ? 'Promoção agendada para ' . $promo->starts_at->format('d/m') . '.'
                    : $product->name . ' está em promoção.');
            }
        } catch (InvalidArgumentException $e) {
            $this->addError('promoPrice', $e->getMessage());
            return;
        }

        $this->showEditModal = false;
        $this->resetEditForm();
    }

    /** Põe direto em promoção pelos valores sugeridos (tabela → revenda). */
    public function quickStart(int $productId, PromotionService $service): void
    {
        $this->startProducts([$productId], $service);
    }

    public function bulkStart(PromotionService $service): void
    {
        $this->startProducts($this->selected, $service);
        $this->selected = [];
    }

    public function endPromotion(int $promotionId, PromotionService $service): void
    {
        $promo = $this->ownPromotion($promotionId);
        $service->end($promo);
        $this->selected = array_values(array_diff($this->selected, [$promotionId]));
        $this->notifySuccess('Promoção retirada. ' . $promo->product?->name . ' volta para ' . $service->money((float) $promo->product?->price_sale) . '.');
    }

    public function bulkEnd(PromotionService $service): void
    {
        $count = 0;
        foreach ($this->selectedPromotions() as $promo) {
            $service->end($promo);
            $count++;
        }
        $this->selected = [];
        $this->notifySuccess($count . ' promoção(ões) retirada(s).');
    }

    /** Repete uma promoção encerrada com os mesmos valores. */
    public function restart(int $promotionId, PromotionService $service): void
    {
        $old = $this->ownPromotion($promotionId);
        $settings = $this->settings;

        try {
            $service->start($old->product, [
                'original_price' => (float) $old->original_price,
                'promo_price'    => (float) $old->promo_price,
                'ends_at'        => $settings->default_days ? now()->addDays($settings->default_days) : null,
                'message'        => $old->message,
            ]);
            $this->notifySuccess('Promoção reativada.');
        } catch (InvalidArgumentException $e) {
            $this->notifyError($e->getMessage());
        }
    }

    public function openBulk(string $action): void
    {
        if (empty($this->selected)) {
            return;
        }
        $this->bulkAction = $action;
        $this->bulkPercent = '';
        $this->bulkEndsAt = $this->settings->default_days ? now()->addDays($this->settings->default_days)->format('Y-m-d') : '';
        $this->resetErrorBag();
        $this->showBulkModal = true;
    }

    public function applyBulk(PromotionService $service): void
    {
        $done = 0;
        $limited = [];

        foreach ($this->selectedPromotions() as $promo) {
            $data = [];
            if ($this->bulkAction === 'desconto') {
                $percent = (float) str_replace(',', '.', (string) $this->bulkPercent);
                if ($percent <= 0 || $percent >= 100) {
                    $this->addError('bulkPercent', 'Informe um desconto entre 1% e 99%.');
                    return;
                }
                $wanted = round((float) $promo->original_price * (1 - $percent / 100), 2);
                $min = $service->minPromoPrice($promo->product, $this->settings);
                if ($wanted < $min) {
                    $limited[] = $promo->product->name;
                    $wanted = $min;
                }
                $data['promo_price'] = $wanted;
            } else {
                $data['ends_at'] = $this->bulkEndsAt ?: null;
            }

            try {
                $service->update($promo, $data);
                $done++;
            } catch (InvalidArgumentException $e) {
                $limited[] = $promo->product->name . ' (' . $e->getMessage() . ')';
            }
        }

        $this->showBulkModal = false;
        $this->notifySuccess($done . ' promoção(ões) atualizada(s).');
        if ($limited) {
            $this->notifyWarning('Ficaram no lucro mínimo: ' . implode(', ', array_slice($limited, 0, 5)) . (count($limited) > 5 ? '…' : ''), 8000);
        }
    }

    // ─── WhatsApp ─────────────────────────────────────────────────

    public function openShare(?int $promotionId = null): void
    {
        $ids = $promotionId ? [$promotionId] : $this->selected;
        $promos = $this->promotionsByIds($ids);
        if ($promos->isEmpty()) {
            return;
        }
        $this->shareIds = $promos->pluck('id')->all();
        $this->shareClientId = null;
        $this->buildShareText();
        $this->showShareModal = true;
    }

    public function setShareClient(?int $clientId): void
    {
        $this->shareClientId = $clientId ?: null;
        $this->buildShareText();
    }

    /**
     * Registra o envio e devolve o link do WhatsApp para o navegador abrir.
     * channel: whatsapp | compartilhar | copiar
     */
    public function recordShare(string $channel, ?int $clientId = null): string
    {
        $service = app(PromotionService::class);
        $client = $clientId ? $this->ownClient($clientId) : null;
        $promos = $this->promotionsByIds($this->shareIds);

        $text = $clientId === null || $clientId === $this->shareClientId
            ? $this->shareText
            : $this->messageFor($promos, $client);

        $service->recordSend(
            Auth::id(),
            $promos->pluck('id')->all(),
            $client?->id,
            in_array($channel, ['whatsapp', 'compartilhar', 'copiar'], true) ? $channel : 'whatsapp',
            $promos->count() > 1 ? 'ofertas' : 'produto'
        );

        return $service->whatsappUrl($text, $client);
    }

    // ─── Configurações ────────────────────────────────────────────

    public function openSettings(): void
    {
        $s = $this->settings;
        $this->settingsForm = [
            'min_margin_percent'   => $this->fmt((float) $s->min_margin_percent),
            'suggest_min_discount' => $this->fmt((float) $s->suggest_min_discount),
            'default_days'         => $s->default_days,
            'message_template'     => $s->template(),
            'footer'               => (string) $s->footer,
            'footer_catalog_link'  => (bool) $s->footer_catalog_link,
            'default_discount'     => $this->fmt((float) ($s->default_discount ?? 10)),
            'price_ending'         => $s->price_ending ?: 'none',
            'auto_end_out_of_stock' => (bool) ($s->auto_end_out_of_stock ?? true),
            'greet_client'         => (bool) ($s->greet_client ?? true),
        ];
        $this->resetErrorBag();
        $this->showSettingsModal = true;
    }

    public function saveSettings(): void
    {
        $form = $this->settingsForm;
        $form['min_margin_percent'] = $this->num($form['min_margin_percent'] ?? 0);
        $form['suggest_min_discount'] = $this->num($form['suggest_min_discount'] ?? 0);
        $form['default_discount'] = $this->num($form['default_discount'] ?? 10);
        $form['default_days'] = ($form['default_days'] ?? '') === '' ? null : (int) $form['default_days'];
        $this->settingsForm = $form;

        $this->validate([
            'settingsForm.min_margin_percent'   => 'required|numeric|min:0|max:500',
            'settingsForm.suggest_min_discount' => 'required|numeric|min:0|max:99',
            'settingsForm.default_days'         => 'nullable|integer|min:1|max:365',
            'settingsForm.message_template'     => 'required|string|max:2000',
            'settingsForm.footer'               => 'nullable|string|max:500',
            'settingsForm.default_discount'     => 'required|numeric|min:1|max:90',
            'settingsForm.price_ending'         => 'required|in:' . implode(',', array_keys(PromotionSetting::PRICE_ENDINGS)),
        ], [], [
            'settingsForm.default_discount'     => 'desconto padrão',
            'settingsForm.min_margin_percent'   => 'lucro mínimo',
            'settingsForm.suggest_min_discount' => 'desconto mínimo',
            'settingsForm.default_days'         => 'dias de validade',
            'settingsForm.message_template'     => 'modelo da mensagem',
        ]);

        $settings = $this->settings;
        $settings->fill([
            'user_id'              => Auth::id(),
            'min_margin_percent'   => $form['min_margin_percent'],
            'suggest_min_discount' => $form['suggest_min_discount'],
            'default_days'         => $form['default_days'],
            'message_template'     => $form['message_template'],
            'footer'               => $form['footer'] ?? null,
            'footer_catalog_link'  => (bool) ($form['footer_catalog_link'] ?? false),
            'default_discount'     => $form['default_discount'],
            'price_ending'         => $form['price_ending'],
            'auto_end_out_of_stock' => (bool) ($form['auto_end_out_of_stock'] ?? true),
            'greet_client'         => (bool) ($form['greet_client'] ?? true),
        ])->save();

        $this->showSettingsModal = false;
        $this->notifySuccess('Configurações salvas.');
    }

    public function resetTemplate(): void
    {
        $this->settingsForm['message_template'] = PromotionSetting::DEFAULT_TEMPLATE;
    }

    public function useTemplate(string $key): void
    {
        if (isset(PromotionSetting::TEMPLATES[$key])) {
            $this->settingsForm['message_template'] = PromotionSetting::TEMPLATES[$key][2];
        }
    }

    public function backfillOriginalPrices(PromotionService $service, OrderPdfParser $parser): void
    {
        $result = $service->backfillOriginalPrices(Auth::id(), $parser);

        if ($result['files'] === 0) {
            $this->notifyWarning('Não encontrei PDFs de uploads anteriores guardados no app.');
            return;
        }

        $this->notifySuccess(sprintf(
            'Li %d PDF(s) e preenchi o preço de tabela de %d produto(s).',
            $result['files'],
            $result['updated']
        ), 6000);
    }

    // ─── Dados para a view ────────────────────────────────────────

    public function getSettingsProperty(): PromotionSetting
    {
        return PromotionSetting::forUser(Auth::id());
    }

    public function getEditingProductProperty(): ?Product
    {
        return $this->editingProductId ? Product::where('user_id', Auth::id())->find($this->editingProductId) : null;
    }

    public function getEditPreviewProperty(): array
    {
        $product = $this->editingProduct;
        $original = $this->num($this->originalPrice);
        $promo = $this->num($this->promoPrice);
        $cost = $product ? (float) $product->price : 0;
        $min = $product ? app(PromotionService::class)->minPromoPrice($product, $this->settings) : 0;

        return [
            'max'      => $product ? app(PromotionService::class)->maxDiscountPercent($product, $original, $this->settings) : 0,
            'discount' => $original > 0 && $promo > 0 ? (int) round((1 - $promo / $original) * 100) : 0,
            'savings'  => max(0, $original - $promo),
            'cost'     => $cost,
            'min'      => $min,
            'profit'   => $promo - $cost,
            'margin'   => $cost > 0 ? round(($promo / $cost - 1) * 100, 1) : 0,
            'belowMin' => $product && $promo > 0 && $promo < $min,
        ];
    }

    public function getItemsProperty()
    {
        $service = app(PromotionService::class);
        $term = trim($this->search);

        if ($this->tab === 'sugestoes') {
            $list = $service->suggestionsByType(Auth::id(), $this->suggest, $this->suggestLimit, $this->settings, $term);

            return new LengthAwarePaginator($list, $list->count(), max(1, $list->count()), 1);
        }

        $status = ['ativas' => Promotion::ATIVA, 'agendadas' => Promotion::AGENDADA, 'encerradas' => Promotion::ENCERRADA][$this->tab];

        $query = Promotion::ofUser(Auth::id())
            ->where('promotions.status', $status)
            ->with(['product' => fn ($q) => $q->with('category')->withCount('variants'), 'sends' => fn ($q) => $q->latest()->with('client')])
            ->join('products', 'products.id', '=', 'promotions.product_id')
            ->select('promotions.*')
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('products.name', 'like', "%{$term}%")->orWhere('products.product_code', 'like', "%{$term}%")));

        match ($this->tab === 'encerradas' ? 'recentes' : $this->sort) {
            'validade' => $query->orderByRaw('promotions.ends_at IS NULL')->orderBy('promotions.ends_at'),
            'nome'     => $query->orderBy('products.name'),
            'recentes' => $query->orderByDesc($this->tab === 'encerradas' ? 'promotions.ended_at' : 'promotions.created_at'),
            default    => $query->orderByRaw('(promotions.promo_price / promotions.original_price) asc'),
        };

        return $query->paginate($this->perPage);
    }

    public function getStatsProperty(): array
    {
        $service = app(PromotionService::class);
        $userId = Auth::id();
        $active = Promotion::ofUser($userId)->where('status', Promotion::ATIVA);

        return [
            'ativas'      => (clone $active)->count(),
            'agendadas'   => Promotion::ofUser($userId)->where('status', Promotion::AGENDADA)->count(),
            'vencendo'    => (clone $active)->whereNotNull('ends_at')->where('ends_at', '<=', now()->addDays(3))->count(),
            'desconto'    => (int) round((float) (clone $active)->avg(DB::raw('(1 - promo_price / original_price) * 100'))),
            'sugestoes'   => $service->suggestionsQuery($userId, $this->settings)->count(),
            'semTabela'   => Product::where('user_id', $userId)->where('stock_quantity', '>', 0)
                                ->where(fn ($q) => $q->whereNull('price_original')->orWhere('price_original', '<=', 0))->count(),
            'vendas'      => $service->salesSummary($userId),
        ];
    }

    public function getShareDataProperty(): array
    {
        if (!$this->showShareModal) {
            return ['promotions' => collect(), 'clients' => collect(), 'allClients' => collect(), 'cards' => []];
        }
        $service = app(PromotionService::class);
        $promos = $this->promotionsByIds($this->shareIds);

        return [
            'promotions' => $promos,
            'clients'    => $promos->count() === 1 ? $service->interestedClients($promos->first()) : collect(),
            'allClients' => Client::where('user_id', Auth::id())->whereNotNull('phone')->where('phone', '!=', '')->orderBy('name')->get(['id', 'name', 'phone']),
            'cards'      => $promos->map(fn (Promotion $p) => [
                'name'     => $p->product->name,
                'image'    => $p->product->image_url,
                'original' => $service->money((float) $p->original_price),
                'promo'    => $service->money((float) $p->promo_price),
                'discount' => $p->discount_percent,
                'validity' => $p->ends_at ? 'Válido até ' . $p->ends_at->format('d/m') : 'Enquanto durar o estoque',
            ])->values()->all(),
        ];
    }

    public function render()
    {
        return view('livewire.promotions.promotions-index', [
            'items'    => $this->items,
            'stats'    => $this->stats,
            'settings' => $this->settings,
        ]);
    }

    // ─────────────────────────────────────────────────────────────

    private function startProducts(array $productIds, PromotionService $service): void
    {
        $settings = $this->settings;
        $ok = 0;
        $errors = [];

        foreach (Product::where('user_id', Auth::id())->whereIn('id', $productIds)->get() as $product) {
            $prices = $service->suggestedPrices($product, $settings);
            try {
                $service->start($product, [
                    'original_price' => (float) $prices['original'],
                    'promo_price'    => (float) ($prices['promo'] ?? $prices['original']),
                    'ends_at'        => $settings->default_days ? now()->addDays($settings->default_days) : null,
                ]);
                $ok++;
            } catch (InvalidArgumentException $e) {
                $errors[] = $product->name . ': ' . $e->getMessage();
            }
        }

        if ($ok) {
            $this->notifySuccess($ok . ' produto(s) em promoção.');
        }
        if ($errors) {
            $this->notifyWarning(implode(' | ', array_slice($errors, 0, 3)), 8000);
        }
    }

    private function buildShareText(): void
    {
        $promos = $this->promotionsByIds($this->shareIds);
        $client = $this->shareClientId ? $this->ownClient($this->shareClientId) : null;
        $this->shareText = $this->messageFor($promos, $client);
    }

    private function messageFor($promos, ?Client $client): string
    {
        $service = app(PromotionService::class);

        return $promos->count() === 1
            ? $service->message($promos->first(), $client, $this->settings)
            : $service->offersMessage($promos, $client, $this->settings);
    }

    private function selectedPromotions()
    {
        if ($this->tab === 'sugestoes') {
            return collect();
        }

        return $this->promotionsByIds($this->selected)->whereIn('status', [Promotion::ATIVA, Promotion::AGENDADA]);
    }

    private function promotionsByIds(array $ids)
    {
        return Promotion::ofUser(Auth::id())->whereIn('id', $ids)->with('product')->get();
    }

    private function ownPromotion(int $id): Promotion
    {
        return Promotion::ofUser(Auth::id())->with('product')->findOrFail($id);
    }

    private function ownProduct(int $id): Product
    {
        return Product::where('user_id', Auth::id())->findOrFail($id);
    }

    private function ownClient(int $id): Client
    {
        return Client::where('user_id', Auth::id())->findOrFail($id);
    }

    private function resetEditForm(): void
    {
        $this->reset(['editingPromotionId', 'editingProductId', 'originalPrice', 'promoPrice', 'startsAt', 'endsAt', 'message']);
        $this->resetErrorBag();
    }

    /** "1.449,50" | "1449.50" | 1449.5 → 1449.5 */
    private function num($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        $value = str_replace([' ', 'R$'], '', (string) $value);
        if (str_contains($value, ',')) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }

        return (float) $value;
    }

    private function fmt(?float $value): string
    {
        return $value === null ? '' : number_format($value, 2, ',', '');
    }
}
