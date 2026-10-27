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
    @php
        $pySteps = [
            ['Access Token', $hasToken, 'bi-key'],
            ['Public Key', filled($public_key), 'bi-credit-card-2-front'],
            ['Webhook', $hasSecret, 'bi-broadcast'],
            ['Ligado', $enabled, 'bi-power'],
        ];
        $pyMethods = collect([$pix_enabled ? 'Pix' : null, $card_enabled ? 'Cartão' : null, $boleto_enabled ? 'Boleto' : null, $allow_manual ? 'Combinar' : null])->filter()->implode(', ');
    @endphp

    <div class="py-page">
        {{-- Topo: situação, passos e números --}}
        <div class="py-hero">
            <span class="py-glow"></span>
            <div class="py-hero-ico"><i class="bi bi-qr-code"></i></div>
            <div class="py-hero-txt">
                <span class="py-kicker"><i class="bi bi-shop-window"></i> Pagamentos do portal</span>
                <h2>{{ $ready ? ($testMode ? 'Ligado em modo de teste' : 'Ligado e recebendo') : ($enabled ? 'Ligado, falta terminar de configurar' : 'Desligado') }}</h2>
                <p>Com o Mercado Pago o cliente paga no portal por Pix, cartão ou boleto e o pedido vira venda paga sozinho, com baixa no estoque.</p>
                <div class="py-progress">
                    @foreach($pySteps as [$label, $ok, $icon])
                        <span class="{{ $ok ? 'is-ok' : '' }}"><i class="bi {{ $ok ? 'bi-check-circle-fill' : $icon }}"></i> {{ $label }}</span>
                    @endforeach
                </div>
            </div>
            <div class="py-hero-stats">
                <div><b>{{ $paidCount }}</b><small>pedidos pagos pelo portal</small></div>
                <div><b>R$ {{ number_format($paidTotal, 2, ',', '.') }}</b><small>recebido no total</small></div>
                <div class="py-hero-mini">
                    @foreach(['pix' => ['Pix', 'bi-qr-code-scan'], 'cartao' => ['Cartão', 'bi-credit-card'], 'boleto' => ['Boleto', 'bi-upc']] as $mk => [$ml, $mi])
                        <span><i class="bi {{ $mi }}"></i> {{ $ml }} <b>{{ $byMethod[$mk] ?? 0 }}</b></span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="py-grid">
        <div class="py-main">
        <form wire:submit="save" class="py-main">

            {{-- 1. Ligar e credenciais --}}
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="py-num">1</span>
                    <div class="min-w-0">
                        <h2>Conta do Mercado Pago</h2>
                        <p>As chaves da conta que vai receber o dinheiro. Ficam criptografadas e não aparecem de novo.</p>
                    </div>
                </div>
                <button type="button" class="py-switch-row {{ $enabled ? 'is-on' : '' }}" wire:click="$toggle('enabled')" data-testid="toggle-online">
                    <span class="py-sw-ico"><i class="bi bi-power"></i></span>
                    <span class="py-sw-txt"><b>Aceitar pagamento online no portal</b><small>{{ $enabled ? 'Ligado.' : 'Desligado: o portal continua recebendo pedidos para vocês combinarem o pagamento.' }} Lembre de salvar depois de mudar.</small></span>
                    <span class="py-switch"><span></span></span>
                </button>
                <div class="py-fields">
                    <div class="py-field py-field-full">
                        <label class="settings-field-label">Access Token (credencial de produção)</label>
                        @if($hasToken)
                            <div class="py-saved">
                                <i class="bi bi-lock-fill"></i> Salvo, termina em <b>…{{ $tokenEnd }}</b>
                                <button type="button" wire:click="removeToken" wire:confirm="Remover as credenciais do Mercado Pago? O Pix sai do portal na hora." class="py-link-danger">Remover</button>
                            </div>
                        @endif
                        <flux:input wire:model="access_token" type="password" autocomplete="off"
                            placeholder="{{ $hasToken ? 'Cole um novo só se quiser trocar' : 'APP_USR-...' }}"/>
                        @error('access_token') <p class="py-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="py-field">
                        <label class="settings-field-label">Public Key <span class="py-pill">Para cartão</span></label>
                        <flux:input wire:model="public_key" type="text" autocomplete="off" placeholder="APP_USR-..."/>
                        @error('public_key') <p class="py-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="py-field">
                        <label class="settings-field-label">Assinatura secreta do webhook <span class="py-pill">Recomendado</span></label>
                        @if($hasSecret)
                            <div class="py-saved"><i class="bi bi-lock-fill"></i> Salva</div>
                        @endif
                        <flux:input wire:model="webhook_secret" type="password" autocomplete="off"
                            placeholder="{{ $hasSecret ? 'Cole uma nova só se quiser trocar' : 'Chave que o Mercado Pago mostra em Webhooks' }}"/>
                        @error('webhook_secret') <p class="py-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="py-field py-field-full">
                        <label class="settings-field-label">Endereço do webhook (cole no Mercado Pago, evento "Pagamentos")</label>
                        <div class="py-copy" x-data="{ok:false}">
                            <code>{{ $webhookUrl }}</code>
                            <button type="button" @click="navigator.clipboard.writeText(@js($webhookUrl)); ok=true; setTimeout(()=>ok=false,1500)">
                                <i class="bi" :class="ok ? 'bi-check2' : 'bi-copy'"></i><span x-text="ok ? 'Copiado' : 'Copiar'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Formas de pagamento --}}
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="py-num">2</span>
                    <div class="min-w-0">
                        <h2>Formas de pagamento</h2>
                        <p>{{ $pyMethods !== '' ? 'O cliente vê: ' . $pyMethods . '.' : 'Nenhuma forma ligada.' }}</p>
                    </div>
                </div>
                <div class="py-methods">
                    <div class="py-method {{ $pix_enabled ? 'is-on' : '' }}">
                        <button type="button" class="py-switch-row" wire:click="$toggle('pix_enabled')" data-testid="toggle-pix">
                            <span class="py-sw-ico is-pix"><i class="bi bi-qr-code-scan"></i></span>
                            <span class="py-sw-txt"><b>Pix</b><small>QR Code e copia e cola, aprovado na hora</small></span>
                            <span class="py-switch"><span></span></span>
                        </button>
                        @if($pix_enabled)
                        <div class="py-method-opts">
                            <label class="settings-field-label">Prazo para pagar o Pix (minutos)</label>
                            <flux:input wire:model="pix_minutes" type="number" min="10" max="1440" inputmode="numeric"/>
                            @error('pix_minutes') <p class="py-err">{{ $message }}</p> @enderror
                        </div>
                        @endif
                    </div>

                    <div class="py-method {{ $card_enabled ? 'is-on' : '' }}">
                        <button type="button" class="py-switch-row" wire:click="$toggle('card_enabled')" data-testid="toggle-card">
                            <span class="py-sw-ico is-card"><i class="bi bi-credit-card"></i></span>
                            <span class="py-sw-txt"><b>Cartão de crédito e débito</b><small>Formulário seguro do Mercado Pago; o número do cartão nunca passa pelo app</small></span>
                            <span class="py-switch"><span></span></span>
                        </button>
                        @if($card_enabled)
                        <div class="py-method-opts">
                            <label class="settings-field-label">Parcelar em até</label>
                            <select wire:model="max_installments" class="settings-select">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ $i === 1 ? 'Só à vista' : $i . 'x' }}</option>
                                @endfor
                            </select>
                            <p class="py-hint">Os juros do parcelamento seguem o que está configurado na sua conta do Mercado Pago.</p>
                            @if($needsPk)
                                <p class="py-err"><i class="bi bi-exclamation-triangle"></i> Cole a Public Key no passo 1 para o cartão aparecer no portal.</p>
                            @endif
                        </div>
                        @endif
                    </div>

                    <div class="py-method {{ $boleto_enabled ? 'is-on' : '' }}">
                        <button type="button" class="py-switch-row" wire:click="$toggle('boleto_enabled')" data-testid="toggle-boleto">
                            <span class="py-sw-ico is-gray"><i class="bi bi-upc"></i></span>
                            <span class="py-sw-txt"><b>Boleto</b><small>Confirma em até 2 dias úteis depois do pagamento; o estoque só baixa quando o boleto é pago</small></span>
                            <span class="py-switch"><span></span></span>
                        </button>
                        @if($boleto_enabled)
                        <div class="py-method-opts">
                            <label class="settings-field-label">Boleto vence em (dias)</label>
                            <flux:input wire:model="boleto_days" type="number" min="1" max="10" inputmode="numeric"/>
                            @error('boleto_days') <p class="py-err">{{ $message }}</p> @enderror
                        </div>
                        @endif
                    </div>

                    <div class="py-method {{ $allow_manual ? 'is-on' : '' }}">
                        <button type="button" class="py-switch-row" wire:click="$toggle('allow_manual')" data-testid="toggle-manual">
                            <span class="py-sw-ico is-amber"><i class="bi bi-chat-dots"></i></span>
                            <span class="py-sw-txt"><b>Deixar o cliente combinar o pagamento</b><small>O cliente pode só enviar o pedido e pagar como vocês combinarem</small></span>
                            <span class="py-switch"><span></span></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 3. Retirada e entrega --}}
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="py-num">3</span>
                    <div class="min-w-0">
                        <h2>Retirada e entrega</h2>
                        <p>O que o cliente escolhe no carrinho. Pelo menos uma fica ligada.</p>
                    </div>
                </div>
                <div class="py-methods">
                    <div class="py-method {{ $pickup_enabled ? 'is-on' : '' }}">
                        <button type="button" class="py-switch-row" wire:click="$toggle('pickup_enabled')" data-testid="toggle-pickup">
                            <span class="py-sw-ico is-green"><i class="bi bi-shop"></i></span>
                            <span class="py-sw-txt"><b>Retirar com você</b><small>Sem taxa</small></span>
                            <span class="py-switch"><span></span></span>
                        </button>
                    </div>
                    <div class="py-method {{ $delivery_enabled ? 'is-on' : '' }}">
                        <button type="button" class="py-switch-row" wire:click="$toggle('delivery_enabled')" data-testid="toggle-delivery">
                            <span class="py-sw-ico is-indigo"><i class="bi bi-house-door"></i></span>
                            <span class="py-sw-txt"><b>Entregar no endereço do cliente</b><small>O cliente precisa ter o endereço completo em Meus dados</small></span>
                            <span class="py-switch"><span></span></span>
                        </button>
                        @if($delivery_enabled)
                        <div class="py-method-opts py-fields">
                            <div class="py-field">
                                <label class="settings-field-label">Taxa de entrega padrão (R$)</label>
                                <flux:input wire:model="delivery_fee" type="text" inputmode="decimal" placeholder="0,00"/>
                                @error('delivery_fee') <p class="py-err">{{ $message }}</p> @enderror
                            </div>
                            <div class="py-field">
                                <label class="settings-field-label">Aviso para o cliente</label>
                                <flux:input wire:model="delivery_note" type="text" placeholder="Ex.: entrego em até 2 dias úteis"/>
                                @error('delivery_note') <p class="py-err">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="py-actions">
                <button data-mobile-save type="submit" class="py-btn-save"><i class="bi bi-check2"></i> Salvar</button>
                <button type="button" wire:click="testConnection" class="py-btn-test" wire:loading.attr="disabled" wire:target="testConnection">
                    <i class="bi bi-plug"></i>
                    <span wire:loading.remove wire:target="testConnection">Testar conexão</span>
                    <span wire:loading wire:target="testConnection">Testando...</span>
                </button>
                <x-action-message on="payments-saved"><span class="py-ok-msg"><i class="bi bi-check-circle"></i> Salvo!</span></x-action-message>
            </div>
            @if($testResult)
                <div class="py-test {{ $testOk ? 'ok' : 'bad' }}"><i class="bi {{ $testOk ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i> {{ $testResult }}</div>
            @endif
        </form>

        <div class="py-zones">
            <livewire:portal.shipping-zones />
        </div>
        </div>{{-- /py-main --}}

        <aside class="py-side">
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="ap-ico bg-gradient-to-br from-amber-500 to-orange-500"><i class="bi bi-signpost-split"></i></span>
                    <div class="min-w-0">
                        <h2>Como pegar as credenciais</h2>
                        <p>Uma vez só, uns 5 minutos.</p>
                    </div>
                </div>
                <ol class="py-steps">
                    <li class="{{ $hasToken ? 'is-done' : '' }}">Entre em <b>mercadopago.com.br/developers</b> com a conta que vai receber o dinheiro.</li>
                    <li class="{{ $hasToken ? 'is-done' : '' }}"><b>Suas integrações › Criar aplicação</b>. Tipo: pagamentos online, produto: Checkout Transparente (API).</li>
                    <li class="{{ $hasToken && filled($public_key) ? 'is-done' : '' }}">Abra <b>Credenciais de produção</b> e copie o <b>Access Token</b> e a <b>Public Key</b> para o passo 1 (a Public Key é obrigatória para cartão).</li>
                    <li class="{{ $hasSecret ? 'is-done' : '' }}">Em <b>Webhooks</b>, cole o endereço do passo 1, marque o evento <b>Pagamentos</b>, salve e copie a <b>assinatura secreta</b> para cá.</li>
                    <li class="{{ $testOk ? 'is-done' : '' }}">Salve e toque em <b>Testar conexão</b>.</li>
                </ol>
                <p class="py-steps-note"><i class="bi bi-shield-lock"></i> Não mande essas chaves por mensagem para ninguém.</p>
            </div>
        </aside>
        </div>{{-- /py-grid --}}
    </div>

    <style>
        /* usados pelo Frete por região (livewire portal.shipping-zones) */
        .settings-payments-page .pay-err { color: #ef4444; font-size: .73rem; margin-top: .2rem; }
        .settings-payments-page .pay-test-btn { display: inline-flex; align-items: center; gap: .4rem; flex-shrink: 0; border: 1.5px solid #bae6fd; color: #0369a1; background: #fff; border-radius: .8rem; padding: .55rem .95rem; font-size: .85rem; font-weight: 800; cursor: pointer; }
        .dark .settings-payments-page .pay-test-btn { background: #111827; color: #7dd3fc; border-color: rgba(14, 165, 233, .35); }
        .py-page { display: flex; flex-direction: column; gap: 1rem; }
        .py-hero { position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem; padding: 1.4rem 1.6rem; border-radius: 1.6rem; color: #fff; background: linear-gradient(135deg, #00b1ea 0%, #009ee3 45%, #0a4ea1 100%); box-shadow: 0 24px 50px -28px rgba(10, 78, 161, .8); }
        .py-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, .3), transparent 40%); }
        .py-hero-ico { position: relative; flex: none; display: grid; place-items: center; width: 4rem; height: 4rem; border-radius: 1.2rem; background: rgba(255, 255, 255, .18); border: 1px solid rgba(255, 255, 255, .3); font-size: 1.9rem; }
        .py-hero-txt { position: relative; flex: 1; min-width: 0; }
        .py-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .92; }
        .py-hero-txt h2 { font-size: 1.4rem; font-weight: 900; line-height: 1.2; margin-top: .2rem; }
        .py-hero-txt p { font-size: .8rem; opacity: .92; max-width: 40rem; }
        .py-progress { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .6rem; }
        .py-progress span { display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .6rem; border-radius: 999px; background: rgba(0, 0, 0, .15); border: 1px dashed rgba(255, 255, 255, .4); font-size: .72rem; font-weight: 700; opacity: .85; }
        .py-progress span.is-ok { background: rgba(255, 255, 255, .95); color: #0a4ea1; border-style: solid; border-color: transparent; opacity: 1; }
        .py-hero-stats { position: relative; flex: none; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; width: 20rem; }
        .py-hero-stats > div { padding: .6rem .7rem; border-radius: .9rem; background: rgba(255, 255, 255, .15); border: 1px solid rgba(255, 255, 255, .25); min-width: 0; }
        .py-hero-stats b { display: block; font-size: 1.15rem; font-weight: 900; line-height: 1.15; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .py-hero-stats small { display: block; font-size: .66rem; opacity: .9; line-height: 1.25; }
        .py-hero-stats > .py-hero-mini { grid-column: 1 / -1; display: flex; justify-content: space-between; gap: .4rem; padding: .5rem .7rem; }
        .py-hero-mini span { display: inline-flex; align-items: center; gap: .3rem; font-size: .74rem; font-weight: 700; }
        .py-hero-mini b { display: inline; font-size: .9rem; }
        .py-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1rem; align-items: start; }
        .py-main { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .py-side { position: sticky; top: 1rem; min-width: 0; }
        .py-num { flex: none; display: grid; place-items: center; width: 2.3rem; height: 2.3rem; border-radius: .8rem; background: linear-gradient(135deg, #00b1ea, #0a4ea1); color: #fff; font-weight: 900; box-shadow: 0 8px 18px -8px rgba(10, 78, 161, .6); }
        .py-switch-row { width: 100%; display: flex; align-items: center; gap: .75rem; padding: .75rem .85rem; border-radius: 1rem; border: 1px solid #eef2f7; background: #f8fafc; text-align: left; margin-bottom: .75rem; transition: all .15s; cursor: pointer; }
        .py-switch-row.is-on { background: #fff; border-color: #bae6fd; box-shadow: 0 8px 20px -16px #0284c7; }
        .dark .py-switch-row { background: #111827; border-color: #1f2937; }
        .dark .py-switch-row.is-on { background: rgba(14, 165, 233, .08); border-color: rgba(14, 165, 233, .35); box-shadow: none; }
        .py-sw-ico { flex: none; display: grid; place-items: center; width: 2.3rem; height: 2.3rem; border-radius: .8rem; background: #dcfce7; color: #16a34a; font-size: 1rem; }
        .py-sw-ico.is-pix { background: #e0f2fe; color: #0284c7; }
        .py-sw-ico.is-card { background: #dbeafe; color: #2563eb; }
        .py-sw-ico.is-gray { background: #f1f5f9; color: #475569; }
        .py-sw-ico.is-amber { background: #fef3c7; color: #d97706; }
        .py-sw-ico.is-green { background: #dcfce7; color: #16a34a; }
        .py-sw-ico.is-indigo { background: var(--color-indigo-50); color: var(--color-indigo-600); }
        .dark .py-sw-ico { background: rgba(148, 163, 184, .12); }
        .py-sw-txt { display: block; flex: 1; min-width: 0; }
        .py-sw-txt b { display: block; font-size: .88rem; color: #0f172a; }
        .py-sw-txt small { display: block; font-size: .73rem; color: #64748b; }
        .dark .py-sw-txt b { color: #f1f5f9; }
        .py-switch { flex: none; position: relative; width: 2.6rem; height: 1.5rem; border-radius: 999px; background: #cbd5e1; transition: background .2s; }
        .py-switch span { position: absolute; top: .18rem; left: .18rem; width: 1.14rem; height: 1.14rem; border-radius: 999px; background: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, .25); transition: transform .2s; }
        .py-switch-row.is-on .py-switch, .py-method.is-on .py-switch { background: #0ea5e9; }
        .py-switch-row.is-on .py-switch span, .py-method.is-on .py-switch span { transform: translateX(1.1rem); }
        .dark .py-switch { background: #334155; }
        .py-methods { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
        .py-method { min-width: 0; border-radius: 1rem; border: 1px solid #eef2f7; background: #f8fafc; transition: all .15s; }
        .py-method .py-switch-row { margin: 0; border: 0; background: transparent; box-shadow: none; }
        .py-method.is-on { background: #fff; border-color: #bae6fd; box-shadow: 0 8px 20px -16px #0284c7; }
        .dark .py-method { background: #111827; border-color: #1f2937; }
        .dark .py-method.is-on { background: rgba(14, 165, 233, .08); border-color: rgba(14, 165, 233, .35); box-shadow: none; }
        .py-method-opts { padding: 0 .85rem .85rem; }
        .py-method-opts.py-fields { grid-template-columns: minmax(0, 1fr); gap: .6rem; }
        .py-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .9rem; }
        .py-field { min-width: 0; }
        .py-field-full { grid-column: 1 / -1; }
        .py-pill { margin-left: .35rem; padding: .05rem .45rem; border-radius: 999px; background: var(--color-indigo-50); color: var(--color-indigo-600); font-size: .66rem; font-weight: 800; }
        .py-err { color: #ef4444; font-size: .73rem; margin-top: .3rem; }
        .py-hint { color: #64748b; font-size: .72rem; margin-top: .3rem; }
        .py-saved { display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; font-size: .78rem; color: #059669; margin: .1rem 0 .35rem; }
        .py-link-danger { margin-left: auto; background: none; border: 0; color: #ef4444; font-weight: 600; font-size: .78rem; cursor: pointer; text-decoration: underline; }
        .py-copy { display: flex; align-items: center; gap: .5rem; border: 1.5px dashed #bae6fd; border-radius: .8rem; padding: .55rem .6rem .55rem .8rem; background: #f0f9ff; }
        .dark .py-copy { background: rgba(14, 165, 233, .06); border-color: rgba(14, 165, 233, .3); }
        .py-copy code { flex: 1; min-width: 0; font-size: .76rem; word-break: break-all; color: inherit; }
        .py-copy button { display: inline-flex; align-items: center; gap: .3rem; flex-shrink: 0; border: 0; border-radius: .6rem; padding: .4rem .7rem; background: #0ea5e9; color: #fff; font-size: .76rem; font-weight: 700; cursor: pointer; }
        .py-actions { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
        .py-btn-save { display: inline-flex; align-items: center; gap: .4rem; padding: .7rem 1.4rem; border-radius: .9rem; background: linear-gradient(135deg, #0ea5e9, #0a4ea1); color: #fff; font-size: .88rem; font-weight: 800; box-shadow: 0 12px 24px -14px #0a4ea1; }
        .py-btn-test { display: inline-flex; align-items: center; gap: .4rem; padding: .66rem 1.1rem; border-radius: .9rem; border: 1.5px solid #bae6fd; color: #0369a1; background: #fff; font-size: .85rem; font-weight: 800; }
        .dark .py-btn-test { background: #111827; color: #7dd3fc; border-color: rgba(14, 165, 233, .35); }
        .py-ok-msg { display: inline-flex; align-items: center; gap: .35rem; color: #059669; font-size: .83rem; font-weight: 700; }
        .py-test { border-radius: .9rem; padding: .7rem .9rem; font-size: .83rem; font-weight: 600; display: flex; gap: .5rem; align-items: flex-start; }
        .py-test.ok { background: rgba(16, 185, 129, .1); color: #047857; }
        .py-test.bad { background: rgba(239, 68, 68, .1); color: #b91c1c; }
        .dark .py-test.ok { color: #6ee7b7; }
        .dark .py-test.bad { color: #fca5a5; }
        .py-steps { counter-reset: py; display: flex; flex-direction: column; gap: .6rem; font-size: .8rem; line-height: 1.45; color: #334155; }
        .py-steps li { counter-increment: py; position: relative; padding-left: 2.1rem; }
        .py-steps li::before { content: counter(py); position: absolute; left: 0; top: 0; display: grid; place-items: center; width: 1.5rem; height: 1.5rem; border-radius: 999px; background: #f1f5f9; color: #475569; font-size: .72rem; font-weight: 900; }
        .py-steps li.is-done::before { content: '✓'; background: #0ea5e9; color: #fff; }
        .py-steps li.is-done { color: #94a3b8; }
        .dark .py-steps { color: #cbd5e1; }
        .dark .py-steps li::before { background: #1e293b; color: #cbd5e1; }
        .py-steps-note { margin-top: .9rem; font-size: .74rem; color: #64748b; display: flex; gap: .4rem; }
        @media (max-width: 1535px) { .py-hero { flex-wrap: wrap; } .py-hero-stats { width: 100%; grid-template-columns: repeat(3, minmax(0, 1fr)); } .py-hero-stats > .py-hero-mini { grid-column: auto; flex-direction: column; justify-content: center; gap: .1rem; } }
        @media (max-width: 1279px) { .py-grid { grid-template-columns: minmax(0, 1fr) 290px; } .py-methods, .py-fields { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 1023px) { .py-grid { grid-template-columns: minmax(0, 1fr); } .py-side { position: static; } .py-methods, .py-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .py-hero { padding: 1.1rem; border-radius: 1.3rem; gap: .9rem; }
            .py-hero-ico { width: 3.2rem; height: 3.2rem; font-size: 1.45rem; }
            .py-hero-txt { flex-basis: calc(100% - 4.5rem); }
            .py-hero-txt h2 { font-size: 1.1rem; }
            .py-hero-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .py-hero-stats > .py-hero-mini { grid-column: 1 / -1; flex-direction: row; justify-content: space-between; }
            .py-methods, .py-fields { grid-template-columns: minmax(0, 1fr); }
            .py-actions > button { flex: 1; justify-content: center; }
        }
    </style>
    </x-settings.layout>
</section>
