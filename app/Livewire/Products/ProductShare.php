<?php

namespace App\Livewire\Products;

use App\Models\Client;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionSetting;
use App\Services\Products\PromotionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Botão de WhatsApp do produto (card, tela do produto, edição...): mesma
 * imagem e mensagem da tela de promoções, mas para qualquer produto. Se o
 * produto estiver em promoção, usa a promoção (De/Por e modelos de promo).
 * Fica uma vez na página e abre com o evento open-product-share.
 */
class ProductShare extends Component
{
    public bool $show = false;
    public ?int $productId = null;
    public ?int $clientId = null;
    public string $template = '';
    public string $text = '';

    #[On('open-product-share')]
    public function open(int $productId): void
    {
        $product = $this->product($productId);
        if (!$product) {
            return;
        }
        $this->productId = $product->id;
        $this->clientId = null;
        $this->template = '';
        $this->buildText();
        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function setClient(?int $clientId): void
    {
        $this->clientId = $clientId ?: null;
        $this->buildText();
    }

    public function setTemplate(string $key): void
    {
        $templates = $this->promotion() ? PromotionSetting::TEMPLATES : PromotionSetting::PRODUCT_TEMPLATES;
        $this->template = isset($templates[$key]) ? $key : '';
        $this->buildText();
    }

    /** Registra o envio (só promoções têm histórico) e devolve o link do WhatsApp. */
    public function recordShare(string $channel, ?int $clientId = null): string
    {
        $service = app(PromotionService::class);
        $client = $clientId ? $this->ownClient($clientId) : null;
        $text = $clientId === null || $clientId === $this->clientId
            ? $this->text
            : $this->messageFor($client);

        if ($promo = $this->promotion()) {
            $service->recordSend(
                Auth::id(),
                [$promo->id],
                $client?->id,
                in_array($channel, ['whatsapp', 'compartilhar', 'copiar'], true) ? $channel : 'whatsapp',
                'produto'
            );
        }

        return $service->whatsappUrl($text, $client);
    }

    public function getDataProperty(): array
    {
        $product = $this->show && $this->productId ? $this->product($this->productId) : null;
        if (!$product) {
            return ['product' => null, 'promo' => null, 'clients' => collect(), 'allClients' => collect(), 'card' => null];
        }
        $service = app(PromotionService::class);
        $promo = $this->promotion();

        return [
            'product'    => $product,
            'promo'      => $promo,
            'clients'    => $service->productInterestedClients($product, $promo?->id),
            'allClients' => Client::where('user_id', Auth::id())->whereNotNull('phone')->where('phone', '!=', '')->orderBy('name')->get(['id', 'name', 'phone']),
            'card'       => [
                'name'     => $product->name . ($product->parent_id && $product->variation_value ? ' · ' . $product->variation_value : ''),
                // Caminho relativo: mesma origem da página, senão o canvas não vira imagem para copiar/compartilhar
                'image'    => $product->image && $product->image !== 'product-placeholder.png' ? (parse_url($product->image_url, PHP_URL_PATH) ?: $product->image_url) : null,
                'promo'    => $service->money($promo ? (float) $promo->promo_price : (float) $product->price_sale),
                'original' => $promo ? $service->money((float) $promo->original_price) : null,
                'discount' => $promo?->discount_percent,
                'savings'  => $promo ? $service->money(max(0, (float) $promo->original_price - (float) $promo->promo_price)) : null,
                'validity' => $promo
                    ? ($promo->ends_at ? 'Válido até ' . $promo->endsLocal()->format('d/m') : 'Enquanto durar o estoque')
                    : 'Pronta entrega',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.products.product-share', ['data' => $this->data]);
    }

    private function buildText(): void
    {
        $client = $this->clientId ? $this->ownClient($this->clientId) : null;
        $this->text = $this->messageFor($client);
    }

    private function messageFor(?Client $client): string
    {
        $service = app(PromotionService::class);
        $product = $this->product((int) $this->productId);
        if (!$product) {
            return '';
        }
        if ($promo = $this->promotion()) {
            return $service->message($promo, $client, null, PromotionSetting::TEMPLATES[$this->template][2] ?? null);
        }

        return $service->productMessage($product, $client, null, $this->template);
    }

    private function promotion(): ?Promotion
    {
        $product = $this->productId ? $this->product($this->productId) : null;

        return $product?->livePromotion();
    }

    private function product(int $id): ?Product
    {
        return Product::where('user_id', Auth::id())->find($id);
    }

    private function ownClient(int $id): Client
    {
        return Client::where('user_id', Auth::id())->findOrFail($id);
    }
}
