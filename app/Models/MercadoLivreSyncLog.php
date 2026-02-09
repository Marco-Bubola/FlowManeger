<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MercadoLivreSyncLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $table = 'mercadolivre_sync_log';

    protected $fillable = [
        'user_id',
        'sync_type',
        'entity_type',
        'entity_id',
        'action',
        'status',
        'message',
        'request_data',
        'response_data',
        'http_status',
        'execution_time',
        'api_calls_remaining',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relacionamento com usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cria log de sucesso
     */
    public static function logSuccess(
        int $userId,
        string $syncType,
        string $message = null,
        array $data = []
    ) {
        return self::create(array_merge([
            'user_id' => $userId,
            'sync_type' => $syncType,
            'status' => 'success',
            'message' => $message,
        ], $data));
    }

    /**
     * Cria log de erro
     */
    public static function logError(
        int $userId,
        string $syncType,
        string $message,
        array $data = []
    ) {
        return self::create(array_merge([
            'user_id' => $userId,
            'sync_type' => $syncType,
            'status' => 'error',
            'message' => $message,
        ], $data));
    }

    /**
     * Cria log de warning
     */
    public static function logWarning(
        int $userId,
        string $syncType,
        string $message,
        array $data = []
    ) {
        return self::create(array_merge([
            'user_id' => $userId,
            'sync_type' => $syncType,
            'status' => 'warning',
            'message' => $message,
        ], $data));
    }

    /**
     * Scopes
     */
    public function scopeErrors($query)
    {
        return $query->where('status', 'error');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('sync_type', $type);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeForEntity($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)
                    ->where('entity_id', $entityId);
    }
}
