<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InvoiceUploadHistory extends Model
{
    use HasFactory;

    protected $table = 'invoice_uploads_history';

    protected $fillable = [
        'user_id',
        'bank_id',
        'filename',
        'file_path',
        'file_type',
        'total_transactions',
        'transactions_created',
        'transactions_updated',
        'transactions_skipped',
        'total_value',
        'status',
        'error_message',
        'summary',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_transactions' => 'integer',
        'transactions_created' => 'integer',
        'transactions_updated' => 'integer',
        'transactions_skipped' => 'integer',
        'total_value' => 'decimal:2',
        'summary' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relacionamento com usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com banco
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id_bank');
    }

    /**
     * Scope para uploads do usuário e banco específico
     */
    public function scopeForUserAndBank($query, $userId, $bankId)
    {
        return $query->where('user_id', $userId)->where('bank_id', $bankId);
    }

    /**
     * Scope para uploads recentes
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Obter duração formatada
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->started_at || !$this->completed_at) {
            return 'N/A';
        }

        $seconds = $this->completed_at->diffInSeconds($this->started_at);

        if ($seconds < 60) {
            return $seconds . 's';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return $minutes . 'm ' . $remainingSeconds . 's';
    }

    /**
     * Obter taxa de sucesso
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_transactions == 0) {
            return 0;
        }

        $successful = $this->transactions_created + $this->transactions_updated;
        return round(($successful / $this->total_transactions) * 100, 1);
    }

    /**
     * Obter badge de status
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'processing' => [
                'label' => 'Processando',
                'color' => 'yellow',
                'icon' => 'bi-hourglass-split'
            ],
            'completed' => [
                'label' => 'Completo',
                'color' => 'green',
                'icon' => 'bi-check-circle-fill'
            ],
            'failed' => [
                'label' => 'Falhou',
                'color' => 'red',
                'icon' => 'bi-x-circle-fill'
            ],
            default => [
                'label' => 'Desconhecido',
                'color' => 'gray',
                'icon' => 'bi-question-circle'
            ]
        };
    }

    /**
     * Obter valor total formatado
     */
    public function getFormattedTotalValueAttribute()
    {
        return 'R$ ' . number_format($this->total_value ?? 0, 2, ',', '.');
    }
}
