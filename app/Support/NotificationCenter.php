<?php

namespace App\Support;

use Illuminate\Support\Arr;

class NotificationCenter
{
    public const SESSION_KEY = 'notifications.queue';

    public static function flash(string $type, string $message, array $options = []): void
    {
        $payload = [
            'type' => $type,
            'message' => $message,
            'duration' => $options['duration'] ?? null,
            'dismissible' => $options['dismissible'] ?? null,
            'persistOnRedirect' => $options['persistOnRedirect'] ?? true,
        ];

        $queue = session()->get(self::SESSION_KEY, []);
        $queue[] = $payload;
        session()->flash(self::SESSION_KEY, $queue);

        // Maintains compatibility with legacy flashes for views still reading session('success') etc.
        static::flashLegacyBucket($type, $payload);
    }

    public static function success(string $message, array $options = []): void
    {
        static::flash('success', $message, $options);
    }

    public static function error(string $message, array $options = []): void
    {
        static::flash('error', $message, $options);
    }

    public static function warning(string $message, array $options = []): void
    {
        static::flash('warning', $message, $options);
    }

    public static function info(string $message, array $options = []): void
    {
        static::flash('info', $message, $options);
    }

    protected static function flashLegacyBucket(string $type, array $payload): void
    {
        $current = session()->get($type);

        if ($current === null) {
            session()->flash($type, $payload['message']);
            return;
        }

        $bucket = Arr::wrap($current);
        $bucket[] = $payload['message'];
        session()->flash($type, $bucket);
    }
}
