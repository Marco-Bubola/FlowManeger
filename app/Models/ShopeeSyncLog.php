<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopeeSyncLog extends Model
{
    protected $table = 'shopee_sync_logs';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'platform',
        'sync_type',
        'entity_type',
        'entity_id',
        'action',
        'status',
        'message',
        'request_data',
        'response_data',
        'http_status',
        'execution_time_ms',
        'reference_id',
    ];

    protected $casts = [
        'request_data'  => 'array',
        'response_data' => 'array',
        'created_at'    => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Factory Methods (para facilitar criação de logs)
    // -------------------------------------------------------------------------

    public static function logSuccess(
        int $userId,
        string $platform,
        string $syncType,
        string $message,
        array $data = []
    ): self {
        return self::create(array_merge([
            'user_id'    => $userId,
            'platform'   => $platform,
            'sync_type'  => $syncType,
            'status'     => 'success',
            'message'    => $message,
            'created_at' => now(),
        ], $data));
    }

    public static function logError(
        int $userId,
        string $platform,
        string $syncType,
        string $message,
        array $data = []
    ): self {
        return self::create(array_merge([
            'user_id'    => $userId,
            'platform'   => $platform,
            'sync_type'  => $syncType,
            'status'     => 'error',
            'message'    => $message,
            'created_at' => now(),
        ], $data));
    }

    public static function logInfo(
        int $userId,
        string $platform,
        string $syncType,
        string $message,
        array $data = []
    ): self {
        return self::create(array_merge([
            'user_id'    => $userId,
            'platform'   => $platform,
            'sync_type'  => $syncType,
            'status'     => 'info',
            'message'    => $message,
            'created_at' => now(),
        ], $data));
    }
}
