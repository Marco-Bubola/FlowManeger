<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CashbookUploadHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cashbook_uploads_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'cofrinho_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'total_transactions',
        'transactions_created',
        'transactions_skipped',
        'status',
        'started_at',
        'completed_at',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'total_transactions' => 'integer',
        'transactions_created' => 'integer',
        'transactions_skipped' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Get the user that owns the upload history.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cofrinho associated with the upload history.
     */
    public function cofrinho(): BelongsTo
    {
        return $this->belongsTo(Cofrinho::class);
    }

    /**
     * Scope a query to only include uploads for a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if the upload is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if the upload is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the upload has failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark the upload as completed.
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the upload as failed.
     */
    public function markAsFailed(string $errorMessage = null): bool
    {
        return $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Get the status badge information.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'completed' => [
                'color' => 'green',
                'icon' => 'bi-check-circle-fill',
                'label' => 'Concluído'
            ],
            'failed' => [
                'color' => 'red',
                'icon' => 'bi-x-circle-fill',
                'label' => 'Falhou'
            ],
            'processing' => [
                'color' => 'yellow',
                'icon' => 'bi-arrow-repeat',
                'label' => 'Processando'
            ],
            default => [
                'color' => 'gray',
                'icon' => 'bi-question-circle-fill',
                'label' => 'Desconhecido'
            ]
        };
    }
}
