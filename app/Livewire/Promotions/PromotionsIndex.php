<?php

namespace App\Livewire\Promotions;

use App\Livewire\Promotions\Concerns\SchedulesPromotion;
use App\Models\Client;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionSetting;
use App\Services\Products\OrderPdfParser;
use App\Services\Products\PromotionNotificationService;
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
    use HasNotifications, SchedulesPromotion, WithPagination;

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
    public string $shareTemplate = ''; // '' = mensagem da promoção ou modelo das configurações

    // Folheto das promoções (imagem com várias ofertas)
    public bool $showFlyerModal = false;

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
        // Liga as agendadas que já chegaram na hora, mesmo se a rotina horária atrasar.
        $service->refreshStatuses(Auth::id());
        try {
            app(PromotionNotificationService::class)->notifyStarted(Auth::id());
        } catch (\Throwable $e) {
            report($e);
        }

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
        $this->fillScheduleFrom($promo);
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

        // "Agora" numa promoção que já está no ar mantém o início original.
        $current = $this->editingPromotionId ? $this->ownPromotion($this->editingPromotionId) : null;
        $startsAt = $this->startsAtValue()
            ?? ($current && $current->status === Promotion::ATIVA ? $current->starts_at : null)
            ?? ($current ? now() : null);

        $data = [
            'original_price' => $this->num($this->originalPrice),
            'promo_price'    => $this->num($this->promoPrice),
            'starts_at'      => $startsAt,
            'ends_at'        => $this->endsAt ?: null,
            'message'        => $this->message,
        ];

        try {
            if ($current) {
                $updated = $service->update($current, $data);
                $this->notifySuccess($updated->isScheduled()
                    ? 'Promoção agendada para ' . Promotion::humanDateTime($updated->starts_at) . '.'
                    : 'Promoção atualizada.');
            } else {
                $promo = $service->start($product, $data);
                $this->notifySuccess($promo->status === Promotion::AGENDADA
                    ? 'Promoção agendada para ' . Promotion::humanDateTime($promo->starts_at) . '.'
                    : $product->name . ' está em promoção.');
            }
        } catch (InvalidArgumentException $e) {
            $this->addError(str_contains($e->getMessage(), 'promoção') || str_contains($e->getMessage(), 'data') ? 'dates' : 'promoPrice', $e->getMessage());
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

    /** Agendada: começa já, sem esperar a data. */
    public function startNow(int $promotionId, PromotionService $service): void
    {
        try {
            $promo = $service->startNow($this->ownPromotion($promotionId));
            $this->notifySuccess(ucwords(mb_strtolower((string) $promo->product?->name)) . ' já está em promoção.');
        } catch (InvalidArgumentException $e) {
            $this->notifyError($e->getMessage(), 8000);
        }
    }

    /** Agendada: cancela antes de começar. */
    public function cancelScheduled(int $promotionId, PromotionService $service): void
    {
        $promo = $this->ownPromotion($promotionId);
        if ($promo->status !== Promotion::AGENDADA) {
            return;
        }
        $service->end($promo, 'cancelada');
        $this->selected = array_values(array_diff($this->selected, [$promotionId]));
        $this->notifySuccess('Agendamento cancelado. O preço de ' . ucwords(mb_strtolower((string) $promo->product?->name)) . ' não muda.');
    }

    public function endPromotion(int $promotionId, PromotionService $service): void
    {
        $promo = $this->ownPromotion($promotionId);
        if ($promo->status === Promotion::AGENDADA) {
            $this->cancelScheduled($promotionId, $service);
            return;
        }
        $service->end($promo);
        $this->selected = array_values(array_diff($this->selected, [$promotionId]));
        $this->notifySuccess('Promoção retirada. ' . $promo->product?->name . ' volta para ' . $service->money((float) $promo->product?->price_sale) . '.');
    }

    public function bulkEnd(PromotionService $service): void
    {
        $count = 0;
        foreach ($this->selectedPromotions() as $promo) {
            $service->end($promo, $promo->status === Promotion::AGENDADA ? 'cancelada' : 'manual');
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
        $this->bulkEndsAt = $this->settings->default_days ? now(Promotion::tz())->addDays($this->settings->default_days)->format('Y-m-d') : '';
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
        $this->shareTemplate = '';
        $this->buildShareText();
        $this->showShareModal = true;
    }

    public function setShareClient(?int $clientId): void
    {
        $this->shareClientId = $clientId ?: null;
        $this->buildShareText();
    }

    /** Troca o modelo da mensagem só para este envio ('' volta ao padrão). */
    public function setShareTemplate(string $key): void
    {
        $this->shareTemplate = isset(PromotionSetting::TEMPLATES[$key]) ? $key : '';
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

    // ─── Folheto ──────────────────────────────────────────────────

    public function openFlyer(): void
    {
        app(PromotionService::class)->refreshStatuses(Auth::id());
        $this->showFlyerModal = true;
    }

    /**
     * Promoções no ar com estoque, do maior desconto ao menor, para o
     * folheto. Nunca leva a quantidade em estoque.
     */
    public function getFlyerDataProperty(): array
    {
        if (!$this->showFlyerModal) {
            return ['cards' => [], 'store' => '', 'phone' => ''];
        }
        $service = app(PromotionService::class);
        $user = Auth::user();

        $promos = Promotion::ofUser(Auth::id())
            ->live()
            ->with('product')
            ->get()
            ->filter(fn (Promotion $p) => $p->product)
            ->sortByDesc(fn (Promotion $p) => $p->discount_percent)
            ->values();

        return [
            'cards' => $promos->map(function (Promotion $p) use ($service) {
                $name = mb_convert_case(mb_strtolower((string) $p->product->name), MB_CASE_TITLE, 'UTF-8');
                // "400Ml" → "400ml", "Cuide-Se" → "Cuide-se"
                $name = preg_replace_callback('/(\d)(Ml|Mg|G|Kg|Cm|Un)\b/u', fn ($m) => $m[1] . mb_strtolower($m[2]), $name);
                $name = preg_replace_callback('/-(\p{Lu})/u', fn ($m) => '-' . mb_strtolower($m[1]), $name);

                return [
                    'id'       => $p->id,
                    'name'     => $name . ($p->product->variation_value ? ' · ' . $p->product->variation_value : ''),
                    // Caminho relativo: mesma origem, senão o canvas fica "sujo" e não vira PNG
                    'image'    => $p->product->image && $p->product->image !== 'product-placeholder.png' ? parse_url($p->product->image_url, PHP_URL_PATH) : null,
                    'original' => $service->money((float) $p->original_price),
                    'promo'    => $service->money((float) $p->promo_price),
                    'discount' => $p->discount_percent,
                    'ends'     => $p->ends_at ? $p->endsLocal()->format('Y-m-d') : null,
                ];
            })->all(),
            'store' => (string) ($user?->name ?? ''),
            'phone' => (string) ($user?->phone ?? ''),
        ];
    }

    /** Registra o envio do folheto (baixar, compartilhar ou copiar). */
    public function recordFlyer(array $ids, string $channel): void
    {
        $ids = Promotion::ofUser(Auth::id())->whereIn('id', array_map('intval', $ids))->pluck('id')->all();
        if (!$ids) {
            return;
        }
        app(PromotionService::class)->recordSend(
            Auth::id(),
            $ids,
            null,
            in_array($channel, ['compartilhar', 'copiar', 'baixar'], true) ? $channel : 'compartilhar',
            'folheto'
        );
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
            'collection_due_template'     => $s->collectionTemplate('due'),
            'collection_overdue_template' => $s->collectionTemplate('overdue'),
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
            'settingsForm.collection_due_template'     => 'nullable|string|max:2000',
            'settingsForm.collection_overdue_template' => 'nullable|string|max:2000',
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
            'collection_due_template'     => $form['collection_due_template'] ?? null,
            'collection_overdue_template' => $form['collection_overdue_template'] ?? null,
        ])->save();

        $this->showSettingsModal = false;
        $this->notifySuccess('Configurações salvas.');
    }

    public function resetTemplate(): void
    {
        $this->settingsForm['message_template'] = PromotionSetting::DEFAULT_TEMPLATE;
    }

    public function resetCollectionTemplate(string $kind): void
    {
        if (isset(PromotionSetting::COLLECTION_TEMPLATES[$kind])) {
            [, , $column, $default] = PromotionSetting::COLLECTION_TEMPLATES[$kind];
            $this->settingsForm[$column] = $default;
        }
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

        match ($this->tab === 'encerradas' ? 'recentes' : ($this->tab === 'agendadas' ? 'inicio' : $this->sort)) {
            'inicio'   => $query->orderBy('promotions.starts_at')->orderBy('products.name'),
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
                // Caminho relativo: mesma origem da página, senão o canvas não pode virar imagem para copiar/compartilhar
                'image'    => parse_url($p->product->image_url, PHP_URL_PATH) ?: $p->product->image_url,
                'original' => $service->money((float) $p->original_price),
                'promo'    => $service->money((float) $p->promo_price),
                'discount' => $p->discount_percent,
                'validity' => $p->ends_at ? 'Válido até ' . $p->endsLocal()->format('d/m') : 'Enquanto durar o estoque',
                'savings'  => $service->money(max(0, (float) $p->original_price - (float) $p->promo_price)),
                'stock'    => (int) $p->product->stock_quantity,
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

        $template = PromotionSetting::TEMPLATES[$this->shareTemplate][2] ?? null;

        return $promos->count() === 1
            ? $service->message($promos->first(), $client, $this->settings, $template)
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
        $this->reset(['editingPromotionId', 'editingProductId', 'originalPrice', 'promoPrice', 'startMode', 'startDate', 'startTime', 'endsAt', 'message']);
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
