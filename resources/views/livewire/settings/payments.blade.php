<?php

use App\Models\PaymentSetting;
use App\Services\Payments\MercadoPagoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public bool $enabled = false;
    public bool $pix_enabled = true;
    public bool $allow_manual = true;
    public bool $pickup_enabled = true;
    public bool $delivery_enabled = false;
    public string $delivery_fee = '0';
    public string $delivery_note = '';
    public int $pix_minutes = 30;
    public bool $card_enabled = false;
    public int $max_installments = 12;
    public bool $boleto_enabled = false;
    public int $boleto_days = 3;
    public string $public_key = '';

    // Chaves secretas: nunca voltam para a tela; campo vazio = manter a que já está salva.
    public string $access_token = '';
    public string $webhook_secret = '';
    public bool $hasToken = false;
    public bool $hasSecret = false;
    public string $tokenEnd = '';

    public ?string $testResult = null;
    public bool $testOk = false;

    public function mount(): void
    {
        $s = PaymentSetting::for(Auth::id());
        if ($s) {
            $this->enabled          = (bool) $s->enabled;
            $this->pix_enabled      = (bool) $s->pix_enabled;
            $this->allow_manual     = (bool) $s->allow_manual;
            $this->pickup_enabled   = (bool) $s->pickup_enabled;
            $this->delivery_enabled = (bool) $s->delivery_enabled;
            $this->delivery_fee     = number_format((float) $s->delivery_fee, 2, ',', '');
            $this->delivery_note    = (string) $s->delivery_note;
            $this->pix_minutes      = (int) ($s->pix_minutes ?: 30);
            $this->card_enabled     = (bool) $s->card_enabled;
            $this->max_installments = (int) ($s->max_installments ?: 1);
            $this->boleto_enabled   = (bool) $s->boleto_enabled;
            $this->boleto_days      = (int) ($s->boleto_days ?: 3);
            $this->public_key       = (string) $s->public_key;
        }
        $this->refreshSecrets($s);
    }

    private function refreshSecrets(?PaymentSetting $s): void
    {
        $this->hasToken  = $s && filled($s->access_token);
        $this->hasSecret = $s && filled($s->webhook_secret);
        $this->tokenEnd  = $this->hasToken ? substr($s->access_token, -4) : '';
    }

    public function save(): void
    {
        $this->access_token   = trim($this->access_token);
        $this->webhook_secret = trim($this->webhook_secret);
        $this->public_key     = trim($this->public_key);

        $this->validate([
            'access_token'   => ['nullable', 'string', 'max:255', 'regex:/^(APP_USR|TEST)-/'],
            'public_key'     => ['nullable', 'string', 'max:255'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
            'delivery_fee'   => ['nullable', 'regex:/^\d{1,6}([.,]\d{1,2})?$/'],
            'delivery_note'  => ['nullable', 'string', 'max:300'],
            'pix_minutes'    => ['required', 'integer', 'min:10', 'max:1440'],
            'max_installments' => ['required', 'integer', 'min:1', 'max:12'],
            'boleto_days'    => ['required', 'integer', 'min:1', 'max:10'],
        ], [
            'boleto_days.max'    => 'Máximo de 10 dias.',
            'access_token.regex' => 'O Access Token começa com APP_USR- (produção) ou TEST- (teste).',
            'delivery_fee.regex' => 'Use só números, ex.: 12,50.',
            'pix_minutes.min'    => 'Mínimo de 10 minutos.',
            'pix_minutes.max'    => 'Máximo de 24 horas (1440 minutos).',
        ]);

        $s = PaymentSetting::firstOrNew(['user_id' => Auth::id()]);
        $s->fill([
            'provider'         => 'mercadopago',
            'enabled'          => $this->enabled,
            'pix_enabled'      => $this->pix_enabled,
            'allow_manual'     => $this->allow_manual,
            'pickup_enabled'   => $this->pickup_enabled,
            'delivery_enabled' => $this->delivery_enabled,
            'delivery_fee'     => (float) str_replace(',', '.', $this->delivery_fee ?: '0'),
            'delivery_note'    => $this->delivery_note ?: null,
            'pix_minutes'      => $this->pix_minutes,
            'card_enabled'     => $this->card_enabled,
            'max_installments' => $this->max_installments,
            'boleto_enabled'   => $this->boleto_enabled,
            'boleto_days'      => $this->boleto_days,
            'public_key'       => $this->public_key ?: null,
        ]);
        if ($this->access_token !== '') {
            $s->access_token = $this->access_token;
        }
        if ($this->webhook_secret !== '') {
            $s->webhook_secret = $this->webhook_secret;
        }
        // Sem nenhuma forma de entrega o cliente não consegue fechar o pedido.
        if (! $s->pickup_enabled && ! $s->delivery_enabled) {
            $s->pickup_enabled = true;
            $this->pickup_enabled = true;
        }
        $s->save();

        $this->access_token = '';
        $this->webhook_secret = '';
        $this->refreshSecrets($s);
        $this->testResult = null;
        $this->dispatch('payments-saved');
    }

    public function removeToken(): void
    {
        $s = PaymentSetting::for(Auth::id());
        if ($s) {
            $s->access_token = null;
            $s->webhook_secret = null;
            $s->enabled = false;
            $s->save();
        }
        $this->enabled = false;
        $this->refreshSecrets($s);
        $this->testResult = null;
    }

    public function testConnection(): void
    {
        $s = PaymentSetting::for(Auth::id());
        if (! $s || blank($s->access_token)) {
            $this->testOk = false;
            $this->testResult = 'Salve o Access Token antes de testar.';
            return;
        }
        try {
            $acc = (new MercadoPagoService($s))->account();
            $name = $acc['nickname'] ?? trim(($acc['first_name'] ?? '') . ' ' . ($acc['last_name'] ?? ''));
            $this->testOk = true;
            $this->testResult = 'Conectado à conta ' . ($name ?: ($acc['email'] ?? '#' . ($acc['id'] ?? '')))
                . ($s->testMode() ? ' (modo de teste: nenhum dinheiro de verdade)' : '') . '.';
        } catch (\Throwable $e) {
            $this->testOk = false;
            $this->testResult = $e->getMessage();
        }
    }

    public function with(): array
    {
        $s = PaymentSetting::for(Auth::id());

        return [
            'webhookUrl' => route('payments.mercadopago.webhook', Auth::id()),
            'ready'      => $s?->onlineReady() ?? false,
            'needsPk'    => $this->card_enabled && blank($s?->public_key),
            'testMode'   => $s?->testMode() ?? false,
            'paidCount'  => \App\Models\PortalPayment::where('user_id', Auth::id())->where('status', 'approved')->count(),
            'byMethod'   => \App\Models\PortalPayment::where('user_id', Auth::id())->where('status', 'approved')
                ->selectRaw('method, count(*) as n')->groupBy('method')->pluck('n', 'method')->all(),
            'paidTotal'  => (float) \App\Models\PortalPayment::where('user_id', Auth::id())->where('status', 'approved')->sum('amount'),
        ];
    }
}; ?>

<section class="settings-payments-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

        <div class="s-pg-grid">
        <div class="s-col-main">

        <form wire:submit="save" style="display:flex;flex-direction:column;gap:1rem">

        {{-- CARD: Mercado Pago --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(0,158,227,.12);color:#009ee3">
                        <i class="bi bi-qr-code" style="font-size:1rem"></i>
                    </div>
                    <div>
                        <p class="settings-card-title">Mercado Pago: Pix, cartão e boleto</p>
                        <p class="settings-card-desc">O cliente paga no portal e o pedido vira venda paga sozinho</p>
                    </div>
                </div>
                @if($ready)
                    <span class="s-badge s-badge-success">{{ $testMode ? 'Ativo (teste)' : 'Ativo' }}</span>
                @else
                    <span class="s-badge">Desligado</span>
                @endif
            </div>

            <div style="padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:1rem">
                <label class="settings-notif-row" style="margin:0">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-power"></i></div>
                        <div>
                            <p class="settings-notif-title">Aceitar pagamento online no portal</p>
                            <p class="settings-notif-desc">Desligado, o portal continua recebendo pedidos para você combinar o pagamento</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch {{ $enabled ? 'active' : '' }}" wire:click="$toggle('enabled')">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

                <div class="settings-form-grid">
                    <div class="settings-form-full">
                        <label class="settings-field-label">Access Token (credencial de produção)</label>
                        @if($hasToken)
                            <div class="pay-saved">
                                <i class="bi bi-lock-fill"></i> Salvo, termina em <b>…{{ $tokenEnd }}</b>
                                <button type="button" wire:click="removeToken" wire:confirm="Remover as credenciais do Mercado Pago? O Pix sai do portal na hora." class="pay-link-danger">Remover</button>
                            </div>
                        @endif
                        <flux:input wire:model="access_token" type="password" autocomplete="off"
                            placeholder="{{ $hasToken ? 'Cole um novo só se quiser trocar' : 'APP_USR-...' }}"/>
                        @error('access_token') <p class="pay-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="settings-form-full">
                        <label class="settings-field-label">Public Key</label>
                        <flux:input wire:model="public_key" type="text" autocomplete="off" placeholder="APP_USR-..."/>
                        @error('public_key') <p class="pay-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="settings-form-full">
                        <label class="settings-field-label">Assinatura secreta do webhook <span class="s-badge s-badge-accent" style="margin-left:.35rem">Recomendado</span></label>
                        @if($hasSecret)
                            <div class="pay-saved"><i class="bi bi-lock-fill"></i> Salva</div>
                        @endif
                        <flux:input wire:model="webhook_secret" type="password" autocomplete="off"
                            placeholder="{{ $hasSecret ? 'Cole uma nova só se quiser trocar' : 'Chave que o Mercado Pago mostra em Webhooks' }}"/>
                        @error('webhook_secret') <p class="pay-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="settings-form-full">
                        <label class="settings-field-label">Endereço do webhook (cole no Mercado Pago, evento "Pagamentos")</label>
                        <div class="pay-copy" x-data="{ok:false}">
                            <code>{{ $webhookUrl }}</code>
                            <button type="button" @click="navigator.clipboard.writeText(@js($webhookUrl)); ok=true; setTimeout(()=>ok=false,1500)">
                                <i class="bi" :class="ok ? 'bi-check2' : 'bi-copy'"></i><span x-text="ok ? 'Copiado' : 'Copiar'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <label class="settings-notif-row" style="margin:0">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(0,158,227,.1);color:#009ee3"><i class="bi bi-qr-code-scan"></i></div>
                        <div>
                            <p class="settings-notif-title">Pix</p>
                            <p class="settings-notif-desc">QR Code e copia e cola, aprovado na hora</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch {{ $pix_enabled ? 'active' : '' }}" wire:click="$toggle('pix_enabled')">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

                <div class="settings-form-grid">
                    <div>
                        <label class="settings-field-label">Prazo para pagar o Pix (minutos)</label>
                        <flux:input wire:model="pix_minutes" type="number" min="10" max="1440" inputmode="numeric"/>
                        @error('pix_minutes') <p class="pay-err">{{ $message }}</p> @enderror
                    </div>
                </div>

                <label class="settings-notif-row" style="margin:0">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(52,131,250,.1);color:#3483fa"><i class="bi bi-credit-card"></i></div>
                        <div>
                            <p class="settings-notif-title">Cartão de crédito e débito</p>
                            <p class="settings-notif-desc">Formulário seguro do Mercado Pago; o número do cartão nunca passa pelo app. Precisa da Public Key</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch {{ $card_enabled ? 'active' : '' }}" wire:click="$toggle('card_enabled')" data-testid="toggle-card">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>
                @if($card_enabled)
                <div class="settings-form-grid">
                    <div>
                        <label class="settings-field-label">Parcelar em até</label>
                        <select wire:model="max_installments" class="settings-select">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">{{ $i === 1 ? 'Só à vista' : $i . 'x' }}</option>
                            @endfor
                        </select>
                        <p class="pay-hint">Os juros do parcelamento seguem o que está configurado na sua conta do Mercado Pago.</p>
                    </div>
                </div>
                @if($needsPk)
                    <p class="pay-err">Cole a Public Key acima para o cartão aparecer no portal.</p>
                @endif
                @endif

                <label class="settings-notif-row" style="margin:0">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(100,116,139,.12);color:#475569"><i class="bi bi-upc"></i></div>
                        <div>
                            <p class="settings-notif-title">Boleto</p>
                            <p class="settings-notif-desc">Confirmação em até 2 dias úteis depois que o cliente paga. O estoque só baixa quando o boleto é pago</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch {{ $boleto_enabled ? 'active' : '' }}" wire:click="$toggle('boleto_enabled')" data-testid="toggle-boleto">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>
                @if($boleto_enabled)
                <div class="settings-form-grid">
                    <div>
                        <label class="settings-field-label">Boleto vence em (dias)</label>
                        <flux:input wire:model="boleto_days" type="number" min="1" max="10" inputmode="numeric"/>
                        @error('boleto_days') <p class="pay-err">{{ $message }}</p> @enderror
                    </div>
                </div>
                @endif

                <label class="settings-notif-row" style="margin:0">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(245,158,11,.1);color:#f59e0b"><i class="bi bi-chat-dots"></i></div>
                        <div>
                            <p class="settings-notif-title">Deixar o cliente combinar o pagamento</p>
                            <p class="settings-notif-desc">Além do Pix, o cliente pode só enviar o pedido e pagar como vocês combinarem</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch {{ $allow_manual ? 'active' : '' }}" wire:click="$toggle('allow_manual')">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>
            </div>
        </div>

        {{-- CARD: Entrega --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="bi bi-truck" style="font-size:1rem"></i></div>
                    <div>
                        <p class="settings-card-title">Retirada e entrega</p>
                        <p class="settings-card-desc">O que o cliente escolhe no carrinho</p>
                    </div>
                </div>
            </div>
            <div style="padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:1rem">
                <label class="settings-notif-row" style="margin:0">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-shop"></i></div>
                        <div>
                            <p class="settings-notif-title">Retirar com você</p>
                            <p class="settings-notif-desc">Sem taxa</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch {{ $pickup_enabled ? 'active' : '' }}" wire:click="$toggle('pickup_enabled')">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>
                <label class="settings-notif-row" style="margin:0">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="bi bi-house-door"></i></div>
                        <div>
                            <p class="settings-notif-title">Entregar no endereço do cliente</p>
                            <p class="settings-notif-desc">O cliente precisa ter o endereço completo em Meus dados</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch {{ $delivery_enabled ? 'active' : '' }}" wire:click="$toggle('delivery_enabled')">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>
                @if($delivery_enabled)
                <div class="settings-form-grid">
                    <div>
                        <label class="settings-field-label">Taxa de entrega (R$)</label>
                        <flux:input wire:model="delivery_fee" type="text" inputmode="decimal" placeholder="0,00"/>
                        @error('delivery_fee') <p class="pay-err">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="settings-field-label">Aviso para o cliente</label>
                        <flux:input wire:model="delivery_note" type="text" placeholder="Ex.: entrego em até 2 dias úteis"/>
                        @error('delivery_note') <p class="pay-err">{{ $message }}</p> @enderror
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
            <button data-mobile-save type="submit" class="settings-save-btn">
                <i class="bi bi-check2"></i> Salvar
            </button>
            <button type="button" wire:click="testConnection" class="pay-test-btn" wire:loading.attr="disabled" wire:target="testConnection">
                <i class="bi bi-plug"></i>
                <span wire:loading.remove wire:target="testConnection">Testar conexão</span>
                <span wire:loading wire:target="testConnection">Testando...</span>
            </button>
            <x-action-message on="payments-saved">
                <span style="display:inline-flex;align-items:center;gap:.35rem;color:#059669;font-size:.83rem;font-weight:600"><i class="bi bi-check-circle"></i> Salvo!</span>
            </x-action-message>
        </div>
        @if($testResult)
            <div class="pay-test {{ $testOk ? 'ok' : 'bad' }}"><i class="bi {{ $testOk ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i> {{ $testResult }}</div>
        @endif

        </form>
        </div>{{-- /s-col-main --}}

        <div class="s-col-side">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-cash-coin" style="font-size:1rem"></i></div>
                        <div>
                            <p class="settings-card-title">Recebido pelo portal</p>
                            <p class="settings-card-desc">Pagamentos aprovados pelo site</p>
                        </div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:.75rem;padding:0 1.25rem 1.25rem">
                    <div class="settings-info-tile" style="grid-column:span 3;background:rgba(16,185,129,.08);border-color:transparent">
                        <p class="settings-info-tile-label">Pedidos pagos</p>
                        <p class="settings-info-tile-val" style="color:#10b981">{{ $paidCount }}</p>
                    </div>
                    <div class="settings-info-tile" style="grid-column:span 3;background:rgba(99,102,241,.08);border-color:transparent">
                        <p class="settings-info-tile-label">Total</p>
                        <p class="settings-info-tile-val" style="color:#6366f1">R$ {{ number_format($paidTotal, 2, ',', '.') }}</p>
                    </div>
                    @foreach(['pix' => 'Pix', 'cartao' => 'Cartão', 'boleto' => 'Boleto'] as $mk => $ml)
                    <div class="settings-info-tile" style="grid-column:span 2;background:rgba(148,163,184,.08);border-color:transparent">
                        <p class="settings-info-tile-label">{{ $ml }}</p>
                        <p class="settings-info-tile-val" style="color:#475569">{{ $byMethod[$mk] ?? 0 }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(245,158,11,.1);color:#f59e0b"><i class="bi bi-signpost-split" style="font-size:1rem"></i></div>
                        <div>
                            <p class="settings-card-title">Como pegar as credenciais</p>
                            <p class="settings-card-desc">Uma vez só, uns 5 minutos</p>
                        </div>
                    </div>
                </div>
                <ol class="pay-steps">
                    <li>Entre em <b>mercadopago.com.br/developers</b> com a conta que vai receber o dinheiro.</li>
                    <li><b>Suas integrações › Criar aplicação</b>. Tipo: pagamentos online, produto: Checkout Transparente (API).</li>
                    <li>Abra <b>Credenciais de produção</b> e copie o <b>Access Token</b> e a <b>Public Key</b> para cá (a Public Key é obrigatória para cartão).</li>
                    <li>Em <b>Webhooks</b>, cole o endereço acima, marque o evento <b>Pagamentos</b>, salve e copie a <b>assinatura secreta</b> para cá.</li>
                    <li>Salve e toque em <b>Testar conexão</b>.</li>
                </ol>
                <p class="pay-steps-note"><i class="bi bi-shield-lock"></i> As chaves ficam criptografadas e nunca aparecem de novo nesta tela. Não mande essas chaves por mensagem para ninguém.</p>
            </div>
        </div>{{-- /s-col-side --}}

        </div>{{-- /s-pg-grid --}}
    </x-settings.layout>

    <style>
        .settings-payments-page .pay-err{color:#ef4444;font-size:.73rem;margin-top:.2rem}
        .settings-payments-page .pay-hint{color:#64748b;font-size:.72rem;margin-top:.3rem}
        .settings-payments-page .pay-saved{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;font-size:.78rem;color:#059669;margin:.1rem 0 .35rem}
        .settings-payments-page .pay-link-danger{margin-left:auto;background:none;border:0;color:#ef4444;font-weight:600;font-size:.78rem;cursor:pointer;text-decoration:underline}
        .settings-payments-page .pay-copy{display:flex;align-items:center;gap:.5rem;border:1.5px dashed rgba(var(--s-accent-rgb),.3);border-radius:.7rem;padding:.55rem .6rem .55rem .8rem;background:rgba(var(--s-accent-rgb),.04)}
        .settings-payments-page .pay-copy code{flex:1;min-width:0;font-size:.76rem;word-break:break-all;color:inherit}
        .settings-payments-page .pay-copy button{display:inline-flex;align-items:center;gap:.3rem;flex-shrink:0;border:0;border-radius:.55rem;padding:.4rem .7rem;background:var(--s-accent);color:#fff;font-size:.76rem;font-weight:700;cursor:pointer}
        .settings-payments-page .pay-test-btn{display:inline-flex;align-items:center;gap:.4rem;border:1.5px solid rgba(var(--s-accent-rgb),.35);color:var(--s-accent);background:transparent;border-radius:.75rem;padding:.6rem 1rem;font-size:.85rem;font-weight:700;cursor:pointer}
        .settings-payments-page .pay-test{border-radius:.75rem;padding:.7rem .9rem;font-size:.83rem;font-weight:600;display:flex;gap:.5rem;align-items:flex-start}
        .settings-payments-page .pay-test.ok{background:rgba(16,185,129,.1);color:#047857}
        .settings-payments-page .pay-test.bad{background:rgba(239,68,68,.1);color:#b91c1c}
        .settings-payments-page .pay-steps{margin:0;padding:0 1.25rem 0 2.6rem;display:flex;flex-direction:column;gap:.55rem;font-size:.82rem;line-height:1.45;list-style:decimal}
        .settings-payments-page .pay-steps-note{margin:.9rem 1.25rem 1.25rem;font-size:.76rem;color:#64748b;display:flex;gap:.4rem}
        .dark .settings-payments-page .pay-test.ok{color:#6ee7b7}
        .dark .settings-payments-page .pay-test.bad{color:#fca5a5}
        .dark .settings-payments-page .pay-steps-note{color:#94a3b8}
    </style>
</section>
