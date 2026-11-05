<?php

namespace App\Services\Clients;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Status do cliente no CRM, calculado pelas compras (vendas canceladas e
 * orçamentos não contam):
 *  - Novo: cadastrado nos últimos 30 dias
 *  - Ativo: comprou nos últimos 90 dias
 *  - Sumido: última compra entre 90 dias e 1 ano
 *  - Inativo: mais de 1 ano sem comprar
 *  - Sem compras: cadastrado há mais de 30 dias e nunca comprou
 * Também diz de onde o cliente veio (origem).
 */
class ClientStatus
{
    public const NEW_DAYS = 30;
    public const ACTIVE_DAYS = 90;
    public const LOST_DAYS = 365;

    public const STATUSES = [
        'novo' => ['label' => 'Novo', 'icon' => 'bi-stars', 'tone' => 'sky', 'hint' => 'Cadastrado nos últimos 30 dias'],
        'ativo' => ['label' => 'Ativo', 'icon' => 'bi-lightning-charge-fill', 'tone' => 'emerald', 'hint' => 'Comprou nos últimos 90 dias'],
        'sumido' => ['label' => 'Sumido', 'icon' => 'bi-hourglass-split', 'tone' => 'amber', 'hint' => 'Sem comprar há mais de 90 dias'],
        'inativo' => ['label' => 'Inativo', 'icon' => 'bi-moon-fill', 'tone' => 'slate', 'hint' => 'Sem comprar há mais de 1 ano'],
        'sem_compra' => ['label' => 'Sem compras', 'icon' => 'bi-person-dash', 'tone' => 'violet', 'hint' => 'Nunca comprou'],
    ];

    /** Origem: como o cliente conheceu a loja. */
    public const SOURCES = [
        'instagram' => ['label' => 'Instagram', 'icon' => 'bi-instagram'],
        'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'bi-whatsapp'],
        'indicacao' => ['label' => 'Indicação', 'icon' => 'bi-people'],
        'loja' => ['label' => 'Loja / passou na frente', 'icon' => 'bi-shop'],
        'site' => ['label' => 'Site / catálogo', 'icon' => 'bi-globe'],
        'mercadolivre' => ['label' => 'Mercado Livre', 'icon' => 'bi-bag'],
        'shopee' => ['label' => 'Shopee', 'icon' => 'bi-bag-heart'],
        'facebook' => ['label' => 'Facebook', 'icon' => 'bi-facebook'],
        'google' => ['label' => 'Google', 'icon' => 'bi-google'],
        'evento' => ['label' => 'Feira / evento', 'icon' => 'bi-calendar-event'],
        'outro' => ['label' => 'Outro', 'icon' => 'bi-three-dots'],
    ];

    /** Vendas que contam como compra. */
    public const IGNORED_SALE_STATUSES = ['cancelada', 'orcamento'];

    public static function lastSaleSql(): string
    {
        return "(select max(s.created_at) from sales s where s.client_id = clients.id and s.status not in ('cancelada','orcamento'))";
    }

    public static function compute(?Carbon $createdAt, ?Carbon $lastSale, ?Carbon $now = null): string
    {
        $now = $now ?: now();
        if ($createdAt && $createdAt->gte($now->copy()->subDays(self::NEW_DAYS))) {
            return 'novo';
        }
        if (! $lastSale) {
            return 'sem_compra';
        }
        if ($lastSale->gte($now->copy()->subDays(self::ACTIVE_DAYS))) {
            return 'ativo';
        }

        return $lastSale->gte($now->copy()->subDays(self::LOST_DAYS)) ? 'sumido' : 'inativo';
    }

    /** Status de um cliente; usa as vendas já carregadas quando houver. */
    public static function for(Client $client): string
    {
        $sales = $client->relationLoaded('sales')
            ? $client->sales->reject(fn ($s) => in_array($s->status, self::IGNORED_SALE_STATUSES, true))
            : null;
        $last = $sales !== null
            ? $sales->max('created_at')
            : $client->sales()->whereNotIn('status', self::IGNORED_SALE_STATUSES)->max('created_at');

        return self::compute($client->created_at ? Carbon::parse($client->created_at) : null, $last ? Carbon::parse($last) : null);
    }

    public static function info(string $status): array
    {
        return self::STATUSES[$status] ?? self::STATUSES['sem_compra'];
    }

    public static function sourceInfo(?string $source): ?array
    {
        return $source && isset(self::SOURCES[$source]) ? self::SOURCES[$source] : null;
    }

    /** Filtra a consulta de clientes por status. */
    public static function apply(Builder $query, string $status): Builder
    {
        $last = self::lastSaleSql();
        $new = now()->subDays(self::NEW_DAYS);
        $active = now()->subDays(self::ACTIVE_DAYS);
        $lost = now()->subDays(self::LOST_DAYS);

        if ($status === 'novo') {
            return $query->where('clients.created_at', '>=', $new);
        }
        $query->where(fn ($q) => $q->where('clients.created_at', '<', $new)->orWhereNull('clients.created_at'));

        return match ($status) {
            'ativo' => $query->whereRaw("$last >= ?", [$active]),
            'sumido' => $query->whereRaw("$last < ?", [$active])->whereRaw("$last >= ?", [$lost]),
            'inativo' => $query->whereRaw("$last < ?", [$lost]),
            'sem_compra' => $query->whereRaw("$last is null"),
            default => $query,
        };
    }

    /** Quantos clientes em cada status (para os números do topo). */
    public static function counts(?Builder $base = null): array
    {
        $out = [];
        foreach (array_keys(self::STATUSES) as $s) {
            $out[$s] = self::apply($base ? clone $base : Client::query(), $s)->count();
        }

        return $out;
    }
}
