<?php

namespace App\Models;

use App\Models\SalePayment as ModelsSalePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SalePayment;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'total_price',
        'amount_paid',
        'status', // pendente, orcamento, confirmada, concluida, cancelada
        'payment_method',
        'tipo_pagamento', // a_vista, parcelado
        'parcelas', // int
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'parcelas' => 'integer',
        'tipo_pagamento' => 'string',
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
}
