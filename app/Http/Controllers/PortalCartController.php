<?php

namespace App\Http\Controllers;

use App\Models\PortalCart;
use App\Models\Product;
use App\Services\Portal\CartRecovery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Carrinho guardado do cliente logado (para o lembrete de carrinho abandonado). */
class PortalCartController extends Controller
{
    /** O catálogo manda o carrinho a cada mudança; sem login não guarda nada. */
    public function save(Request $request, int $userId)
    {
        /** @var \App\Models\Client|null $client */
        $client = Auth::guard('portal')->user();
        if (! $client || (int) $client->user_id !== $userId) {
            return response()->json(['saved' => false]);
        }
        $items = $request->validate(['items' => ['present', 'array', 'max:60'], 'items.*.id' => ['required', 'integer'], 'items.*.qty' => ['nullable', 'integer']])['items'];
        $cart = CartRecovery::sync($client, $items);

        return response()->json(['saved' => (bool) $cart]);
    }

    /** Link da lembrança: põe os produtos de volta no carrinho do aparelho e abre o carrinho. */
    public function restore(int $userId, string $token)
    {
        $cart = CartRecovery::ready() ? PortalCart::where('user_id', $userId)->where('token', $token)->first() : null;
        if (! $cart) {
            return redirect()->route('portal.catalog', ['userId' => $userId]);
        }
        $products = Product::withoutGlobalScope('team_visibility')->where('user_id', $userId)
            ->whereIn('id', collect($cart->items ?? [])->pluck('id'))->whereIn('status', ['active', 'ativo'])
            ->with(['images', 'activePromotion'])->get()->keyBy('id');
        $orders = app(\App\Services\Portal\PortalOrderService::class);
        $items = collect($cart->items ?? [])->map(function ($i) use ($products, $orders) {
            $p = $products->get($i['id']);
            if (! $p || (int) $p->stock_quantity <= 0) {
                return null;
            }

            return ['id' => $p->id, 'name' => $p->name, 'price' => (float) ($p->activePromotion?->promo_price ?? $p->price_sale), 'stock' => (int) $p->stock_quantity,
                'img' => $orders->photo($p), 'qty' => min((int) $i['qty'], (int) $p->stock_quantity)];
        })->filter()->values();

        return view('portal.cart-restore', [
            'items' => $items,
            'next' => Auth::guard('portal')->check() ? route('portal.quotes.create') : route('portal.catalog', ['userId' => $userId]),
            'name' => \App\Services\Portal\StoreIdentity::for($userId)['name'],
        ]);
    }
}
