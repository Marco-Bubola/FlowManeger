<?php

namespace App\Http\Controllers;

use App\Models\ClientQuoteRequest;
use App\Models\ConsortiumNotification;
use App\Models\PortalCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * LGPD (art. 18) no portal: o cliente baixa os dados que a loja tem dele e pode
 * encerrar a conta do site. Os registros de venda ficam com a loja (a lei fiscal
 * pede); a loja é avisada para apagar o resto.
 */
class PortalPrivacyController extends Controller
{
    public function data()
    {
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();
        $quotes = ClientQuoteRequest::where('client_id', $client->id)->orderBy('id')->get()->map(fn ($q) => [
            'pedido' => $q->id, 'data' => $q->created_at?->toDateTimeString(), 'situacao' => $q->status_label,
            'itens' => collect($q->items ?? [])->map(fn ($i) => ['produto' => $i['name'] ?? null, 'quantidade' => $i['quantity'] ?? 1])->all(),
            'entrega' => $q->delivery_method, 'endereco' => $q->shipping_address['text'] ?? null, 'recado' => $q->client_notes,
        ]);
        $data = [
            'gerado_em' => now()->toDateTimeString(),
            'cadastro' => collect($client->only(['name', 'email', 'phone', 'cpf_cnpj', 'birth_date', 'cep', 'street', 'number', 'complement', 'neighborhood', 'city', 'state', 'created_at', 'portal_last_login_at']))
                ->map(fn ($v) => $v instanceof \DateTimeInterface ? $v->format('Y-m-d H:i') : $v)->all(),
            'aceitou_privacidade_em' => Schema::hasColumn('clients', 'privacy_accepted_at') ? $client->privacy_accepted_at?->toDateTimeString() : null,
            'pedidos_pelo_site' => $quotes->all(),
            'carrinho_guardado' => Schema::hasTable('portal_carts') ? PortalCart::where('client_id', $client->id)->value('items') : null,
        ];

        return response()->json($data, 200, ['Content-Disposition' => 'attachment; filename="meus-dados.json"'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function close(Request $request)
    {
        $request->validate(['confirmar' => ['accepted']], ['confirmar.accepted' => 'Marque a confirmação para encerrar a conta.']);
        /** @var \App\Models\Client $client */
        $client = Auth::guard('portal')->user();
        $ownerId = (int) $client->user_id;

        $client->forceFill(['portal_active' => false, 'portal_password' => null, 'google_id' => null, 'portal_token' => null, 'remember_token' => null])->save();
        if (Schema::hasTable('portal_carts')) {
            PortalCart::where('client_id', $client->id)->delete();
        }
        ConsortiumNotification::createGeneric('portal', 'privacy_request', $ownerId, 'Cliente encerrou a conta do site',
            "{$client->name} encerrou a conta do catálogo e pediu para apagar os dados pessoais (LGPD). O acesso ao site já foi cortado; apague no cadastro o que não precisa guardar para as vendas e a nota fiscal.",
            ['priority' => 'high', 'force' => true, 'entity_type' => 'Client', 'entity_id' => $client->id, 'action_url' => route('clients.edit', $client->id)]);

        Auth::guard('portal')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.catalog', ['userId' => $ownerId])->with('success', 'Sua conta foi encerrada. A loja foi avisada para apagar os seus dados.');
    }
}
