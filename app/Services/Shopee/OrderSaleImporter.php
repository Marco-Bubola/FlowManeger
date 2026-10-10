<?php

namespace App\Services\Shopee;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\ShopeeOrder;
use App\Models\ShopeePublication;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Transforma um pedido Shopee já gravado em uma venda do sistema.
 *
 * O estoque NÃO é baixado aqui: o pedido já baixou (StockSyncService) ou é
 * anterior à baixa automática. A venda nasce com stock_applied = true, como
 * na importação do Mercado Livre, para a quitação não baixar de novo.
 */
class OrderSaleImporter
{
    /**
     * @return array{success: bool, message: string, sale?: Sale}
     */
    public function import(ShopeeOrder $order, int $userId): array
    {
        if ((int) $order->user_id !== $userId) {
            return ['success' => false, 'message' => 'Pedido não pertence a este usuário.'];
        }

        try {
            $sale = DB::transaction(function () use ($order, $userId) {
                // Trava a linha: dois cliques/abas não criam duas vendas
                $order = ShopeeOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();

                if ($order->imported_to_sale_id && Sale::withoutGlobalScopes()->whereKey($order->imported_to_sale_id)->exists()) {
                    throw new \DomainException("Pedido já importado (venda #{$order->imported_to_sale_id}).");
                }
                if (in_array($order->order_status, ['CANCELLED', 'IN_CANCEL'], true)) {
                    throw new \DomainException('Pedido cancelado na Shopee não vira venda.');
                }

                $lines = $this->buildLines($order, $userId);

                $paid = in_array($order->order_status, StockSyncService::PAID_STATUSES, true);
                $total = round((float) $order->total_amount, 2);
                if ($total <= 0) {
                    $total = round($lines->sum(fn ($l) => $l['quantity'] * $l['price_sale']), 2);
                }
                $date = ($order->shopee_created_at ?? $order->created_at ?? now())->copy()->setTimezone(config('app.timezone'));
                $method = self::paymentMethod($order->payment_method);

                $client = $this->findOrCreateClient($order, $userId);

                $sale = Sale::create([
                    'user_id' => $userId,
                    'client_id' => $client->id,
                    'total_price' => $total,
                    'amount_paid' => $paid ? $total : 0,
                    'status' => $paid ? 'pago' : 'pendente',
                    'payment_method' => $method,
                    'tipo_pagamento' => 'a_vista',
                    'parcelas' => 1,
                    'source' => 'shopee',
                ]);

                // Data da venda = data do pedido
                $sale->timestamps = false;
                $sale->created_at = $date;
                $sale->updated_at = $date;
                // O pedido já mexeu no estoque: a venda não baixa de novo
                $sale->stock_applied = true;
                $sale->save();
                $sale->timestamps = true;

                foreach ($lines as $line) {
                    SaleItem::create(['sale_id' => $sale->id] + $line);
                }

                if ($paid) {
                    SalePayment::create([
                        'sale_id' => $sale->id,
                        'amount_paid' => $total,
                        'payment_method' => $method,
                        'payment_date' => $date->format('Y-m-d'),
                    ]);
                }

                $order->imported_to_sale_id = $sale->id;
                $order->save();

                return $sale;
            });
        } catch (\DomainException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (\Throwable $e) {
            Log::error('Shopee: erro ao importar pedido como venda', ['order_sn' => $order->shopee_order_sn, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Erro ao importar pedido: ' . $e->getMessage()];
        }

        return ['success' => true, 'message' => "Pedido importado como venda #{$sale->id}.", 'sale' => $sale];
    }

    /**
     * Itens da venda a partir dos anúncios vinculados (mesma regra de variação
     * da baixa de estoque). Valor de cada anúncio dividido entre os produtos
     * pelo preço de venda, como no ML.
     */
    protected function buildLines(ShopeeOrder $order, int $userId): Collection
    {
        $lines = collect();
        $missing = [];
        $items = self::items($order);

        // Sem preço nos itens (pedido antigo/simplificado): divide o total
        if (array_sum(array_map(fn ($i) => $i['unit'] * $i['qty'], $items)) <= 0 && (float) $order->total_amount > 0) {
            $units = max(1, array_sum(array_column($items, 'qty')));
            foreach ($items as &$i) {
                $i['unit'] = (float) $order->total_amount / $units;
            }
            unset($i);
        }

        foreach ($items as $item) {
            $publication = $item['item_id'] !== ''
                ? ShopeePublication::where('user_id', $userId)->where('shopee_item_id', $item['item_id'])->first()
                : null;
            $products = $publication ? self::productsFor($publication, $item['model_id']) : collect();

            if ($products->isEmpty()) {
                $missing[] = $item['name'] ?: $item['item_id'];
                continue;
            }

            $weights = $products->map(fn ($p) => max(0.01, (float) $p->price_sale) * max(1, (int) $p->pivot->quantity))->values();
            $sum = $weights->sum();
            foreach ($products->values() as $i => $product) {
                $perUnit = max(1, (int) $product->pivot->quantity);
                $share = $item['unit'] * ($weights[$i] / $sum);
                $lines->push([
                    'product_id' => $product->id,
                    'quantity' => $item['qty'] * $perUnit,
                    'price' => $product->price,
                    'price_sale' => round($share / $perUnit, 2),
                ]);
            }
        }

        if ($missing) {
            throw new \DomainException('Anúncio(s) sem produto vinculado: ' . implode(', ', array_slice($missing, 0, 3))
                . '. Vincule o anúncio a um produto antes de importar.');
        }
        if ($lines->isEmpty()) {
            throw new \DomainException('Pedido sem itens para importar.');
        }

        return $lines;
    }

    /** Itens do pedido normalizados. */
    public static function items(ShopeeOrder $order): array
    {
        $raw = is_array($order->order_items) ? $order->order_items : [];
        if (empty($raw) && $order->shopee_item_id) {
            $raw = [['item_id' => $order->shopee_item_id, 'model_id' => $order->shopee_model_id, 'model_quantity_purchased' => 1]];
        }

        return array_map(fn ($i) => [
            'item_id' => (string) ($i['item_id'] ?? ''),
            'model_id' => (string) ($i['model_id'] ?? ''),
            'name' => (string) ($i['item_name'] ?? ''),
            'model_name' => (string) ($i['model_name'] ?? ''),
            'sku' => (string) (($i['model_sku'] ?? '') ?: ($i['item_sku'] ?? '')),
            'qty' => max(1, (int) ($i['model_quantity_purchased'] ?? $i['quantity'] ?? 1)),
            'unit' => (float) (($i['model_discounted_price'] ?? 0) ?: ($i['model_original_price'] ?? 0)),
            'image' => $i['image_info']['image_url'] ?? null,
        ], array_values($raw));
    }

    /** Produtos vinculados ao anúncio para a variação vendida. */
    public static function productsFor(ShopeePublication $publication, string $modelId): Collection
    {
        $products = $publication->linkedProducts();
        if ($modelId !== '' && $modelId !== '0') {
            $byModel = $products->filter(fn ($p) => (string) $p->pivot->shopee_model_id === $modelId);
            if ($byModel->isNotEmpty() || $publication->has_variations) {
                $products = $byModel;
            }
        }

        return $products->values();
    }

    /** Cliente pelo nome do comprador (mesma regra do ML: acha pelo nome ou cria). */
    protected function findOrCreateClient(ShopeeOrder $order, int $userId): Client
    {
        $address = is_array($order->shipping_address) ? $order->shipping_address : [];
        $name = trim((string) ($address['name'] ?? ''));
        if ($name === '' || str_contains($name, '*')) {
            $name = trim((string) $order->buyer_username);
        }
        $name = mb_substr($name !== '' ? $name : 'Cliente Shopee', 0, 100);

        $phone = trim((string) ($order->buyer_phone ?: ($address['phone'] ?? '')));
        if (str_contains($phone, '*')) {
            $phone = ''; // a Shopee mascara o telefone
        }

        $client = null;
        if ($phone !== '') {
            $client = Client::where('user_id', $userId)->where('phone', $phone)->first();
        }

        return $client
            ?? Client::where('user_id', $userId)->where('name', $name)->first()
            ?? Client::create([
                'user_id' => $userId,
                'name' => $name,
                'phone' => $phone !== '' ? mb_substr($phone, 0, 30) : null,
            ]);
    }

    /** Forma de pagamento da Shopee para os valores usados nas vendas. */
    public static function paymentMethod(?string $method): string
    {
        $m = mb_strtolower((string) $method);

        return match (true) {
            str_contains($m, 'pix') => 'pix',
            str_contains($m, 'boleto') => 'boleto',
            str_contains($m, 'debit') || str_contains($m, 'débito') => 'cartao_debito',
            str_contains($m, 'credit') || str_contains($m, 'crédito') || str_contains($m, 'card') || str_contains($m, 'cart') => 'cartao_credito',
            default => 'shopee',
        };
    }
}
