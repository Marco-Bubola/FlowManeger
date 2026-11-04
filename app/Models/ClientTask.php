<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Tarefa / follow-up com data ("ligar para Maria dia 10"). Cliente é opcional. */
class ClientTask extends Model
{
    protected $fillable = ['user_id', 'client_id', 'title', 'notes', 'due_date', 'due_time', 'done_at', 'notified_at'];

    protected $casts = [
        'due_date' => 'date',
        'done_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class)->withoutGlobalScopes();
    }

    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereNull('done_at');
    }

    public function scopeOwnedBy(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    public static function today(): Carbon
    {
        return Carbon::now(ConsortiumNotification::DISPLAY_TZ)->startOfDay();
    }

    public function isLate(): bool
    {
        return ! $this->done_at && $this->due_date->toDateString() < self::today()->toDateString();
    }

    public function isToday(): bool
    {
        return ! $this->done_at && $this->due_date->toDateString() === self::today()->toDateString();
    }

    /** Dia e hora marcados, no fuso da loja. */
    public function dueAt(): Carbon
    {
        return Carbon::parse($this->due_date->toDateString() . ' ' . ($this->due_time ?: '09:00'), ConsortiumNotification::DISPLAY_TZ);
    }

    /** "Hoje", "Amanhã", "Ontem", "seg, 10/11" ... */
    public function dueLabel(): string
    {
        $d = Carbon::parse($this->due_date->toDateString(), ConsortiumNotification::DISPLAY_TZ);
        $today = self::today();
        $label = match (true) {
            $d->isSameDay($today) => 'Hoje',
            $d->isSameDay($today->copy()->addDay()) => 'Amanhã',
            $d->isSameDay($today->copy()->subDay()) => 'Ontem',
            default => $d->locale('pt_BR')->isoFormat('ddd, DD/MM'),
        };

        return $this->due_time ? $label . ' às ' . substr($this->due_time, 0, 5) : $label;
    }
}
