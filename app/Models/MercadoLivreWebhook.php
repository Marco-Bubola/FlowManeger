<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MercadoLivreWebhook extends Model
{
    use HasFactory;

    protected $table = 'mercadolivre_webhooks';

    public $timestamps = false;

    protected $fillable = [
        'topic',
        'resource',
        'ml_user_id',
        'application_id',
        'attempts',
        'sent',
        'received_at',
        'processed',
        'processed_at',
        'raw_data',
        'error_message',
        'http_status',
        'processing_result',
    ];

    protected $casts = [
        'sent' => 'datetime',
        'received_at' => 'datetime',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
        'raw_data' => 'array',
        'processing_result' => 'array',
    ];

    /**
     * Marca como processado
     */
    public function markAsProcessed(array $result = [])
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
            'processing_result' => $result,
            'error_message' => null,
        ]);
    }

    /**
     * Marca como erro e incrementa tentativas
     */
    public function markAsError(string $errorMessage)
    {
        $this->update([
            'processed' => false,
            'attempts' => $this->attempts + 1,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Verifica se deve reprocessar
     */
    public function shouldRetry(): bool
    {
        return !$this->processed && $this->attempts < 3;
    }

    /**
     * Extrai ID do recurso da URL
     */
    public function getResourceId(): ?string
    {
        if (preg_match('/\/([^\/]+)$/', $this->resource, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('processed', false);
    }

    public function scopeProcessed($query)
    {
        return $query->where('processed', true);
    }

    public function scopeByTopic($query, string $topic)
    {
        return $query->where('topic', $topic);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('received_at', '>=', now()->subHours($hours));
    }

    public function scopeNeedsRetry($query)
    {
        return $query->where('processed', false)
                    ->where('attempts', '<', 3);
    }
}
