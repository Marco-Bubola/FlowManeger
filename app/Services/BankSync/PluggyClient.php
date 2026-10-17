<?php

namespace App\Services\BankSync;

use App\Models\BankConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/** API da Pluggy (Open Finance). https://docs.pluggy.ai */
class PluggyClient
{
    public function __construct(private BankConnection $connection)
    {
    }

    private function base(): string
    {
        return rtrim(config('services.pluggy.base_url', 'https://api.pluggy.ai'), '/');
    }

    /** A chave da API vale 2 horas; guardamos por 100 minutos. */
    private function apiKey(bool $fresh = false): string
    {
        $key = 'pluggy-key:' . $this->connection->user_id . ':' . md5((string) $this->connection->client_id);
        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addMinutes(100), function () {
            $r = Http::timeout(30)->acceptJson()->post($this->base() . '/auth', [
                'clientId' => $this->connection->client_id,
                'clientSecret' => $this->connection->client_secret,
            ]);
            if (! $r->successful() || ! $r->json('apiKey')) {
                throw new RuntimeException($r->status() === 401 || $r->status() === 403
                    ? 'A Pluggy recusou o Client ID ou o Client Secret. Confira e salve de novo.'
                    : 'Não consegui falar com a Pluggy (HTTP ' . $r->status() . ').');
            }

            return (string) $r->json('apiKey');
        });
    }

    private function get(string $path, array $query = []): array
    {
        $r = Http::timeout(60)->acceptJson()->withHeaders(['X-API-KEY' => $this->apiKey()])->get($this->base() . $path, $query);
        if ($r->status() === 401) {
            $r = Http::timeout(60)->acceptJson()->withHeaders(['X-API-KEY' => $this->apiKey(true)])->get($this->base() . $path, $query);
        }
        if ($r->status() === 404) {
            throw new RuntimeException('A Pluggy não achou ' . $path . '. Confira o Item ID (ele muda se a conexão for refeita no Meu Pluggy).');
        }
        if (! $r->successful()) {
            throw new RuntimeException('A Pluggy respondeu com erro (HTTP ' . $r->status() . '): ' . mb_substr((string) ($r->json('message') ?? $r->body()), 0, 160));
        }

        return (array) $r->json();
    }

    public function item(string $itemId): array
    {
        return $this->get('/items/' . rawurlencode($itemId));
    }

    public function accounts(string $itemId): array
    {
        return (array) ($this->get('/accounts', ['itemId' => $itemId])['results'] ?? []);
    }

    /** Todas as transações da conta no período (pagina de 500 em 500). */
    public function transactions(string $accountId, string $from, string $to): array
    {
        $all = [];
        $page = 1;
        do {
            $res = $this->get('/transactions', ['accountId' => $accountId, 'from' => $from, 'to' => $to, 'pageSize' => 500, 'page' => $page]);
            $all = array_merge($all, (array) ($res['results'] ?? []));
            $pages = (int) ($res['totalPages'] ?? 1);
            $page++;
        } while ($page <= $pages && $page <= 20);

        return $all;
    }
}
