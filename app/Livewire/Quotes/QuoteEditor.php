<?php

namespace App\Livewire\Quotes;

use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteMaterial;
use App\Services\Quotes\QuoteService;
use App\Support\Quotes\QuoteDefaults;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Criar e editar orçamento. Cada item tem a ficha de custo (materiais e horas da
 * tabela, linhas livres), acréscimos e o preço (sugerido pela margem ou digitado).
 * O custo fica só aqui: o cliente vê nome, descrição, fotos e preço.
 */
#[Layout('components.layouts.app')]
class QuoteEditor extends Component
{
    use WithFileUploads;

    public ?int $quoteId = null;
    public string $status = 'rascunho';
    public string $kind = 'produto';
    public string $title = '';

    public ?int $clientId = null;
    public string $clientSearch = '';
    public string $contactName = '';
    public string $contactPhone = '';

    public array $items = [];
    public array $services = [];
    public string $pickup = '';

    public $discount = 0;
    public $deliveryFee = 0;
    public $productionDays = '';
    public string $validUntil = '';
    public string $paymentTerms = '';
    public string $clientNotes = '';
    public string $adminNotes = '';

    public array $afterPhotos = [];

    // Uploads temporários: uploads[chave do item] = [arquivos]
    public array $uploads = [];
    public array $afterUploads = [];

    // Buscas
    public ?int $productFor = null;   // índice do item que está escolhendo produto (-1 = item novo)
    public string $productSearch = '';
    public ?int $materialFor = null;  // índice do item que está escolhendo material
    public string $materialSearch = '';
    public ?int $openCosts = 0;       // item com a ficha de custo aberta

    public array $settings = [];

    public function mount(?int $quote = null): void
    {
        $this->settings = QuoteService::settings(Auth::user());

        if ($quote) {
            $q = Quote::where('user_id', Auth::id())->findOrFail($quote);
            $this->quoteId = $q->id;
            $this->status = $q->status;
            $this->kind = $q->kind;
            $this->title = (string) $q->title;
            $this->clientId = $q->client_id;
            $this->contactName = (string) $q->contact_name;
            $this->contactPhone = (string) $q->contact_phone;
            $this->items = array_values(array_map(fn ($it) => $this->normalizeItem($it), $q->items ?? []));
            $this->services = $q->services ?? [];
            $this->pickup = (string) $q->pickup;
            $this->discount = (float) $q->discount ?: '';
            $this->deliveryFee = (float) $q->delivery_fee ?: '';
            $this->productionDays = $q->production_days ?: '';
            $this->validUntil = $q->valid_until?->toDateString() ?? '';
            $this->paymentTerms = (string) $q->payment_terms;
            $this->clientNotes = (string) $q->client_notes;
            $this->adminNotes = (string) $q->admin_notes;
            $this->afterPhotos = $q->after_photos ?? [];
            $this->openCosts = $this->items ? 0 : null;
            if (!$this->items) {
                $this->items[] = $this->prefillFromRequest($q);
            }

            return;
        }

        $this->validUntil = Carbon::today(QuoteService::TZ)->addDays($this->settings['validity_days'])->toDateString();
        $this->kind = request('tipo') === 'reforma' ? 'reforma' : 'produto';
        if ($cid = (int) request('cliente')) {
            $this->clientId = Client::where('user_id', Auth::id())->whereKey($cid)->value('id');
        }
        if (($pid = (int) request('produto')) && ($p = $this->ownedProduct($pid))) {
            $this->items[] = $this->freshItem($p, 'variacao');
        } else {
            $this->items[] = $this->freshItem();
        }
    }

    // ------------------------------------------------------------------ itens

    protected function ownedProduct(int $id): ?Product
    {
        return Product::where('user_id', Auth::id())->find($id);
    }

    protected function freshItem(?Product $p = null, string $type = 'livre'): array
    {
        $it = QuoteService::newItem($p, $type, (float) $this->settings['margin']);
        if ($this->kind === 'reforma' && !$p) {
            $it['name'] = '';
        }

        return $this->reprice($it);
    }

    protected function normalizeItem(array $it): array
    {
        return array_merge(['key' => \Illuminate\Support\Str::random(8), 'type' => 'livre', 'product_id' => null, 'name' => '', 'description' => '', 'qty' => 1,
            'photos' => [], 'costs' => [], 'extras' => [], 'margin' => (float) $this->settings['margin'], 'price' => 0, 'manual' => false,
            'attrs' => ['cor' => '', 'medida' => '']], $it);
    }

    /** Pedido do portal sem itens ainda: começa um item com o que o cliente escreveu e as fotos. */
    protected function prefillFromRequest(Quote $q): array
    {
        $p = $q->product_id ? $this->ownedProduct($q->product_id) : null;
        $it = $this->freshItem($p, $p ? 'variacao' : 'livre');
        $data = $q->request_data ?? [];
        if (!$p) {
            $it['name'] = $q->kind === 'reforma' ? 'Reforma' : '';
        }
        $it['description'] = mb_substr((string) $q->request_text, 0, 600);
        $it['attrs']['cor'] = (string) ($data['cor_texto'] ?? '');
        $it['attrs']['medida'] = (string) ($data['medidas'] ?? '');
        if (!$p && $q->request_photos) {
            $it['photos'] = array_slice($q->request_photos, 0, 4);
        }

        return $it;
    }

    public function addItem(): void
    {
        $this->items[] = $this->freshItem();
        $this->openCosts = count($this->items) - 1;
    }

    public function removeItem(int $i): void
    {
        unset($this->items[$i]);
        $this->items = array_values($this->items);
        $this->openCosts = null;
    }

    public function duplicateItem(int $i): void
    {
        if (!isset($this->items[$i])) {
            return;
        }
        $copy = $this->items[$i];
        $copy['key'] = \Illuminate\Support\Str::random(8);
        array_splice($this->items, $i + 1, 0, [$copy]);
    }

    public function moveItem(int $i, int $dir): void
    {
        $j = $i + $dir;
        if (!isset($this->items[$i], $this->items[$j])) {
            return;
        }
        [$this->items[$i], $this->items[$j]] = [$this->items[$j], $this->items[$i]];
    }

    public function toggleCosts(int $i): void
    {
        $this->openCosts = $this->openCosts === $i ? null : $i;
    }

    public function pickProductFor(int $i): void
    {
        $this->productFor = $this->productFor === $i ? null : $i;
        $this->productSearch = '';
    }

    /** Escolhe o produto do item: "estoque" = vende como está; "variacao" = outra cor/medida dele. */
    public function useProduct(int $productId, string $type = 'variacao'): void
    {
        $p = $this->ownedProduct($productId);
        $i = $this->productFor;
        if (!$p || $i === null) {
            return;
        }
        $new = $this->freshItem($p, in_array($type, ['estoque', 'variacao'], true) ? $type : 'variacao');
        if (isset($this->items[$i])) {
            $old = $this->items[$i];
            $new['key'] = $old['key'];
            $new['qty'] = $old['qty'] ?? 1;
            $new['attrs'] = array_merge($new['attrs'], array_filter((array) ($old['attrs'] ?? [])));
            if (trim((string) ($old['description'] ?? '')) !== '') {
                $new['description'] = $old['description'];
            }
            $new['photos'] = array_values(array_unique(array_merge($new['photos'], (array) ($old['photos'] ?? []))));
            $this->items[$i] = $new;
        } else {
            $this->items[] = $new;
        }
        $this->productFor = null;
        $this->openCosts = $i;
    }

    public function clearProduct(int $i): void
    {
        if (isset($this->items[$i])) {
            $this->items[$i]['product_id'] = null;
            $this->items[$i]['type'] = 'livre';
        }
    }

    // ------------------------------------------------------------------ custo

    public function pickMaterialFor(int $i): void
    {
        $this->materialFor = $this->materialFor === $i ? null : $i;
        $this->materialSearch = '';
    }

    public function addMaterial(int $materialId): void
    {
        $i = $this->materialFor;
        $m = QuoteMaterial::where('user_id', Auth::id())->find($materialId);
        if (!$m || $i === null || !isset($this->items[$i])) {
            return;
        }
        $this->items[$i]['costs'][] = ['mid' => $m->id, 'n' => $m->name, 'u' => $m->unit, 'q' => $m->unit === 'h' ? 1 : 1, 'v' => (float) $m->price];
        $this->items[$i] = $this->reprice($this->items[$i]);
        $this->materialSearch = '';
    }

    public function addCost(int $i, string $kind = 'livre'): void
    {
        if (!isset($this->items[$i])) {
            return;
        }
        $this->items[$i]['costs'][] = $kind === 'frete'
            ? ['mid' => null, 'n' => 'Frete / entrega por minha conta', 'u' => 'un', 'q' => 1, 'v' => 0]
            : ['mid' => null, 'n' => '', 'u' => 'un', 'q' => 1, 'v' => 0];
    }

    public function removeCost(int $i, int $c): void
    {
        unset($this->items[$i]['costs'][$c]);
        $this->items[$i]['costs'] = array_values($this->items[$i]['costs'] ?? []);
        $this->items[$i] = $this->reprice($this->items[$i]);
    }

    /** Atualiza os custos que vieram da tabela com o preço de hoje. */
    public function refreshCosts(int $i): void
    {
        $ids = collect($this->items[$i]['costs'] ?? [])->pluck('mid')->filter();
        $now = QuoteMaterial::where('user_id', Auth::id())->whereIn('id', $ids)->pluck('price', 'id');
        foreach ($this->items[$i]['costs'] as $c => $row) {
            if (!empty($row['mid']) && isset($now[$row['mid']])) {
                $this->items[$i]['costs'][$c]['v'] = (float) $now[$row['mid']];
            }
        }
        $this->items[$i] = $this->reprice($this->items[$i]);
    }

    public function toggleExtra(int $i, int $e): void
    {
        $extra = $this->settings['extras'][$e] ?? null;
        if (!$extra || !isset($this->items[$i])) {
            return;
        }
        $list = collect($this->items[$i]['extras'] ?? []);
        $this->items[$i]['extras'] = $list->contains('n', $extra['n'])
            ? $list->reject(fn ($x) => $x['n'] === $extra['n'])->values()->all()
            : $list->push($extra)->values()->all();
        $this->items[$i] = $this->reprice($this->items[$i]);
    }

    public function useSuggested(int $i): void
    {
        if (isset($this->items[$i])) {
            $this->items[$i]['manual'] = false;
            $this->items[$i] = $this->reprice($this->items[$i]);
        }
    }

    protected function reprice(array $it): array
    {
        $it['qty'] = max(1, (int) ($it['qty'] ?? 1));
        foreach ($it['costs'] ?? [] as $c => $row) {
            $it['costs'][$c]['q'] = is_numeric($row['q'] ?? null) ? (float) $row['q'] : 0;
            $it['costs'][$c]['v'] = is_numeric($row['v'] ?? null) ? (float) $row['v'] : 0;
        }
        if (empty($it['manual'])) {
            $it['price'] = QuoteService::suggestedPrice(QuoteService::itemCost($it), (float) ($it['margin'] ?? $this->settings['margin']), QuoteService::itemExtras($it));
        }

        return $it;
    }

    public function updatedItems($value, $key): void
    {
        $parts = explode('.', (string) $key);
        $i = (int) ($parts[0] ?? -1);
        if (!isset($this->items[$i])) {
            return;
        }
        if (($parts[1] ?? '') === 'price') {
            $this->items[$i]['price'] = max(0, (float) str_replace(',', '.', (string) $value));
            $this->items[$i]['manual'] = true;
        }
        $this->items[$i] = $this->reprice($this->items[$i]);
    }

    public function updatedKind(): void
    {
        if ($this->kind === 'reforma') {
            $this->productionDays = $this->productionDays ?: 10;
        }
    }

    public function toggleService(string $s): void
    {
        $this->services = in_array($s, $this->services, true)
            ? array_values(array_diff($this->services, [$s]))
            : array_values(array_merge($this->services, [$s]));
    }

    // ------------------------------------------------------------------ fotos

    public function updatedUploads($value, $key): void
    {
        $k = explode('.', (string) $key)[0];
        $i = collect($this->items)->search(fn ($it) => ($it['key'] ?? '') === $k);
        $files = $this->uploads[$k] ?? [];
        $files = is_array($files) ? $files : [$files];
        $this->validate(['uploads.' . $k . '.*' => 'image|max:12288'], [], ['uploads.' . $k . '.*' => 'foto']);
        if ($i === false) {
            return;
        }
        foreach ($files as $f) {
            if (count($this->items[$i]['photos'] ?? []) >= 8) {
                break;
            }
            if ($path = QuoteService::storePhoto($f, Auth::id())) {
                $this->items[$i]['photos'][] = $path;
            }
        }
        unset($this->uploads[$k]);
    }

    public function removePhoto(int $i, int $p): void
    {
        unset($this->items[$i]['photos'][$p]);
        $this->items[$i]['photos'] = array_values($this->items[$i]['photos'] ?? []);
    }

    public function updatedAfterUploads(): void
    {
        $this->validate(['afterUploads.*' => 'image|max:12288'], [], ['afterUploads.*' => 'foto']);
        foreach ($this->afterUploads as $f) {
            if (count($this->afterPhotos) < 12 && ($path = QuoteService::storePhoto($f, Auth::id()))) {
                $this->afterPhotos[] = $path;
            }
        }
        $this->afterUploads = [];
    }

    public function removeAfterPhoto(int $p): void
    {
        unset($this->afterPhotos[$p]);
        $this->afterPhotos = array_values($this->afterPhotos);
    }

    // ------------------------------------------------------------------ cliente

    public function pickClient(int $id): void
    {
        $this->clientId = Client::where('user_id', Auth::id())->whereKey($id)->value('id');
        $this->clientSearch = '';
    }

    public function clearClient(): void
    {
        $this->clientId = null;
    }

    /** Cria o cliente sem sair do orçamento (nome da busca + WhatsApp, se tiver). Igual ao criar venda. */
    public function createClient(string $name = '', string $phone = ''): void
    {
        $name = trim($name) !== '' ? trim($name) : trim($this->clientSearch);
        $phone = trim($phone) !== '' ? trim($phone) : trim($this->contactPhone);
        if ($name === '' || mb_strlen($name) > 255) {
            $this->addError('newClient', 'Escreva o nome do cliente.');

            return;
        }
        $client = Client::where('user_id', Auth::id())->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first()
            ?? Client::create(['user_id' => Auth::id(), 'name' => $name, 'phone' => mb_substr($phone, 0, 30) ?: null]);
        $this->clientId = $client->id;
        $this->clientSearch = '';
        $this->contactName = '';
        $this->dispatch('notify', type: 'success', message: 'Cliente ' . $client->name . ' selecionado.');
    }

    // ------------------------------------------------------------------ salvar

    public function save(string $then = 'show')
    {
        $this->items = array_values(array_filter($this->items, fn ($it) => trim((string) ($it['name'] ?? '')) !== '' || !empty($it['costs']) || !empty($it['photos'])));
        $this->validate([
            'kind' => 'required|in:produto,reforma',
            'title' => 'nullable|string|max:160',
            'contactName' => 'nullable|string|max:120',
            'contactPhone' => 'nullable|string|max:30',
            'items' => 'required|array|min:1|max:40',
            'items.*.name' => 'required|string|max:160',
            'items.*.description' => 'nullable|string|max:2000',
            'items.*.qty' => 'required|integer|min:1|max:9999',
            'items.*.price' => 'required|numeric|min:0|max:9999999',
            'discount' => 'nullable|numeric|min:0',
            'deliveryFee' => 'nullable|numeric|min:0',
            'productionDays' => 'nullable|integer|min:0|max:365',
            'validUntil' => 'nullable|date',
            'paymentTerms' => 'nullable|string|max:255',
            'clientNotes' => 'nullable|string|max:3000',
            'adminNotes' => 'nullable|string|max:3000',
        ], [
            'items.required' => 'Coloque pelo menos um item no orçamento.',
            'items.*.name.required' => 'Dê um nome para cada item.',
        ], [
            'items.*.name' => 'nome do item', 'items.*.qty' => 'quantidade', 'items.*.price' => 'preço',
            'discount' => 'desconto', 'deliveryFee' => 'entrega', 'productionDays' => 'prazo', 'validUntil' => 'validade',
        ]);

        if (!$this->clientId && trim($this->contactName) === '') {
            $this->addError('contactName', 'Escolha um cliente ou escreva o nome de quem pediu.');

            return null;
        }

        $quote = $this->quoteId
            ? Quote::where('user_id', Auth::id())->findOrFail($this->quoteId)
            : null;
        $before = $quote ? json_encode([$quote->publicItems(), (float) $quote->total]) : null;

        $data = [
            'kind' => $this->kind,
            'title' => trim($this->title) ?: null,
            'client_id' => $this->clientId,
            'contact_name' => trim($this->contactName) ?: null,
            'contact_phone' => trim($this->contactPhone) ?: null,
            'items' => array_map(fn ($it) => $this->reprice($it), $this->items),
            'services' => $this->kind === 'reforma' ? $this->services : [],
            'pickup' => $this->kind === 'reforma' ? ($this->pickup ?: null) : null,
            'after_photos' => $this->afterPhotos,
            'discount' => (float) ($this->discount ?: 0),
            'delivery_fee' => (float) ($this->deliveryFee ?: 0),
            'production_days' => $this->productionDays !== '' ? (int) $this->productionDays : null,
            'valid_until' => $this->validUntil ?: null,
            'payment_terms' => trim($this->paymentTerms) ?: null,
            'client_notes' => trim($this->clientNotes) ?: null,
            'admin_notes' => trim($this->adminNotes) ?: null,
        ];

        if (!$quote) {
            $quote = QuoteService::make(Auth::id(), $data);
            $quote->addEvent('criado', 'Orçamento criado');
        } else {
            $quote->fill($data);
            if ($quote->status === 'pedido') {
                $quote->status = 'rascunho';
                $quote->addEvent('criado', 'Orçamento montado a partir do pedido');
            }
        }
        QuoteService::recalc($quote);

        // Mudou o que o cliente vê depois de enviado: o link mostra a versão nova.
        $after = json_encode([$quote->publicItems(), (float) $quote->total]);
        if ($before !== null && $before !== $after && in_array($quote->status, ['enviado', 'aprovado', 'vencido'], true)) {
            $quote->edited_after_sent_at = now();
            $quote->addEvent('editado', 'Editado depois de enviado');
            if ($quote->status === 'aprovado') {
                $quote->status = 'enviado';
                $quote->approved_at = null;
                $quote->addEvent('editado', 'Voltou para "Enviado": o cliente precisa aprovar a versão nova');
            }
        }
        if ($quote->status === 'vencido' && $quote->valid_until && !$quote->isExpired()) {
            $quote->status = $quote->sent_at ? 'enviado' : 'rascunho';
        }
        $quote->save();
        $this->quoteId = $quote->id;
        $this->status = $quote->status;

        $this->dispatch('notify', type: 'success', message: 'Orçamento ' . $quote->code() . ' salvo.');

        if ($then === 'venda') {
            if ($quote->sale_id) {
                return $this->redirectRoute('sales.show', $quote->sale_id, navigate: true);
            }
            if ($quote->status !== 'aprovado') {
                $quote->approved_at = now();
                $quote->addEvent('aprovado', 'Confirmado pela loja');
                $quote->save();
            }
            $sale = QuoteService::convertToSale($quote->fresh());
            session()->flash('success', 'Orçamento ' . $quote->code() . ' virou a venda #' . $sale->id . '.');

            return $this->redirectRoute('sales.show', $sale->id, navigate: true);
        }

        return $then === 'stay' ? null : $this->redirectRoute('orcamentos.show', $quote->id, navigate: true);
    }

    public function render()
    {
        // Dropdown de cliente (igual ao criar venda): sem busca mostra os mais recentes.
        $clientResults = collect();
        if (!$this->clientId) {
            $cq = Client::where('user_id', Auth::id());
            if (($cs = trim($this->clientSearch)) !== '') {
                $cq->where(fn ($q) => $q->where('name', 'like', "%$cs%")->orWhere('phone', 'like', "%$cs%"))->orderBy('name');
            } else {
                $cq->latest('updated_at');
            }
            $clientResults = $cq->limit(30)->get(['id', 'name', 'phone']);
        }

        $productResults = collect();
        if ($this->productFor !== null) {
            $q = Product::where('user_id', Auth::id())->where(fn ($w) => $w->whereNull('parent_id')->orWhere('parent_id', 0));
            if (($s = trim($this->productSearch)) !== '') {
                $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('product_code', 'like', "%$s%"));
            }
            $productResults = $q->orderByDesc('made_to_order')->orderBy('name')->limit(12)->get(['id', 'name', 'product_code', 'image', 'price', 'price_sale', 'made_to_order']);
        }

        $materials = collect();
        if ($this->materialFor !== null) {
            $q = QuoteMaterial::where('user_id', Auth::id());
            if (($s = trim($this->materialSearch)) !== '') {
                foreach (preg_split('/\s+/', $s) as $w) {
                    $q->where(fn ($x) => $x->where('name', 'like', "%$w%")->orWhere('category', 'like', "%$w%"));
                }
            }
            $materials = $q->orderBy('position')->limit(60)->get()->groupBy('category');
            if (!QuoteMaterial::where('user_id', Auth::id())->exists()) {
                QuoteService::materials(Auth::id());
            }
        }

        $sub = 0;
        $cost = 0;
        foreach ($this->items as $it) {
            $qty = max(1, (int) ($it['qty'] ?? 1));
            $sub += (float) ($it['price'] ?? 0) * $qty;
            $cost += QuoteService::itemCost($it) * $qty;
        }
        $total = max(0, $sub - (float) ($this->discount ?: 0) + (float) ($this->deliveryFee ?: 0));

        $stale = [];
        if ($this->quoteId) {
            $tmp = new Quote(['user_id' => Auth::id(), 'items' => $this->items]);
            $stale = QuoteService::staleCosts($tmp);
        }

        $quote = $this->quoteId ? Quote::where('user_id', Auth::id())->find($this->quoteId) : null;

        return view('livewire.quotes.quote-editor', [
            'client' => $this->clientId ? Client::where('user_id', Auth::id())->find($this->clientId) : null,
            'clientResults' => $clientResults,
            'productResults' => $productResults,
            'materials' => $materials,
            'subtotal' => round($sub, 2),
            'costTotal' => round($cost, 2),
            'total' => round($total, 2),
            'profit' => round($sub - (float) ($this->discount ?: 0) - $cost, 2),
            'stale' => $stale,
            'quote' => $quote,
            'serviceOptions' => QuoteDefaults::REPAIR_SERVICES,
        ])->title($this->quoteId ? 'Editar orçamento' : 'Novo orçamento');
    }
}
