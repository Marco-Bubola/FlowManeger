<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use App\Services\Quotes\QuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Portal: "Pedir orçamento" (produto sob medida ou reforma, com fotos) e
 * "Quero em outra cor" (?produto=ID). Funciona com ou sem login. O pedido vira
 * um orçamento com situação "Pedido novo" na área de Orçamentos do app.
 */
class PortalCustomQuoteController extends Controller
{
    protected function product(int $userId, ?int $id): ?Product
    {
        if (!$id) {
            return null;
        }

        return Product::withoutGlobalScope('team_visibility')->where('user_id', $userId)->whereIn('status', ['active', 'ativo'])->find($id);
    }

    /** Opções que a família do produto já tem (as cores que a loja já fez). */
    protected function familyOptions(?Product $p): array
    {
        if (!$p) {
            return [];
        }
        $root = (int) ($p->parent_id ?: $p->id);

        return Product::withoutGlobalScope('team_visibility')->where('user_id', $p->user_id)
            ->where(fn ($q) => $q->where('id', $root)->orWhere('parent_id', $root))
            ->pluck('variation_value')->map(fn ($v) => trim((string) $v))->filter()
            ->reject(fn ($v) => preg_match('/^(R\$\s*)?[\d.,]+$/u', $v))->unique()->values()->all();
    }

    public function create(Request $request, int $userId)
    {
        abort_unless(User::whereKey($userId)->exists(), 404);
        $settings = QuoteService::settings($userId);
        $product = $this->product($userId, (int) $request->query('produto'));
        $client = Auth::guard('portal')->user();
        if ($client && (int) $client->user_id !== $userId) {
            $client = null;
        }

        return view('portal.custom-quote', [
            'ownerId' => $userId,
            'settings' => $settings,
            'enabled' => (bool) $settings['portal_requests'],
            'product' => $product,
            'options' => $this->familyOptions($product),
            'client' => $client,
            'kind' => $request->query('tipo') === 'reforma' ? 'reforma' : 'produto',
        ]);
    }

    public function store(Request $request, int $userId)
    {
        abort_unless(User::whereKey($userId)->exists(), 404);
        $settings = QuoteService::settings($userId);
        if (!$settings['portal_requests']) {
            return back()->with('error', 'A loja não está recebendo pedidos de orçamento por aqui agora.');
        }
        // Campo escondido que só robô preenche.
        if (filled($request->input('site'))) {
            return redirect()->route('portal.custom-quote.create', ['userId' => $userId])->with('quote_sent', true);
        }

        $client = Auth::guard('portal')->user();
        if ($client && (int) $client->user_id !== $userId) {
            $client = null;
        }

        $data = $request->validate([
            'tipo' => 'required|in:produto,reforma',
            'descricao' => 'required|string|min:5|max:2000',
            'medidas' => 'nullable|string|max:200',
            'cores' => 'nullable|array|max:10',
            'cores.*' => 'nullable|string|max:60',
            'outra_cor' => 'nullable|string|max:120',
            'produto' => 'nullable|integer',
            'fotos' => 'nullable|array|max:8',
            'fotos.*' => 'image|max:12288',
            'nome' => $client ? 'nullable' : 'required|string|max:120',
            'whatsapp' => $client ? 'nullable' : 'required|string|min:8|max:30',
        ], [
            'descricao.required' => 'Conte como é o que você quer.',
            'descricao.min' => 'Conte um pouco mais sobre o que você quer.',
            'nome.required' => 'Coloque o seu nome.',
            'whatsapp.required' => 'Coloque o seu WhatsApp para a loja responder.',
            'fotos.max' => 'Mande no máximo 8 fotos.',
            'fotos.*.image' => 'Cada arquivo precisa ser uma foto.',
            'fotos.*.max' => 'Cada foto pode ter no máximo 12 MB.',
        ]);

        $product = $this->product($userId, (int) ($data['produto'] ?? 0));
        $colors = array_filter(array_map(fn ($v) => trim((string) $v), (array) ($data['cores'] ?? [])));
        if (trim((string) ($data['outra_cor'] ?? '')) !== '') {
            $colors['Outra cor'] = trim($data['outra_cor']);
        }
        $colorText = implode(' · ', array_map(fn ($g, $c) => $g === 'Opção' ? $c : "$g $c", array_keys($colors), $colors));

        $photos = [];
        foreach ((array) $request->file('fotos', []) as $f) {
            if ($path = QuoteService::storePhoto($f, $userId, 'quotes/pedidos')) {
                $photos[] = $path;
            }
        }

        $phone = trim((string) ($data['whatsapp'] ?? '')) ?: $client?->phone;
        $quote = QuoteService::make($userId, [
            'status' => 'pedido',
            'source' => $product ? 'portal_cor' : 'portal',
            'kind' => $data['tipo'],
            'client_id' => $client?->id,
            'contact_name' => $client ? null : trim($data['nome']),
            'contact_phone' => $client ? null : $phone,
            'title' => $product ? mb_substr($product->name . ($colorText ? ' — ' . $colorText : ''), 0, 160) : null,
            'request_text' => trim($data['descricao']),
            'request_photos' => $photos,
            'request_data' => array_filter(['cores' => $colors, 'cor_texto' => $colorText, 'medidas' => trim((string) ($data['medidas'] ?? ''))]),
            'product_id' => $product?->id,
            'items' => [],
        ]);
        $quote->addEvent('pedido', 'Pedido feito pelo portal' . ($photos ? ' com ' . count($photos) . ' foto(s)' : ''));
        $quote->save();
        QuoteService::notifyNewRequest($quote);

        return redirect()->route('portal.custom-quote.create', array_filter(['userId' => $userId]))
            ->with('quote_sent', true)
            ->with('quote_days', $settings['reply_days']);
    }
}
