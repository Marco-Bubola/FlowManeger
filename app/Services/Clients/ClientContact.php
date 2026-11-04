<?php

namespace App\Services\Clients;

/** Ajuda de contato: telefone do cliente no formato do link wa.me. */
class ClientContact
{
    public static function whatsapp(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            return strlen($digits) <= 13 ? $digits : null;
        }

        return in_array(strlen($digits), [10, 11], true) ? '55' . $digits : null;
    }

    public static function whatsappUrl(?string $phone, string $text = ''): ?string
    {
        $p = self::whatsapp($phone);

        return $p ? 'https://wa.me/' . $p . ($text !== '' ? '?text=' . rawurlencode($text) : '') : null;
    }
}
