<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** WhatsApp Cloud API (Meta) de cada usuário, para lançar por print. */
class WhatsAppSetting extends Model
{
    protected $table = 'whatsapp_settings';

    protected $fillable = [
        'user_id', 'enabled', 'phone_number_id', 'access_token', 'app_secret',
        'verify_token', 'allowed_numbers', 'auto_save', 'last_message_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'auto_save' => 'boolean',
        'access_token' => 'encrypted',
        'app_secret' => 'encrypted',
        'last_message_at' => 'datetime',
    ];

    protected $hidden = ['access_token', 'app_secret'];

    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId], ['verify_token' => Str::random(32)]);
    }

    public function ready(): bool
    {
        return $this->enabled && filled($this->phone_number_id) && filled($this->access_token);
    }

    /** Só dígitos; aceita com ou sem 55 e com ou sem o 9 do celular. */
    public static function normalizeNumber(?string $number): string
    {
        $digits = preg_replace('/\D/', '', (string) $number);
        if (strlen($digits) === 10 || strlen($digits) === 11) {
            $digits = '55' . $digits;
        }

        return $digits;
    }

    /** Variante sem o nono dígito (a Meta às vezes entrega 55 + DDD + 8 dígitos). */
    private static function withoutNinthDigit(string $digits): string
    {
        return (strlen($digits) === 13 && str_starts_with($digits, '55') && $digits[4] === '9')
            ? substr($digits, 0, 4) . substr($digits, 5)
            : $digits;
    }

    public function allowedList(): array
    {
        return array_values(array_filter(array_map(
            fn ($n) => self::normalizeNumber($n),
            preg_split('/[\s,;]+/', (string) $this->allowed_numbers) ?: []
        )));
    }

    public function allows(string $from): bool
    {
        $from = self::withoutNinthDigit(self::normalizeNumber($from));
        foreach ($this->allowedList() as $n) {
            if (self::withoutNinthDigit($n) === $from) {
                return true;
            }
        }

        return false;
    }
}
