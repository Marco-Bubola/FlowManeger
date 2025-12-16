<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductUploadHistory extends Model
{
    use HasFactory;

    protected $table = 'product_uploads_history';

    protected $fillable = [
        'user_id',
        'filename',
        'file_type',
        'total_products',
        'products_created',
        'products_updated',
        'products_skipped',
        'status',
        'error_message',
        'summary',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_products' => 'integer',
        'products_created' => 'integer',
        'products_updated' => 'integer',
        'products_skipped' => 'integer',
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
     * Scope para uploads do usuário atual
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para uploads recentes
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope para uploads completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para uploads com erro
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Calcula duração do upload em segundos
     */
    public function getDurationAttribute()
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInSeconds($this->completed_at);
        }
        return null;
    }

    /**
     * Formata duração legível
     */
    public function getFormattedDurationAttribute()
    {
        $duration = $this->duration;
        if (!$duration) {
            return 'N/A';
        }

        if ($duration < 60) {
            return $duration . 's';
        }

        $minutes = floor($duration / 60);
        $seconds = $duration % 60;
        return "{$minutes}m {$seconds}s";
    }

    /**
     * Badge de status com cor
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'processing' => ['label' => 'Processando', 'color' => 'blue', 'icon' => 'bi-arrow-clockwise'],
            'completed' => ['label' => 'Concluído', 'color' => 'green', 'icon' => 'bi-check-circle-fill'],
            'failed' => ['label' => 'Erro', 'color' => 'red', 'icon' => 'bi-x-circle-fill'],
            default => ['label' => 'Desconhecido', 'color' => 'gray', 'icon' => 'bi-question-circle'],
        };
    }

    /**
     * Taxa de sucesso
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_products == 0) {
            return 0;
        }

        $successful = $this->products_created + $this->products_updated;
        return round(($successful / $this->total_products) * 100, 2);
    }
}
