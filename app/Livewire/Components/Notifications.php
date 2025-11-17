<?php

namespace App\Livewire\Components;

use App\Support\NotificationCenter;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Component;

class Notifications extends Component
{
    public $notifications = [];
    private array $notificationFingerprints = [];

    private array $sessionTypeMap = [
        'success' => 'success',
        'status' => 'info',
        'message' => 'info',
        'error' => 'error',
        'danger' => 'error',
        'warning' => 'warning',
        'warning_details' => 'warning',
        'info' => 'info',
        'debug_info' => 'info',
        'calendar_debug' => 'info',
    ];

    protected $listeners = [
        'notify' => 'handleNotification',
        'clearNotifications' => 'clearAll'
    ];

    public function mount()
    {
        foreach ($this->sessionTypeMap as $sessionKey => $type) {
            if (!session()->has($sessionKey)) {
                continue;
            }

            $rawValue = session($sessionKey);
            $messages = $this->extractSessionNotifications($type, $rawValue);

            foreach ($messages as $payload) {
                $this->addNotification($payload);
            }

            session()->forget($sessionKey);
        }

        $this->drainQueuedNotifications();
    }

    public function handleNotification($data)
    {
        if (is_array($data)) {
            $this->addNotification($data);
        }
    }

    public function addNotification($type, $message = null, $duration = 5000, array $options = [])
    {
        if (is_array($type)) {
            $payload = $type;
        } else {
            $payload = array_merge($options, [
                'type' => $type,
                'message' => $message,
                'duration' => $duration,
            ]);
        }

        $normalizedType = $payload['type'] ?? 'info';
        $normalizedMessage = $this->normalizeMessage($normalizedType, $payload['message'] ?? '');
        $normalizedDuration = array_key_exists('duration', $payload)
            ? max((int) $payload['duration'], 0)
            : 5000;

        $notification = [
            'id' => $payload['id'] ?? uniqid('ntf_', true),
            'type' => $normalizedType,
            'message' => $normalizedMessage,
            'duration' => $normalizedType === 'loading' ? 0 : $normalizedDuration,
            'dismissible' => $payload['dismissible'] ?? ($normalizedType !== 'loading'),
            'persistOnRedirect' => $payload['persistOnRedirect'] ?? ($normalizedType !== 'loading'),
            'timestamp' => now()->toISOString(),
        ];

        $fingerprint = $this->fingerprint($notification);

        if (in_array($fingerprint, $this->notificationFingerprints, true)) {
            return $notification['id'];
        }

        $this->notifications[] = $notification;
        $this->notificationFingerprints[] = $fingerprint;

        return $notification['id'];
    }

    private function normalizeMessage(string $type, ?string $message): string
    {
        $text = trim((string) ($message ?? ''));

        if ($text !== '') {
            return $text;
        }

        return match ($type) {
            'success' => 'Operação concluída com sucesso.',
            'error' => 'Não foi possível concluir a operação.',
            'warning' => 'Verifique as informações antes de prosseguir.',
            'loading' => 'Processando... Aguarde um instante.',
            default => 'Informação atualizada com sucesso.',
        };
    }

    public function removeNotification($id)
    {
        $this->notifications = array_values(array_filter($this->notifications, function ($notification) use ($id) {
            $shouldKeep = $notification['id'] !== $id;

            if (! $shouldKeep) {
                $this->notificationFingerprints = array_values(array_filter($this->notificationFingerprints, function ($fingerprint) use ($notification) {
                    return $fingerprint !== $this->fingerprint($notification);
                }));
            }

            return $shouldKeep;
        }));
    }

    public function clearAll()
    {
        $this->notifications = [];
        $this->notificationFingerprints = [];
    }

    private function extractSessionNotifications(string $type, $value): array
    {
        $entries = $this->normalizeToArray($value);

        $notifications = [];

        foreach ($entries as $entry) {
            if (is_array($entry)) {
                $message = $entry['message'] ?? ($entry['text'] ?? null);
                if ($message === null) {
                    $message = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                $notifications[] = array_filter([
                    'type' => $entry['type'] ?? $type,
                    'message' => trim((string) $message),
                    'duration' => $entry['duration'] ?? null,
                    'dismissible' => $entry['dismissible'] ?? null,
                    'persistOnRedirect' => $entry['persistOnRedirect'] ?? false,
                ], fn($value) => $value !== null);
                continue;
            }

            $stringMessage = trim((string) $entry);
            if ($stringMessage === '') {
                continue;
            }

            $notifications[] = [
                'type' => $type,
                'message' => $stringMessage,
                'persistOnRedirect' => false,
            ];
        }

        return $notifications;
    }

    private function normalizeToArray($value): array
    {
        if ($value instanceof MessageBag) {
            return $value->all();
        }

        if ($value instanceof Collection) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return Arr::flatten($value);
        }

        return [$value];
    }

    private function drainQueuedNotifications(): void
    {
        $queued = session()->pull(NotificationCenter::SESSION_KEY, []);

        if (empty($queued)) {
            return;
        }

        foreach ($queued as $payload) {
            if (!is_array($payload)) {
                $payload = ['message' => $payload];
            }

            $payload['type'] = $payload['type'] ?? 'info';
            $this->addNotification($payload);
        }
    }

    private function fingerprint(array $notification): string
    {
        return sha1(sprintf(
            '%s|%s|%s|%s',
            $notification['type'] ?? 'info',
            $notification['message'] ?? '',
            $notification['persistOnRedirect'] ?? '0',
            $notification['duration'] ?? '0'
        ));
    }

    public function render()
    {
        return view('livewire.components.notifications');
    }
}
