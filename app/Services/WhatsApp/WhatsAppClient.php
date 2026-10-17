<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/** WhatsApp Cloud API (Meta): baixar o print recebido e responder. */
class WhatsAppClient
{
    public function __construct(private WhatsAppSetting $setting)
    {
    }

    private function base(): string
    {
        return rtrim(config('services.whatsapp.graph_url', 'https://graph.facebook.com/v21.0'), '/');
    }

    private function http()
    {
        return Http::withToken((string) $this->setting->access_token)->timeout(30)->acceptJson();
    }

    /** Dados do número (para o "Testar conexão"). */
    public function phoneInfo(): array
    {
        $r = $this->http()->get($this->base() . '/' . $this->setting->phone_number_id, ['fields' => 'display_phone_number,verified_name,quality_rating']);
        if (! $r->successful()) {
            throw new RuntimeException($this->error($r->json(), $r->status()));
        }

        return $r->json();
    }

    /** @return array{0:string,1:string} [bytes, mime] */
    public function downloadMedia(string $mediaId): array
    {
        $meta = $this->http()->get($this->base() . '/' . $mediaId);
        if (! $meta->successful() || ! $meta->json('url')) {
            throw new RuntimeException('Não consegui baixar a imagem do WhatsApp: ' . $this->error($meta->json(), $meta->status()));
        }
        $file = $this->http()->withHeaders(['Accept' => '*/*'])->get($meta->json('url'));
        if (! $file->successful()) {
            throw new RuntimeException('Não consegui baixar a imagem do WhatsApp (HTTP ' . $file->status() . ').');
        }

        return [$file->body(), (string) ($meta->json('mime_type') ?: $file->header('Content-Type') ?: 'image/jpeg')];
    }

    public function sendText(string $to, string $body): bool
    {
        $r = $this->http()->post($this->base() . '/' . $this->setting->phone_number_id . '/messages', [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['preview_url' => false, 'body' => mb_substr($body, 0, 4000)],
        ]);
        if (! $r->successful()) {
            \Illuminate\Support\Facades\Log::warning('WhatsApp: resposta não enviada', ['status' => $r->status(), 'body' => $r->body()]);
        }

        return $r->successful();
    }

    private function error(?array $json, int $status): string
    {
        $msg = data_get($json, 'error.message');
        if ($status === 401 || data_get($json, 'error.code') === 190) {
            return 'O token da Meta é inválido ou venceu. Gere um token permanente e salve de novo.';
        }

        return $msg ? "A Meta respondeu: {$msg}" : "A Meta respondeu com erro (HTTP {$status}).";
    }
}
