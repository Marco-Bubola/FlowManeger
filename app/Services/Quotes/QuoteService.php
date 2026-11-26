<?php

namespace App\Services\Quotes;

use App\Models\Category;
use App\Models\Client;
use App\Models\ClientTask;
use App\Models\ConsortiumNotification;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteMaterial;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\Clients\ClientContact;
use App\Services\Products\VariationService;
use App\Support\Quotes\QuoteDefaults;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Regras da área de Orçamentos: configurações, tabela de materiais, contas de
 * custo e preço, envio, aprovação e "virar venda".
 */
class QuoteService
{
    public const TZ = ConsortiumNotification::DISPLAY_TZ;

    // ------------------------------------------------------------------ configurações

    /** users.preferences.quotes, completado com QuoteDefaults::SETTINGS. */
    public static function settings(User|int|null $user): array
    {
        $user = $user instanceof User ? $user : ($user ? User::find($user) : null);
        $saved = (array) data_get($user?->preferences ?? [], 'quotes', []);
        $s = array_merge(QuoteDefaults::SETTINGS, array_intersect_key($saved, QuoteDefaults::SETTINGS));
        $s['margin'] = max(0, min(90, (float) $s['margin']));
        $s['validity_days'] = max(1, min(180, (int) $s['validity_days']));
        $s['reply_days'] = max(1, min(30, (int) $s['reply_days']));
        $s['min_markup'] = max(0, min(100, (float) $s['min_markup']));
        $s['extras'] = array_values(array_filter(array_map(fn ($e) => [
            'n' => mb_substr(trim((string) ($e['n'] ?? '')), 0, 80),
            'v' => round((float) ($e['v'] ?? 0), 2),
        ], (array) $s['extras']), fn ($e) => $e['n'] !== ''));
        $s['colors'] = self::colorGroups($s['colors']);

        return $s;
    }

    public static function saveSettings(User $user, array $values): void
    {
        $prefs = $user->preferences ?? [];
        $prefs['quotes'] = array_merge((array) ($prefs['quotes'] ?? []), array_intersect_key($values, QuoteDefaults::SETTINGS));
        $user->preferences = $prefs;
        $user->save();
    }

    /** Grupos de cor que o portal oferece: [{key, name, items: [[nome, hex]]}]. */
    public static function colorGroups($groups): array
    {
        $groups = is_array($groups) && $groups ? $groups : QuoteDefaults::COLOR_GROUPS;

        return array_values(array_filter(array_map(function ($g) {
            $items = array_values(array_filter(array_map(fn ($i) => [
                mb_substr(trim((string) ($i[0] ?? '')), 0, 40),
                preg_match('/^#[0-9a-f]{6}$/i', (string) ($i[1] ?? '')) ? strtolower($i[1]) : '#cccccc',
            ], (array) ($g['items'] ?? [])), fn ($i) => $i[0] !== ''));

            return [
                'key' => Str::slug((string) ($g['key'] ?? $g['name'] ?? '')) ?: Str::random(6),
                'name' => mb_substr(trim((string) ($g['name'] ?? '')), 0, 60),
                'items' => $items,
            ];
        }, $groups), fn ($g) => $g['name'] !== '' && $g['items']));
    }

    // ------------------------------------------------------------------ materiais e horas

    /** Tabela da pessoa; na primeira vez nasce com a do robô. */
    public static function materials(int $userId)
    {
        if (!QuoteMaterial::where('user_id', $userId)->exists()) {
            self::seedMaterials($userId);
        }

        return QuoteMaterial::where('user_id', $userId)->orderBy('position')->orderBy('id')->get();
    }

    public static function seedMaterials(int $userId): int
    {
        $now = now();
        $rows = [];
        foreach (QuoteDefaults::MATERIALS as $i => [$cat, $name, $unit, $price, $note]) {
            $rows[] = [
                'user_id' => $userId, 'category' => $cat, 'name' => $name, 'unit' => $unit, 'price' => $price,
                'note' => $note ?: null, 'estimated' => true, 'position' => ($i + 1) * 10, 'created_at' => $now, 'updated_at' => $now,
            ];
        }
        foreach (array_chunk($rows, 100) as $chunk) {
            QuoteMaterial::insert($chunk);
        }

        return count($rows);
    }

    // ------------------------------------------------------------------ contas

    public static function money(float $v): string
    {
        return 'R$ ' . number_format($v, 2, ',', '.');
    }

    /** Custo de UMA unidade do item: materiais + horas + linhas livres. */
    public static function itemCost(array $item): float
    {
        return round(collect($item['costs'] ?? [])->sum(fn ($c) => (float) ($c['q'] ?? 0) * (float) ($c['v'] ?? 0)), 2);
    }

    public static function itemExtras(array $item): float
    {
        return round(collect($item['extras'] ?? [])->sum(fn ($e) => (float) ($e['v'] ?? 0)), 2);
    }

    /** Preço sugerido: margem sobre o preço (30% = 30% do preço é lucro) + acréscimos, arredondado para cima. */
    public static function suggestedPrice(float $cost, float $marginPct, float $extras = 0): float
    {
        if ($cost <= 0) {
            return round($extras, 2);
        }
        $m = max(0, min(90, $marginPct)) / 100;

        return (float) ceil($cost / (1 - $m)) + round($extras, 2);
    }

    /** Lucro e % de um item com o preço que ficou. */
    public static function itemProfit(array $item): array
    {
        $cost = self::itemCost($item);
        $price = (float) ($item['price'] ?? 0);
        $profit = round($price - $cost, 2);

        return ['cost' => $cost, 'price' => $price, 'profit' => $profit, 'pct' => $price > 0 ? round($profit / $price * 100, 1) : 0.0];
    }

    /** Recalcula total e custo total (não salva). */
    public static function recalc(Quote $quote): Quote
    {
        $sub = 0;
        $cost = 0;
        foreach ($quote->items ?? [] as $it) {
            $qty = max(1, (int) ($it['qty'] ?? 1));
            $sub += (float) ($it['price'] ?? 0) * $qty;
            $cost += self::itemCost($it) * $qty;
        }
        $quote->total = max(0, round($sub - (float) $quote->discount + (float) $quote->delivery_fee, 2));
        $quote->cost_total = round($cost, 2);

        return $quote;
    }

    /** Itens cujo custo mudou porque o preço do material mudou na tabela depois. */
    public static function staleCosts(Quote $quote): array
    {
        $ids = collect($quote->items ?? [])->flatMap(fn ($it) => collect($it['costs'] ?? [])->pluck('mid'))->filter()->unique();
        if ($ids->isEmpty()) {
            return [];
        }
        $now = QuoteMaterial::where('user_id', $quote->user_id)->whereIn('id', $ids)->pluck('price', 'id');
        $out = [];
        foreach ($quote->items ?? [] as $i => $it) {
            foreach ($it['costs'] ?? [] as $c) {
                $mid = $c['mid'] ?? null;
                if ($mid && isset($now[$mid]) && abs((float) $now[$mid] - (float) ($c['v'] ?? 0)) >= 0.01) {
                    $out[$i][] = ['n' => $c['n'] ?? '', 'old' => (float) ($c['v'] ?? 0), 'new' => (float) $now[$mid]];
                }
            }
        }

        return $out;
    }

    // ------------------------------------------------------------------ criar

    public static function nextNumber(int $userId): int
    {
        return (int) Quote::withTrashed()->where('user_id', $userId)->max('number') + 1;
    }

    public static function newToken(): string
    {
        do {
            $t = Str::random(32);
        } while (Quote::withTrashed()->where('token', $t)->exists());

        return $t;
    }

    public static function make(int $userId, array $attrs): Quote
    {
        $s = self::settings($userId);
        $quote = new Quote(array_merge([
            'user_id' => $userId,
            'kind' => 'produto',
            'status' => 'rascunho',
            'source' => 'app',
            'items' => [],
            'valid_until' => Carbon::today(self::TZ)->addDays($s['validity_days'])->toDateString(),
        ], $attrs));
        $quote->user_id = $userId;
        $quote->token = self::newToken();
        for ($try = 0; $try < 5; $try++) {
            $quote->number = self::nextNumber($userId);
            try {
                $quote->save();
                break;
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                if ($try === 4) {
                    throw $e;
                }
            }
        }

        return $quote;
    }

    /** Item novo, vazio ou a partir de um produto do estoque (puxa foto, preço e ficha de custo). */
    public static function newItem(?Product $product = null, string $type = 'livre', float $margin = 30): array
    {
        $item = [
            'key' => Str::random(8), 'type' => $type, 'product_id' => null, 'name' => '', 'description' => '',
            'qty' => 1, 'photos' => [], 'costs' => [], 'extras' => [], 'margin' => $margin, 'price' => 0, 'manual' => false,
            'attrs' => ['cor' => '', 'medida' => ''],
        ];
        if ($product) {
            $item['product_id'] = $product->id;
            $item['type'] = $type === 'livre' ? 'estoque' : $type;
            $item['name'] = (string) $product->name;
            $item['description'] = mb_substr(trim(strip_tags((string) $product->description)), 0, 600);
            if ($product->image && $product->image !== 'product-placeholder.png') {
                $item['photos'] = ['products/' . basename((string) $product->image)];
            }
            foreach ((array) ($product->cost_items ?? []) as $c) {
                $n = trim((string) ($c['n'] ?? $c['item'] ?? ''));
                if ($n === '') {
                    continue;
                }
                $item['costs'][] = ['mid' => null, 'n' => $n, 'u' => '', 'q' => (float) ($c['q'] ?? $c['quantidade'] ?? 1), 'v' => (float) ($c['v'] ?? $c['valor'] ?? 0)];
            }
            if (!$item['costs'] && (float) $product->price > 0) {
                $item['costs'][] = ['mid' => null, 'n' => 'Custo do produto', 'u' => 'un', 'q' => 1, 'v' => (float) $product->price];
            }
            if ($type === 'estoque') {
                $item['price'] = (float) $product->price_sale;
                $item['manual'] = true;
            }
            if ($product->production_days) {
                $item['days'] = (int) $product->production_days;
            }
        }

        return $item;
    }

    /** Foto de orçamento: reduz para no máximo 1600 px (JPG) e guarda no disco público. */
    public static function storePhoto(UploadedFile $file, int $userId, string $folder = 'quotes'): ?string
    {
        try {
            $dir = trim($folder, '/') . '/' . $userId;
            $name = Str::random(20) . '.jpg';
            $src = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
            if (!$src) {
                $path = $file->store($dir, 'public');

                return $path ?: null;
            }
            $w = imagesx($src);
            $h = imagesy($src);
            $scale = min(1, 1600 / max($w, $h));
            $nw = max(1, (int) round($w * $scale));
            $nh = max(1, (int) round($h * $scale));
            $dst = imagecreatetruecolor($nw, $nh);
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            ob_start();
            imagejpeg($dst, null, 82);
            Storage::disk('public')->put($dir . '/' . $name, (string) ob_get_clean());
            imagedestroy($src);
            imagedestroy($dst);

            return $dir . '/' . $name;
        } catch (\Throwable $e) {
            Log::warning('Foto de orçamento não salva', ['error' => $e->getMessage()]);

            return null;
        }
    }

    // ------------------------------------------------------------------ envio e resposta

    public static function whatsappText(Quote $quote): string
    {
        $store = $quote->user?->name ?: 'a loja';
        $first = trim(explode(' ', $quote->customerName())[0] ?? '');
        $lines = [($first && $first !== 'Sem' ? "Olá, {$first}!" : 'Olá!') . " Segue o seu orçamento {$quote->code()}" . ($quote->title ? " ({$quote->title})" : '') . ':'];
        foreach ($quote->publicItems() as $it) {
            $lines[] = '• ' . ($it['qty'] > 1 ? $it['qty'] . 'x ' : '') . $it['name'] . ' — ' . self::money($it['line']);
        }
        $lines[] = 'Total: ' . self::money((float) $quote->total);
        if ($quote->production_days) {
            $lines[] = "Prazo: {$quote->production_days} dias para ficar pronto.";
        }
        if ($quote->valid_until) {
            $lines[] = 'Válido até ' . $quote->valid_until->format('d/m/Y') . '.';
        }
        $lines[] = '';
        $lines[] = 'Veja com fotos e aprove por aqui: ' . $quote->publicUrl();

        return implode("\n", $lines);
    }

    public static function whatsappUrl(Quote $quote, ?string $text = null): ?string
    {
        return ClientContact::whatsappUrl($quote->customerPhone(), $text ?? self::whatsappText($quote));
    }

    /** Marca como enviado (WhatsApp, link copiado ou PDF). Rascunho e pedido viram "Enviado". */
    public static function markSent(Quote $quote, string $how): Quote
    {
        if (in_array($quote->status, ['pedido', 'rascunho', 'vencido'], true)) {
            $quote->status = 'enviado';
            $quote->edited_after_sent_at = null;
        }
        $quote->sent_at = now();
        $quote->addEvent('enviado', 'Enviado ' . $how);
        $quote->save();

        return $quote;
    }

    /** O cliente abriu o link (só a primeira vez entra na linha do tempo). */
    public static function markViewed(Quote $quote): void
    {
        if ($quote->viewed_at && (!$quote->edited_after_sent_at || $quote->viewed_at->gte($quote->edited_after_sent_at))) {
            return;
        }
        $quote->viewed_at = now();
        $quote->addEvent('visto', 'O cliente abriu o orçamento');
        $quote->saveQuietly();
    }

    public static function approve(Quote $quote, string $by = 'cliente'): bool
    {
        if (!$quote->canBeApproved() && $by === 'cliente') {
            return false;
        }
        $quote->status = 'aprovado';
        $quote->approved_at = now();
        $quote->change_request = null;
        $quote->addEvent('aprovado', $by === 'cliente' ? 'O cliente aprovou' : 'Marcado como aprovado');
        $quote->save();

        if ($by === 'cliente') {
            ConsortiumNotification::createGeneric('quotes', 'quote_approved', $quote->user_id,
                'Orçamento aprovado: ' . $quote->code(),
                $quote->customerName() . ' aprovou ' . $quote->displayTitle() . ' (' . self::money((float) $quote->total) . '). Toque para virar venda.',
                ['action_url' => route('orcamentos.show', $quote->id), 'priority' => 'high', 'entity_type' => 'quote', 'entity_id' => $quote->id, 'force' => true]);
        }

        return true;
    }

    public static function requestChange(Quote $quote, string $text): void
    {
        $text = mb_substr(trim($text), 0, 1000);
        $quote->change_request = $text;
        $quote->addEvent('mudanca', 'O cliente pediu mudança: ' . $text);
        $quote->save();
        ConsortiumNotification::createGeneric('quotes', 'quote_change', $quote->user_id,
            'Pedido de mudança: ' . $quote->code(),
            $quote->customerName() . ': ' . Str::limit($text, 140),
            ['action_url' => route('orcamentos.edit', $quote->id), 'priority' => 'high', 'entity_type' => 'quote', 'entity_id' => $quote->id, 'force' => true]);
    }

    public static function reject(Quote $quote, string $by = 'loja', string $reason = ''): void
    {
        $quote->status = 'recusado';
        $quote->rejected_at = now();
        $quote->addEvent('recusado', ($by === 'cliente' ? 'O cliente recusou' : 'Marcado como recusado') . ($reason !== '' ? ': ' . mb_substr($reason, 0, 300) : ''));
        $quote->save();
        if ($by === 'cliente') {
            ConsortiumNotification::createGeneric('quotes', 'quote_rejected', $quote->user_id,
                'Orçamento recusado: ' . $quote->code(), $quote->customerName() . ' não aprovou ' . $quote->displayTitle() . ($reason !== '' ? ': ' . Str::limit($reason, 120) : '.'),
                ['action_url' => route('orcamentos.show', $quote->id), 'entity_type' => 'quote', 'entity_id' => $quote->id]);
        }
    }

    /** Pedido que chegou pelo portal: avisa no sino. */
    public static function notifyNewRequest(Quote $quote): void
    {
        ConsortiumNotification::createGeneric('quotes', 'quote_request', $quote->user_id,
            'Pedido de orçamento: ' . $quote->customerName(),
            ($quote->kind === 'reforma' ? 'Reforma' : 'Produto') . ' — ' . Str::limit((string) ($quote->request_text ?: $quote->displayTitle()), 140),
            ['action_url' => route('orcamentos.edit', $quote->id), 'priority' => 'high', 'entity_type' => 'quote', 'entity_id' => $quote->id, 'force' => true]);
    }

    // ------------------------------------------------------------------ virar venda

    /**
     * Cria a venda do orçamento. Item do estoque entra como ele é; variação nova e
     * peça livre viram um produto sob encomenda (inativo, não aparece no catálogo),
     * com a ficha de custo do orçamento. Reforma entra como serviço do mesmo jeito.
     */
    public static function convertToSale(Quote $quote): Sale
    {
        if ($quote->sale_id && ($sale = Sale::find($quote->sale_id))) {
            return $sale;
        }

        return DB::transaction(function () use ($quote) {
            $client = $quote->client ?: self::clientFor($quote);
            $quote->client_id = $client->id;

            $sale = Sale::create([
                'client_id' => $client->id,
                'user_id' => $quote->user_id,
                'total_price' => (float) $quote->total,
                'amount_paid' => 0,
                'status' => 'pendente',
                'payment_method' => 'dinheiro',
                'tipo_pagamento' => 'a_vista',
                'parcelas' => 1,
                'source' => 'orcamento',
            ]);

            $items = $quote->items ?? [];
            $sum = 0;
            foreach ($items as $i => $it) {
                if (trim((string) ($it['name'] ?? '')) === '') {
                    continue;
                }
                $product = self::productForItem($quote, $it);
                $items[$i]['sale_product_id'] = $product->id;
                $qty = max(1, (int) ($it['qty'] ?? 1));
                $price = round((float) ($it['price'] ?? 0), 2);
                $sum += $price * $qty;
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $product->price ?? self::itemCost($it),
                    'price_sale' => $price,
                ]);
            }

            // Desconto e entrega do orçamento ficam no total da venda.
            $sale->forceFill(['total_price' => (float) $quote->total])->save();
            $sale->applyStockIfDue();

            $quote->items = $items;
            $quote->status = 'convertido';
            $quote->sale_id = $sale->id;
            $quote->addEvent('venda', 'Virou a venda #' . $sale->id);
            $quote->save();

            // Tarefa de produção com o prazo do orçamento.
            if (class_exists(ClientTask::class)) {
                try {
                    $days = (int) ($quote->production_days ?: 0);
                    ClientTask::create([
                        'user_id' => $quote->user_id,
                        'client_id' => $client->id,
                        'title' => mb_substr(($quote->kind === 'reforma' ? 'Entregar reforma ' : 'Entregar ') . $quote->code() . ' — ' . $quote->displayTitle(), 0, 160),
                        'notes' => $quote->kind === 'reforma' ? 'Quando terminar, coloque as fotos de depois no orçamento.' : null,
                        'due_date' => Carbon::today(self::TZ)->addDays(max(1, $days ?: 7))->toDateString(),
                    ]);
                } catch (\Throwable $e) {
                    Log::info('Tarefa de produção do orçamento não criada', ['quote' => $quote->id, 'error' => $e->getMessage()]);
                }
            }

            return $sale;
        });
    }

    public static function clientFor(Quote $quote): Client
    {
        $phone = preg_replace('/\D+/', '', (string) $quote->contact_phone);
        if ($phone !== '') {
            $found = Client::where('user_id', $quote->user_id)->get(['id', 'phone'])
                ->first(fn ($c) => preg_replace('/\D+/', '', (string) $c->phone) === $phone);
            if ($found) {
                return Client::find($found->id);
            }
        }

        return Client::create([
            'user_id' => $quote->user_id,
            'name' => mb_substr($quote->contact_name ?: 'Cliente do orçamento ' . $quote->code(), 0, 100),
            'phone' => $quote->contact_phone ? mb_substr($quote->contact_phone, 0, 16) : null,
        ]);
    }

    protected static function productForItem(Quote $quote, array $it): Product
    {
        $base = !empty($it['product_id'])
            ? Product::withoutGlobalScopes()->where('user_id', $quote->user_id)->find($it['product_id'])
            : null;

        if ($base && ($it['type'] ?? '') === 'estoque') {
            return $base;
        }

        $costs = collect($it['costs'] ?? [])->map(fn ($c) => ['n' => (string) ($c['n'] ?? ''), 'q' => (float) ($c['q'] ?? 1), 'v' => (float) ($c['v'] ?? 0)])->values()->all();
        $photo = collect($it['photos'] ?? [])->first();
        $image = $base?->image ?: 'product-placeholder.png';
        if ($photo && !str_starts_with($photo, 'products/') && Storage::disk('public')->exists($photo)) {
            $image = basename($photo);
            Storage::disk('public')->copy($photo, 'products/' . $image);
        }
        $data = [
            'user_id' => $quote->user_id,
            'name' => mb_substr(trim((string) $it['name']), 0, 255),
            'description' => trim((string) ($it['description'] ?? '')) ?: null,
            'price' => self::itemCost($it),
            'price_sale' => round((float) ($it['price'] ?? 0), 2),
            'stock_quantity' => 0,
            'status' => 'inativo',
            'tipo' => 'simples',
            'image' => $image,
            'made_to_order' => true,
            'production_days' => $quote->production_days ?: null,
            'cost_items' => $costs,
            'custos_adicionais' => 0,
        ];

        $variations = app(VariationService::class);
        if ($base && ($it['type'] ?? '') === 'variacao') {
            $root = $base->parent_id ? (Product::withoutGlobalScopes()->find($base->parent_id) ?: $base) : $base;
            $label = trim(implode(' · ', array_filter([(string) ($it['attrs']['cor'] ?? ''), (string) ($it['attrs']['medida'] ?? '')]))) ?: $data['name'];

            return $variations->createVariant($root, $data, mb_substr($label, 0, 100));
        }

        $data['category_id'] = $base?->category_id ?: self::category($quote->user_id, $quote->kind === 'reforma' ? 'Reformas' : 'Sob medida');
        $data['product_code'] = $variations->generateCode();

        return Product::create($data);
    }

    protected static function category(int $userId, string $name): int
    {
        $cat = Category::where('user_id', $userId)->where('type', 'product')->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

        return (int) ($cat ?? Category::create([
            'user_id' => $userId, 'name' => $name, 'type' => 'product', 'is_active' => 1,
            'icone' => $name === 'Reformas' ? 'bi-tools' : 'bi-rulers', 'hexcolor_category' => '#6366f1',
        ]))->id_category;
    }
}
