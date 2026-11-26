<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Services\Portal\StoreIdentity;
use App\Services\Quotes\QuoteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * O orçamento do jeito que o cliente vê (link sem login e PDF).
 * Só usa Quote::publicItems(): custo, materiais, margem e anotações nunca saem daqui.
 */
class QuotePublicController extends Controller
{
    protected function byToken(string $token): Quote
    {
        $q = Quote::where('token', $token)->whereNotIn('status', ['pedido'])->with('client')->first();
        abort_unless($q, 404);

        return $q;
    }

    public function show(Request $request, string $token)
    {
        $quote = $this->byToken($token);
        // O dono abrindo para conferir (?previa=1, logado) não conta como "visto".
        $isOwner = Auth::check() && (int) Auth::id() === (int) $quote->user_id;
        if (!$isOwner && !$request->boolean('previa')) {
            QuoteService::markViewed($quote);
        }
        if ($quote->status === 'enviado' && $quote->isExpired()) {
            $quote->status = 'vencido';
            $quote->addEvent('vencido', 'Passou da validade');
            $quote->saveQuietly();
        }

        return view('quotes.public', $this->viewData($quote) + ['isOwner' => $isOwner]);
    }

    public function respond(Request $request, string $token)
    {
        $quote = $this->byToken($token);
        $data = $request->validate([
            'acao' => 'required|in:aprovar,mudanca,recusar',
            'texto' => 'nullable|string|max:1000',
        ]);

        if (!in_array($quote->status, ['enviado', 'aprovado'], true) || $quote->isExpired()) {
            return back()->with('error', 'Este orçamento não pode mais ser respondido por aqui. Fale com a loja.');
        }

        if ($data['acao'] === 'aprovar') {
            QuoteService::approve($quote, 'cliente');

            return back()->with('success', 'Orçamento aprovado! A loja já foi avisada e vai falar com você para combinar o pagamento e a entrega.');
        }
        if ($data['acao'] === 'mudanca') {
            if (trim((string) ($data['texto'] ?? '')) === '') {
                return back()->withErrors(['texto' => 'Conte o que você quer mudar.']);
            }
            QuoteService::requestChange($quote, (string) $data['texto']);

            return back()->with('success', 'Pedido de mudança enviado. A loja vai ajustar e te mandar de novo.');
        }
        QuoteService::reject($quote, 'cliente', (string) ($data['texto'] ?? ''));

        return back()->with('success', 'Tudo bem! Avisamos a loja. Se mudar de ideia, é só chamar.');
    }

    public function pdf(string $token)
    {
        $quote = $this->byToken($token);

        return $this->renderPdf($quote);
    }

    public function ownerPdf(int $quote)
    {
        $q = Quote::where('user_id', Auth::id())->with('client')->findOrFail($quote);

        return $this->renderPdf($q);
    }

    protected function renderPdf(Quote $quote)
    {
        $data = $this->viewData($quote);
        // DomPDF precisa do caminho local das fotos.
        $local = function (?string $path) {
            if (!$path || str_starts_with($path, 'http')) {
                return null;
            }
            $full = Storage::disk('public')->path($path);

            return is_file($full) ? $full : null;
        };
        $data['local'] = $local;
        $data['logoPath'] = !empty($data['identity']['logo']) ? $local($data['identity']['logo']) : null;
        $pdf = Pdf::loadView('quotes.pdf', $data)->setPaper('a4');
        $pdf->getDomPDF()->getOptions()->set('isRemoteEnabled', false)->set('chroot', [storage_path('app/public'), public_path()]);

        return $pdf->stream('orcamento-' . $quote->number . '.pdf');
    }

    protected function viewData(Quote $quote): array
    {
        $settings = QuoteService::settings($quote->user_id);

        return [
            'quote' => $quote,
            'items' => $quote->publicItems(),
            'identity' => StoreIdentity::for((int) $quote->user_id),
            'footer' => $settings['footer'],
            'ownerId' => (int) $quote->user_id,
        ];
    }
}
