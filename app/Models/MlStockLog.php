<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MlStockLog extends Model
{
    use HasFactory;

    protected $table = 'ml_stock_logs';

    public $timestamps = false; // Apenas created_at

    protected $fillable = [
        'product_id',
        'ml_publication_id',
        'operation_type',
        'quantity_before',
        'quantity_after',
        'quantity_change',
        'source',
        'ml_order_id',
        'notes',
        'transaction_id',
        'rolled_back',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'rolled_back' => 'boolean',
        'created_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    /**
     * Produto afetado
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Publicação ML relacionada
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(MlPublication::class, 'ml_publication_id');
    }

    /**
     * Usuário responsável (se manual)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Logs de um produto específico
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Logs de uma publicação específica
     */
    public function scopeForPublication($query, $publicationId)
    {
        return $query->where('ml_publication_id', $publicationId);
    }

    /**
     * Logs de vendas no ML
     */
    public function scopeMlSales($query)
    {
        return $query->where('operation_type', 'ml_sale');
    }

    /**
     * Logs de atualizações manuais
     */
    public function scopeManualUpdates($query)
    {
        return $query->where('operation_type', 'manual_update');
    }

    /**
     * Logs de uma transação específica
     */
    public function scopeForTransaction($query, $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    /**
     * Logs revertidos
     */
    public function scopeRolledBack($query)
    {
        return $query->where('rolled_back', true);
    }

    /**
     * Logs ativos (não revertidos)
     */
    public function scopeActive($query)
    {
        return $query->where('rolled_back', false);
    }

    /**
     * Logs de um período específico
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    /**
     * Verifica se o log foi revertido
     */
    public function isRolledBack(): bool
    {
        return $this->rolled_back;
    }

    /**
     * Marca este log como revertido
     */
    public function markAsRolledBack(): void
    {
        $this->update(['rolled_back' => true]);
    }

    /**
     * Formata a mudança de quantidade com sinal
     */
    public function getFormattedChangeAttribute(): string
    {
        $change = $this->quantity_change;
        return $change > 0 ? "+{$change}" : (string)$change;
    }

    /**
     * Retorna descrição legível do tipo de operação
     */
    public function getOperationDescription(): string
    {
        return match($this->operation_type) {
            'ml_sale' => 'Venda no Mercado Livre',
            'manual_update' => 'Atualização Manual',
            'import_excel' => 'Importação de Planilha',
            'internal_sale' => 'Venda Interna',
            'sync_to_ml' => 'Sincronização para ML',
            'adjustment' => 'Ajuste Manual',
            'return' => 'Devolução',
            default => 'Operação Desconhecida',
        };
    }

    /**
     * Registra um novo log de forma estática
     * 
     * @param array $data
     * @return self
     */
    public static function logStockChange(array $data): self
    {
        return self::create(array_merge($data, [
            'created_at' => now(),
        ]));
    }

    /**
     * Busca logs conflitantes (operações simultâneas no mesmo produto)
     * Útil para detectar condições de corrida
     */
    public static function findConflicts($productId, $minutesWindow = 1)
    {
        return self::where('product_id', $productId)
            ->where('created_at', '>=', now()->subMinutes($minutesWindow))
            ->where('rolled_back', false)
            ->orderBy('created_at')
            ->get()
            ->groupBy('transaction_id')
            ->filter(function ($group) {
                return $group->count() > 1; // Múltiplas operações na mesma transação
            });
    }
}
