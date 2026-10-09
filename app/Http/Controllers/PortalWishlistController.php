<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientFavorite;
use App\Models\User;
use App\Services\Portal\WishlistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Meus favoritos" do portal e o "Avise-me".
 * Sem login os favoritos ficam no aparelho (localStorage) e a página recebe os
 * ids pela URL; com login ficam na conta (client_favorites).
 * As rotas de JSON ficam fora do portal.auth para funcionar também com o
 * cadastro ainda incompleto (logo depois de criar a conta).
 */
class PortalWishlistController extends Controller
{
    public function __construct(private WishlistService $wishlist)
    {
    }

    private function client(): ?Client
    {
        /** @var Client|null $client */
        $client = Auth::guard('portal')->user();

        return $client && $client->portal_active ? $client : null;
    }

    public function index(Request $request)
    {
        $client = $this->client();

        if ($client) {
            $storeId = (int) $client->user_id;
            $favorites = ClientFavorite::where('client_id', $client->id)->get()->keyBy('product_id');
            $ids = $favorites->keys()->all();
            // Avisos novos ficam destacados nesta visita e já contam como vistos.
            $alerts = $this->wishlist->unseenAlerts($client)->groupBy('product_id');
            $this->wishlist->markSeen($client);
        } else {
            $storeId = (int) ($request->query('loja') ?: $request->cookie('portal_store'));
            $favorites = collect();
            $alerts = collect();
            $ids = collect(explode(',', (string) $request->query('ids', '')))
                ->map(fn ($v) => (int) $v)->filter(fn ($v) => $v > 0)->take(WishlistService::MAX_FAVORITES)->all();
        }

        if (! $storeId) {
            return redirect()->route('portal.login')->with('info', 'Acesse o catálogo pelo link compartilhado pela loja.');
        }

        $products = $this->wishlist->products($storeId, $ids);
        $store = User::select('id', 'name', 'phone')->find($storeId);
        $wishlist = $this->wishlist;
        $guestHasIds = $request->has('ids');

        return view('portal.favorites', compact('client', 'products', 'favorites', 'alerts', 'store', 'storeId', 'wishlist', 'guestHasIds'));
    }

    public function toggle(Request $request)
    {
        $client = $this->client();
        if (! $client) {
            return response()->json(['message' => 'Entre na sua conta.'], 401);
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'on'         => ['nullable', 'boolean'],
        ]);

        $on = $this->wishlist->toggle($client, (int) $data['product_id'], array_key_exists('on', $data) && $data['on'] !== null ? (bool) $data['on'] : null);

        return response()->json(['favorited' => $on, 'ids' => $this->wishlist->favoriteIds($client)]);
    }

    /** Favoritos salvos no aparelho antes do login/cadastro entram na conta. */
    public function sync(Request $request)
    {
        $client = $this->client();
        if (! $client) {
            return response()->json(['message' => 'Entre na sua conta.'], 401);
        }

        $data = $request->validate([
            'ids'   => ['present', 'array', 'max:' . WishlistService::MAX_FAVORITES],
            'ids.*' => ['integer'],
        ]);

        $added = $this->wishlist->merge($client, $data['ids']);

        return response()->json(['added' => $added, 'ids' => $this->wishlist->favoriteIds($client)]);
    }

    public function notify(Request $request, int $product)
    {
        $client = $this->client();
        if (! $client) {
            return response()->json(['message' => 'Entre na sua conta.'], 401);
        }

        $data = $request->validate([
            'notify_promo' => ['sometimes', 'boolean'],
            'notify_stock' => ['sometimes', 'boolean'],
        ]);

        $fav = $this->wishlist->setNotify($client, $product, $data);
        if (! $fav) {
            return response()->json(['message' => 'Produto não está nos seus favoritos.'], 404);
        }

        return response()->json(['notify_promo' => $fav->notify_promo, 'notify_stock' => $fav->notify_stock]);
    }
}
