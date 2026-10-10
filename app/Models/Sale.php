<?php

namespace App\Models;

use App\Models\SalePayment as ModelsSalePayment;
use App\Traits\HasTeamScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SalePayment;

class Sale extends Model
{
    use HasFactory;
    use HasTeamScope;

    protected string $teamScopeModule = 'sales';

    protected $fillable = [
        'client_id',
        'user_id',
        'total_price',
        'amount_paid',
        'status', // pendente, orcamento, confirmada, concluida, cancelada
        'stock_applied', // estoque já debitado? (true quando confirmada)
        'payment_method',
        'tipo_pagamento', // a_vista, parcelado
        'parcelas', // int
        'source', // e.g. 'portal'
        'portal_quote_id',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'parcelas' => 'integer',
        'tipo_pagamento' => 'string',
        'stock_applied' => 'boolean',
    ];
    /**
     * status: pendente, orcamento, confirmada, concluida, cancelada
     * tipo_pagamento: a_vista, parcelado
     * parcelas: int
     */

    // Relacionamento com o Cliente
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relacionamento com os Itens da Venda (Produtos)
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // Relacionamento com os Pagamentos da Venda
    public function payments()
    {
        return $this->hasMany(ModelsSalePayment::class);
    }

    // Relacionamento com as parcelas da venda
    public function parcelasVenda()
    {
        return $this->hasMany(VendaParcela::class, 'sale_id');
    }

    // Método para calcular o total pago
    public function getTotalPaidAttribute()
    {
        // Excluir pagamentos registrados como 'desconto' do total pago,
        // pois desconto reduz o preço e não deve contar como pagamento efetivo.
        return $this->payments()->where('payment_method', '<>', 'desconto')->sum('amount_paid');
    }

    // Método para calcular o saldo restante
    public function getRemainingAmountAttribute()
    {
        return max(0, $this->total_price - $this->total_paid);
    }

    // Método para recalcular o total da venda baseado nos itens
    public function calculateTotal()
    {
    $total = $this->saleItems()->sum(DB::raw('quantity * price_sale'));
        $this->update(['total_price' => $total]);
        return $total;
    }

    /*
    |--------------------------------------------------------------------------
    | ESTOQUE NA CONFIRMAÇÃO + SYNC MERCADO LIVRE
    |--------------------------------------------------------------------------
    | O estoque só é debitado quando a venda é confirmada (paga/quitada).
    | Idempotente via flag `stock_applied`. Após mexer no estoque, dispara
    | a sincronização das publicações ML afetadas (App→ML).
    */

    /**
     * Quando o estoque baixa: true = ao criar a venda (mesmo pendente);
     * false = só quando a venda fica paga.
     */
    public const STOCK_ON_CREATE = true;

    /** Baixa o estoque se a regra acima pede (chamar depois de criar os itens). */
    public function applyStockIfDue(): void
    {
        if (self::STOCK_ON_CREATE || $this->status === 'pago') {
            $this->applyStockDecrement();
        }
    }

    /** Venda quitada que ainda não tirou do estoque. */
    public function applyStockIfPaid(): void
    {
        if ($this->status === 'pago') {
            $this->applyStockDecrement();
        }
    }

    /**
     * Debita o estoque dos itens da venda (uma única vez).
     */
    public function applyStockDecrement(): void
    {
        if ($this->stock_applied) {
            return;
        }

        DB::transaction(function () {
            $this->load('saleItems.product');

            foreach ($this->saleItems as $item) {
                $product = $item->product ?: Product::find($item->product_id);
                if (!$product) {
                    continue;
                }

                if (($product->tipo ?? 'simples') === 'kit') {
                    foreach ($product->componentes()->get() as $pc) {
                        $comp = $pc->componente()->first();
                        if (!$comp) {
                            continue;
                        }
                        $need = max(0, (int) ($pc->quantidade ?? 0) * (int) $item->quantity);
                        $comp->update(['stock_quantity' => max(0, (int) $comp->stock_quantity - $need)]);
                    }
                } else {
                    $product->update(['stock_quantity' => max(0, (int) $product->stock_quantity - (int) $item->quantity)]);
                }
            }

            $this->forceFill(['stock_applied' => true])->save();
        });

        $this->dispatchMlStockSync();

        // Alerta de estoque baixo/esgotado (nunca pode quebrar a venda)
        try {
            app(\App\Services\Stock\LowStockService::class)->checkProducts(
                (int) $this->user_id,
                $this->saleItems->pluck('product_id')->filter()->all()
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Falha ao verificar estoque baixo da venda', [
                'sale_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Devolve o estoque (cancelamento/exclusão), só se tiver sido aplicado.
     */
    public function restoreStock(): void
    {
        if (!$this->stock_applied) {
            return;
        }

        DB::transaction(function () {
            $this->load('saleItems.product');

            foreach ($this->saleItems as $item) {
                $product = $item->product ?: Product::find($item->product_id);
                if (!$product) {
                    continue;
                }

                if (($product->tipo ?? 'simples') === 'kit') {
                    foreach ($product->componentes()->get() as $pc) {
                        $comp = $pc->componente()->first();
                        if (!$comp) {
                            continue;
                        }
                        $back = max(0, (int) ($pc->quantidade ?? 0) * (int) $item->quantity);
                        $comp->increment('stock_quantity', $back);
                    }
                } else {
                    $product->increment('stock_quantity', (int) $item->quantity);
                }
            }

            $this->forceFill(['stock_applied' => false])->save();
        });

        $this->dispatchMlStockSync();
    }

    /**
     * Dispara o sync de estoque App→ML para as publicações que contêm os
     * produtos (ou componentes de kit) desta venda.
     */
    public function dispatchMlStockSync(): void
    {
        try {
            $this->loadMissing('saleItems.product');

            $affected = collect();
            foreach ($this->saleItems as $item) {
                $product = $item->product ?: Product::find($item->product_id);
                if (!$product) {
                    continue;
                }
                if (($product->tipo ?? 'simples') === 'kit') {
                    // Publicações que contêm o próprio kit também precisam ressincronizar
                    $affected->push($product->id);
                    foreach ($product->componentes()->get() as $pc) {
                        if ($pc->componente_produto_id) {
                            $affected->push($pc->componente_produto_id);
                        }
                    }
                } else {
                    $affected->push($product->id);
                }
            }

            // Kits que usam algum produto afetado mudam de disponibilidade também
            $affected = $affected->merge(
                \App\Models\ProdutoComponente::whereIn('componente_produto_id', $affected->filter()->all())
                    ->pluck('kit_produto_id')
            );

            $affected = $affected->filter()->unique()->values();
            if ($affected->isEmpty()) {
                return;
            }

            \App\Models\MlPublication::query()
                ->where('user_id', $this->user_id)
                ->usingProducts($affected->all())
                ->get()
                ->each(fn ($pub) => \App\Jobs\SyncPublicationToMercadoLivre::dispatch($pub));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Falha ao despachar sync ML da venda', [
                'sale_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
