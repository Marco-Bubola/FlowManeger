<?php

namespace App\Livewire\Products;

use App\Models\ClientFavorite;
use App\Models\ClientProductAlert;
use App\Models\ConsortiumNotification;
use App\Services\Portal\WishlistService;
use App\Traits\HasNotifications;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Clientes interessados: quem favoritou cada produto no catálogo do portal e
 * pediu aviso. Os que estão esperando (o produto entrou em promoção ou voltou
 * ao estoque) aparecem primeiro, com o WhatsApp pronto para um toque.
 */
class InterestedClients extends Component
{
    use HasNotifications;
    use WithPagination;

    public const PER_PAGE = 10;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filter = 'esperando'; // esperando | todos

    #[Url(as: 'produto')]
    public ?int $product = null;

    public function mount(WishlistService $wishlist): void
    {
        if ($this->product) {
            $this->filter = 'todos';
        }
        // Promoções agendadas que começaram e ainda não geraram aviso.
        $wishlist->sweepPromotions(userId: Auth::id());
    }

    public function updating($name): void
    {
        if (in_array($name, ['search', 'filter'], true)) {
            $this->resetPage();
        }
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter === 'todos' ? 'todos' : 'esperando';
        $this->product = null;
        $this->resetPage();
    }

    public function clearProduct(): void
    {
        $this->product = null;
        $this->resetPage();
    }

    /** A loja abriu o WhatsApp do cliente: o aviso sai da fila de espera. */
    public function markContacted(int $productId, int $clientId): void
    {
        ClientProductAlert::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->where('client_id', $clientId)
            ->waiting()
            ->update(['contacted_at' => now()]);

        $this->closeBellIfDone($productId);
    }

    public function markAllContacted(int $productId): void
    {
        ClientProductAlert::where('user_id', Auth::id())->where('product_id', $productId)->waiting()->update(['contacted_at' => now()]);
        $this->closeBellIfDone($productId);
        $this->notifySuccess('Marcado como avisado.');
    }

    private function closeBellIfDone(int $productId): void
    {
        if (app(WishlistService::class)->waitingCount($productId) > 0) {
            return;
        }

        ConsortiumNotification::where('user_id', Auth::id())
            ->where('type', WishlistService::OWNER_NOTIFICATION_TYPE)
            ->where('entity_type', 'product')
            ->where('entity_id', $productId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function render(WishlistService $wishlist)
    {
        $userId = (int) Auth::id();
        $ready = WishlistService::ready();

        $favorites = $ready
            ? ClientFavorite::where('user_id', $userId)
                ->with(['product' => fn ($q) => $q->where('user_id', $userId)->with(['activePromotion', 'images']), 'client'])
                ->get()
                ->filter(fn ($f) => $f->product && $f->client)
            : collect();

        $alerts = $ready
            ? ClientProductAlert::where('user_id', $userId)->latest('id')->get()->groupBy(fn ($a) => $a->product_id . ':' . $a->client_id)
            : collect();

        $groups = $favorites->groupBy('product_id')->map(function ($favs) use ($alerts) {
            $rows = $favs->map(function ($f) use ($alerts) {
                $alert = ($alerts[$f->product_id . ':' . $f->client_id] ?? collect())->first();

                return [
                    'favorite' => $f,
                    'client'   => $f->client,
                    'alert'    => $alert,
                    'waiting'  => $alert && ! $alert->contacted_at,
                ];
            })->sortBy(fn ($r) => [$r['waiting'] ? 0 : 1, mb_strtolower($r['client']->name)])->values();

            return [
                'product' => $favs->first()->product,
                'rows'    => $rows,
                'waiting' => $rows->where('waiting', true)->count(),
                'reason'  => $rows->firstWhere('waiting', true)['alert']->reason ?? null,
            ];
        });

        $waitingTotal = $groups->sum('waiting');
        $term = mb_strtolower(trim($this->search));

        $list = $groups
            ->when($this->product, fn ($c) => $c->filter(fn ($g) => $g['product']->id === $this->product))
            ->when(! $this->product && $this->filter !== 'todos', fn ($c) => $c->filter(fn ($g) => $g['waiting'] > 0))
            ->when($term !== '', fn ($c) => $c->filter(fn ($g) => str_contains(mb_strtolower($g['product']->name . ' ' . $g['product']->variation_value . ' ' . $g['product']->product_code), $term)))
            ->sort(fn ($a, $b) => [$b['waiting'], $b['rows']->count()] <=> [$a['waiting'], $a['rows']->count()])
            ->values();

        $page = max(1, min($this->getPage(), (int) ceil(max(1, $list->count()) / self::PER_PAGE)));
        $products = new LengthAwarePaginator($list->forPage($page, self::PER_PAGE)->values(), $list->count(), self::PER_PAGE, $page);

        return view('livewire.products.interested-clients', [
            'products'      => $products,
            'waitingTotal'  => $waitingTotal,
            'productCount'  => $groups->count(),
            'clientCount'   => $favorites->pluck('client_id')->unique()->count(),
            'wishlist'      => $wishlist,
            'storeName'     => Auth::user()->name,
        ])->layout('components.layouts.app');
    }
}
