<?php

use App\Models\FinanceCapture;
use App\Models\WhatsAppSetting;
use App\Services\WhatsApp\WhatsAppClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
    public bool $enabled = false;
    public bool $auto_save = true;
    public string $phone_number_id = '';
    public string $allowed_numbers = '';

    // Segredos: nunca voltam para a tela; vazio = manter o salvo
    public string $access_token = '';
    public string $app_secret = '';
    public bool $hasToken = false;
    public bool $hasSecret = false;
    public string $tokenEnd = '';

    public ?string $testResult = null;
    public bool $testOk = false;

    public function mount(): void
    {
        $s = WhatsAppSetting::forUser(Auth::id());
        $this->enabled = (bool) $s->enabled;
        $this->auto_save = (bool) $s->auto_save;
        $this->phone_number_id = (string) $s->phone_number_id;
        $this->allowed_numbers = (string) ($s->allowed_numbers ?: Auth::user()->phone);
        $this->refreshSecrets($s);
    }

    private function refreshSecrets(WhatsAppSetting $s): void
    {
        $this->hasToken = filled($s->access_token);
        $this->hasSecret = filled($s->app_secret);
        $this->tokenEnd = $this->hasToken ? substr($s->access_token, -4) : '';
    }

    public function save(): void
    {
        $this->phone_number_id = preg_replace('/\D/', '', $this->phone_number_id);
        $this->access_token = trim($this->access_token);
        $this->app_secret = trim($this->app_secret);

        $this->validate([
            'phone_number_id' => ['nullable', 'digits_between:8,20'],
            'access_token' => ['nullable', 'string', 'min:20', 'max:1000'],
            'app_secret' => ['nullable', 'string', 'regex:/^[a-f0-9]{32}$/i'],
            'allowed_numbers' => ['nullable', 'string', 'max:255'],
        ], [
            'phone_number_id.digits_between' => 'O identificador do número tem só dígitos (uns 15).',
            'access_token.min' => 'O token parece incompleto. Copie de novo.',
            'app_secret.regex' => 'A chave secreta do app tem 32 letras e números.',
        ]);

        $s = WhatsAppSetting::forUser(Auth::id());
        $numbers = collect(preg_split('/[\s,;]+/', $this->allowed_numbers) ?: [])
            ->map(fn ($n) => WhatsAppSetting::normalizeNumber($n))->filter(fn ($n) => strlen($n) >= 12)->unique()->values();
        if ($this->enabled && $numbers->isEmpty()) {
            $this->addError('allowed_numbers', 'Coloque o seu número com DDD. Só ele vai poder lançar.');

            return;
        }

        $s->fill([
            'enabled' => $this->enabled,
            'auto_save' => $this->auto_save,
            'phone_number_id' => $this->phone_number_id ?: null,
            'allowed_numbers' => $numbers->implode(', ') ?: null,
        ]);
        if ($this->access_token !== '') {
            $s->access_token = $this->access_token;
        }
        if ($this->app_secret !== '') {
            $s->app_secret = $this->app_secret;
        }
        $s->save();

        $this->allowed_numbers = (string) $s->allowed_numbers;
        $this->access_token = '';
        $this->app_secret = '';
        $this->refreshSecrets($s);
        $this->testResult = null;
        $this->dispatch('whatsapp-saved');
    }

    public function removeToken(): void
    {
        $s = WhatsAppSetting::forUser(Auth::id());
        $s->fill(['access_token' => null, 'app_secret' => null, 'enabled' => false])->save();
        $this->enabled = false;
        $this->refreshSecrets($s);
        $this->testResult = null;
    }

    public function newVerifyToken(): void
    {
        WhatsAppSetting::forUser(Auth::id())->update(['verify_token' => Str::random(32)]);
    }

    public function testConnection(): void
    {
        $s = WhatsAppSetting::forUser(Auth::id());
        if (blank($s->access_token) || blank($s->phone_number_id)) {
            $this->testOk = false;
            $this->testResult = 'Salve o identificador do número e o token antes de testar.';

            return;
        }
        try {
            $info = (new WhatsAppClient($s))->phoneInfo();
            $this->testOk = true;
            $this->testResult = 'Conectado ao número ' . ($info['display_phone_number'] ?? '?')
                . (! empty($info['verified_name']) ? ' (' . $info['verified_name'] . ')' : '') . '. Mande um print para ele para testar.';
        } catch (\Throwable $e) {
            $this->testOk = false;
            $this->testResult = $e->getMessage();
        }
    }

    public function with(): array
    {
        $s = WhatsAppSetting::forUser(Auth::id());

        return [
            'ready' => $s->ready(),
            'webhookUrl' => route('whatsapp.webhook', Auth::id()),
            'verifyToken' => $s->verify_token,
            'lastAt' => $s->last_message_at,
            'count' => FinanceCapture::where('user_id', Auth::id())->where('source', 'whatsapp')->where('status', 'saved')->count(),
        ];
    }
}; ?>

@php
    // Passos para ficar pronto: quem manda, dados da Meta, token e a primeira mensagem chegando
    $waSteps = [
        ['Seu número', filled($allowed_numbers), 'bi-person-check'],
        ['Identificador', filled($phone_number_id), 'bi-hash'],
        ['Token da Meta', $hasToken, 'bi-key'],
        ['Webhook recebendo', (bool) $lastAt, 'bi-broadcast'],
    ];
    $waDone = collect($waSteps)->where(1, true)->count();
@endphp

<section class="settings-whatsapp-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    <div class="wa-page">
        {{-- Topo: situação, passos e números --}}
        <div class="wa-hero">
            <span class="wa-glow"></span>
            <div class="wa-hero-ico"><i class="bi bi-whatsapp"></i></div>
            <div class="wa-hero-txt">
                <span class="wa-kicker"><i class="bi bi-receipt"></i> Lançar por print</span>
                <h2>{{ $ready ? 'Ligado e pronto para receber' : ($enabled ? 'Ligado, falta terminar de configurar' : 'Desligado') }}</h2>
                <p>Mande o print da notificação do banco para o número do app e ele vai para a fatura certa ou para o livro caixa.</p>
                <div class="wa-progress">
                    @foreach($waSteps as [$label, $ok, $icon])
                        <span class="{{ $ok ? 'is-ok' : '' }}"><i class="bi {{ $ok ? 'bi-check-circle-fill' : $icon }}"></i> {{ $label }}</span>
                    @endforeach
                </div>
            </div>
            <div class="wa-hero-stats">
                <div><b>{{ $count }}</b><small>lançados pelo WhatsApp</small></div>
                <div><b>{{ $lastAt ? $lastAt->locale('pt_BR')->diffForHumans(null, true) : '—' }}</b><small>{{ $lastAt ? 'desde a última mensagem' : 'nenhuma mensagem ainda' }}</small></div>
                <a href="{{ route('finance.print-capture') }}" wire:navigate class="wa-hero-btn"><i class="bi bi-phone"></i> Abrir Lançar por print</a>
            </div>
        </div>

        <div class="wa-grid">
        <form wire:submit="save" class="wa-main">

            {{-- 1. Ligar e quem pode mandar --}}
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="wa-num">1</span>
                    <div class="min-w-0">
                        <h2>Ligar e quem pode mandar</h2>
                        <p>Só os números daqui conseguem lançar. Mensagens de outros são ignoradas.</p>
                    </div>
                </div>
                <button type="button" class="wa-switch-row {{ $enabled ? 'is-on' : '' }}" wire:click="$toggle('enabled')" data-testid="toggle-wa">
                    <span class="wa-sw-ico"><i class="bi bi-power"></i></span>
                    <span class="wa-sw-txt"><b>Receber prints pelo WhatsApp</b><small>{{ $enabled ? 'Ligado' : 'Desligado' }}. Lembre de salvar depois de mudar.</small></span>
                    <span class="wa-switch"><span></span></span>
                </button>
                <div class="wa-field">
                    <label class="settings-field-label">Seu WhatsApp (quem pode mandar)</label>
                    <flux:input wire:model="allowed_numbers" type="text" inputmode="tel" placeholder="(15) 99999-9999"/>
                    <p class="wa-hint">Com DDD. Mais de um número: separe por vírgula.</p>
                    @error('allowed_numbers') <p class="wa-err">{{ $message }}</p> @enderror
                </div>
                <button type="button" class="wa-switch-row {{ $auto_save ? 'is-on' : '' }}" wire:click="$toggle('auto_save')">
                    <span class="wa-sw-ico is-indigo"><i class="bi bi-lightning-charge"></i></span>
                    <span class="wa-sw-txt"><b>Gravar direto quando entender tudo</b><small>{{ $auto_save ? 'Ligado: entra direto na fatura ou no livro caixa, e dá para desfazer.' : 'Desligado, tudo fica em Lançar por print para você conferir antes.' }}</small></span>
                    <span class="wa-switch"><span></span></span>
                </button>
            </div>

            {{-- 2. Dados da Meta --}}
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="wa-num">2</span>
                    <div class="min-w-0">
                        <h2>Dados do app da Meta</h2>
                        <p>Copiados do developers.facebook.com. O token e a chave ficam guardados criptografados e não aparecem de novo.</p>
                    </div>
                </div>
                <div class="wa-fields">
                    <div class="wa-field">
                        <label class="settings-field-label">Identificador do número de telefone</label>
                        <flux:input wire:model="phone_number_id" type="text" inputmode="numeric" autocomplete="off" placeholder="Ex.: 123456789012345"/>
                        @error('phone_number_id') <p class="wa-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="wa-field">
                        <label class="settings-field-label">Token de acesso (permanente)</label>
                        @if($hasToken)
                            <div class="wa-saved"><i class="bi bi-lock-fill"></i> Salvo, termina em <b>…{{ $tokenEnd }}</b>
                                <button type="button" wire:click="removeToken" wire:confirm="Remover o token? O WhatsApp para de lançar na hora." class="wa-link-danger">Remover</button>
                            </div>
                        @endif
                        <flux:input wire:model="access_token" type="password" autocomplete="off" placeholder="{{ $hasToken ? 'Cole um novo só se quiser trocar' : 'EAAG...' }}"/>
                        @error('access_token') <p class="wa-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="wa-field wa-field-full">
                        <label class="settings-field-label">Chave secreta do app <span class="wa-pill">Recomendado</span></label>
                        @if($hasSecret)<div class="wa-saved"><i class="bi bi-lock-fill"></i> Salva</div>@endif
                        <flux:input wire:model="app_secret" type="password" autocomplete="off" placeholder="{{ $hasSecret ? 'Cole uma nova só se quiser trocar' : '32 letras e números' }}"/>
                        <p class="wa-hint">Com ela o app só aceita mensagens assinadas pela Meta.</p>
                        @error('app_secret') <p class="wa-err">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- 3. Webhook --}}
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="wa-num">3</span>
                    <div class="min-w-0">
                        <h2>Colar na Meta (Webhook)</h2>
                        <p>Em WhatsApp › Configuração › Webhook, cole os dois e assine o campo "messages".</p>
                    </div>
                </div>
                <div class="wa-fields">
                    <div class="wa-field wa-field-full">
                        <label class="settings-field-label">URL de callback</label>
                        <div class="wa-copy" x-data="{ok:false}">
                            <code>{{ $webhookUrl }}</code>
                            <button type="button" @click="navigator.clipboard.writeText(@js($webhookUrl)); ok=true; setTimeout(()=>ok=false,1500)"><i class="bi" :class="ok ? 'bi-check2' : 'bi-copy'"></i><span x-text="ok ? 'Copiado' : 'Copiar'"></span></button>
                        </div>
                    </div>
                    <div class="wa-field wa-field-full">
                        <label class="settings-field-label">Token de verificação</label>
                        <div class="wa-copy" x-data="{ok:false}">
                            <code>{{ $verifyToken }}</code>
                            <button type="button" @click="navigator.clipboard.writeText(@js($verifyToken)); ok=true; setTimeout(()=>ok=false,1500)"><i class="bi" :class="ok ? 'bi-check2' : 'bi-copy'"></i><span x-text="ok ? 'Copiado' : 'Copiar'"></span></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wa-actions">
                <button data-mobile-save type="submit" class="wa-btn-save"><i class="bi bi-check2"></i> Salvar</button>
                <button type="button" wire:click="testConnection" class="wa-btn-test" wire:loading.attr="disabled" wire:target="testConnection">
                    <i class="bi bi-plug"></i><span wire:loading.remove wire:target="testConnection">Testar conexão</span><span wire:loading wire:target="testConnection">Testando...</span>
                </button>
                <x-action-message on="whatsapp-saved"><span class="wa-ok-msg"><i class="bi bi-check-circle"></i> Salvo!</span></x-action-message>
            </div>
            @if($testResult)
                <div class="wa-test {{ $testOk ? 'ok' : 'bad' }}"><i class="bi {{ $testOk ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i> {{ $testResult }}</div>
            @endif
        </form>

        <aside class="wa-side">
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="ap-ico bg-gradient-to-br from-amber-500 to-orange-500"><i class="bi bi-signpost-split"></i></span>
                    <div class="min-w-0">
                        <h2>Como ligar</h2>
                        <p>Uma vez só, uns 20 minutos.</p>
                    </div>
                </div>
                <ol class="wa-steps">
                    <li>Entre em <b>developers.facebook.com</b>, <b>Meus apps › Criar app</b>, tipo <b>Empresa</b>, e adicione o produto <b>WhatsApp</b>.</li>
                    <li>Em <b>WhatsApp › Configuração da API</b> a Meta dá um número de teste grátis. Em "Para", cadastre o seu celular.</li>
                    <li class="{{ filled($phone_number_id) ? 'is-done' : '' }}">Copie o <b>Identificador do número de telefone</b> para o passo 2.</li>
                    <li class="{{ $hasToken ? 'is-done' : '' }}">Gere um <b>token permanente</b>: Configurações do negócio › Usuários do sistema › Adicionar (admin) › Gerar token, com <b>whatsapp_business_messaging</b> e <b>whatsapp_business_management</b>. O token da tela da API vence em 24 horas.</li>
                    <li class="{{ $hasSecret ? 'is-done' : '' }}">Em <b>Configurações do app › Básico</b>, copie a <b>Chave secreta do app</b>.</li>
                    <li class="{{ $lastAt ? 'is-done' : '' }}">Salve aqui. Na Meta, em <b>WhatsApp › Configuração › Webhook</b>, cole a URL de callback e o token de verificação, e assine o campo <b>messages</b>.</li>
                    <li class="{{ $count ? 'is-done' : '' }}">Salve o número do app nos contatos e mande <b>ajuda</b>.</li>
                </ol>
                <p class="wa-steps-note"><i class="bi bi-shield-lock"></i> Não mande o token nem a chave por mensagem para ninguém.</p>
            </div>
        </aside>
        </div>
    </div>

    <style>
        .wa-page { display: flex; flex-direction: column; gap: 1rem; }
        .wa-hero { position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem; padding: 1.4rem 1.6rem; border-radius: 1.6rem; color: #fff; background: linear-gradient(135deg, #25d366 0%, #128c7e 55%, #075e54 100%); box-shadow: 0 24px 50px -28px rgba(7, 94, 84, .8); }
        .wa-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, .3), transparent 40%); }
        .wa-hero-ico { position: relative; flex: none; display: grid; place-items: center; width: 4rem; height: 4rem; border-radius: 1.2rem; background: rgba(255, 255, 255, .18); border: 1px solid rgba(255, 255, 255, .3); font-size: 1.9rem; }
        .wa-hero-txt { position: relative; flex: 1; min-width: 0; }
        .wa-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .92; }
        .wa-hero-txt h2 { font-size: 1.4rem; font-weight: 900; line-height: 1.2; margin-top: .2rem; }
        .wa-hero-txt p { font-size: .8rem; opacity: .92; max-width: 40rem; }
        .wa-progress { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .6rem; }
        .wa-progress span { display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .6rem; border-radius: 999px; background: rgba(0, 0, 0, .15); border: 1px dashed rgba(255, 255, 255, .4); font-size: .72rem; font-weight: 700; opacity: .85; }
        .wa-progress span.is-ok { background: rgba(255, 255, 255, .95); color: #047857; border-style: solid; border-color: transparent; opacity: 1; }
        .wa-hero-stats { position: relative; flex: none; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; width: 19rem; }
        .wa-hero-stats > div { padding: .6rem .7rem; border-radius: .9rem; background: rgba(255, 255, 255, .15); border: 1px solid rgba(255, 255, 255, .25); }
        .wa-hero-stats b { display: block; font-size: 1.15rem; font-weight: 900; line-height: 1.15; }
        .wa-hero-stats small { display: block; font-size: .66rem; opacity: .9; line-height: 1.25; }
        .wa-hero-btn { grid-column: 1 / -1; display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .6rem; border-radius: .9rem; background: #fff; color: #047857; font-size: .82rem; font-weight: 800; }
        .wa-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1rem; align-items: start; }
        .wa-main { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .wa-side { position: sticky; top: 1rem; min-width: 0; }
        .wa-num { flex: none; display: grid; place-items: center; width: 2.3rem; height: 2.3rem; border-radius: .8rem; background: linear-gradient(135deg, #25d366, #128c7e); color: #fff; font-weight: 900; box-shadow: 0 8px 18px -8px rgba(18, 140, 126, .6); }
        .wa-switch-row { width: 100%; display: flex; align-items: center; gap: .75rem; padding: .75rem .85rem; border-radius: 1rem; border: 1px solid #eef2f7; background: #f8fafc; text-align: left; margin-bottom: .75rem; transition: all .15s; }
        .wa-switch-row.is-on { background: #fff; border-color: #a7f3d0; box-shadow: 0 8px 20px -16px #059669; }
        .dark .wa-switch-row { background: #111827; border-color: #1f2937; }
        .dark .wa-switch-row.is-on { background: rgba(16, 185, 129, .08); border-color: rgba(16, 185, 129, .35); box-shadow: none; }
        .wa-sw-ico { flex: none; display: grid; place-items: center; width: 2.3rem; height: 2.3rem; border-radius: .8rem; background: #dcfce7; color: #16a34a; font-size: 1rem; }
        .wa-sw-ico.is-indigo { background: var(--color-indigo-50); color: var(--color-indigo-600); }
        .dark .wa-sw-ico { background: rgba(16, 185, 129, .15); }
        .wa-sw-txt { display: block; flex: 1; min-width: 0; }
        .wa-sw-txt b { display: block; font-size: .88rem; color: #0f172a; }
        .wa-sw-txt small { display: block; font-size: .73rem; color: #64748b; }
        .dark .wa-sw-txt b { color: #f1f5f9; }
        .wa-switch { flex: none; position: relative; width: 2.6rem; height: 1.5rem; border-radius: 999px; background: #cbd5e1; transition: background .2s; }
        .wa-switch span { position: absolute; top: .18rem; left: .18rem; width: 1.14rem; height: 1.14rem; border-radius: 999px; background: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, .25); transition: transform .2s; }
        .wa-switch-row.is-on .wa-switch { background: #10b981; }
        .wa-switch-row.is-on .wa-switch span { transform: translateX(1.1rem); }
        .dark .wa-switch { background: #334155; }
        .wa-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .9rem; }
        .wa-field { min-width: 0; margin-bottom: .75rem; }
        .wa-fields .wa-field { margin-bottom: 0; }
        .wa-field-full { grid-column: 1 / -1; }
        .wa-pill { margin-left: .35rem; padding: .05rem .45rem; border-radius: 999px; background: var(--color-indigo-50); color: var(--color-indigo-600); font-size: .66rem; font-weight: 800; }
        .wa-err { color: #ef4444; font-size: .73rem; margin-top: .2rem; }
        .wa-hint { color: #64748b; font-size: .72rem; margin-top: .3rem; }
        .wa-saved { display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; font-size: .78rem; color: #059669; margin: .1rem 0 .35rem; }
        .wa-link-danger { margin-left: auto; background: none; border: 0; color: #ef4444; font-weight: 600; font-size: .78rem; cursor: pointer; text-decoration: underline; }
        .wa-copy { display: flex; align-items: center; gap: .5rem; border: 1.5px dashed #a7f3d0; border-radius: .8rem; padding: .55rem .6rem .55rem .8rem; background: #f0fdf4; }
        .dark .wa-copy { background: rgba(16, 185, 129, .06); border-color: rgba(16, 185, 129, .3); }
        .wa-copy code { flex: 1; min-width: 0; font-size: .76rem; word-break: break-all; color: inherit; }
        .wa-copy button { display: inline-flex; align-items: center; gap: .3rem; flex-shrink: 0; border: 0; border-radius: .6rem; padding: .4rem .7rem; background: #10b981; color: #fff; font-size: .76rem; font-weight: 700; cursor: pointer; }
        .wa-actions { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
        .wa-btn-save { display: inline-flex; align-items: center; gap: .4rem; padding: .7rem 1.4rem; border-radius: .9rem; background: linear-gradient(135deg, #10b981, #047857); color: #fff; font-size: .88rem; font-weight: 800; box-shadow: 0 12px 24px -14px #047857; }
        .wa-btn-test { display: inline-flex; align-items: center; gap: .4rem; padding: .66rem 1.1rem; border-radius: .9rem; border: 1.5px solid #a7f3d0; color: #047857; background: #fff; font-size: .85rem; font-weight: 800; }
        .dark .wa-btn-test { background: #111827; color: #6ee7b7; border-color: rgba(16, 185, 129, .35); }
        .wa-ok-msg { display: inline-flex; align-items: center; gap: .35rem; color: #059669; font-size: .83rem; font-weight: 700; }
        .wa-test { border-radius: .9rem; padding: .7rem .9rem; font-size: .83rem; font-weight: 600; display: flex; gap: .5rem; align-items: flex-start; }
        .wa-test.ok { background: rgba(16, 185, 129, .1); color: #047857; }
        .wa-test.bad { background: rgba(239, 68, 68, .1); color: #b91c1c; }
        .dark .wa-test.ok { color: #6ee7b7; }
        .dark .wa-test.bad { color: #fca5a5; }
        .wa-steps { counter-reset: wa; display: flex; flex-direction: column; gap: .6rem; font-size: .8rem; line-height: 1.45; color: #334155; }
        .wa-steps li { counter-increment: wa; position: relative; padding-left: 2.1rem; }
        .wa-steps li::before { content: counter(wa); position: absolute; left: 0; top: 0; display: grid; place-items: center; width: 1.5rem; height: 1.5rem; border-radius: 999px; background: #f1f5f9; color: #475569; font-size: .72rem; font-weight: 900; }
        .wa-steps li.is-done::before { content: '✓'; background: #10b981; color: #fff; }
        .wa-steps li.is-done { color: #94a3b8; }
        .dark .wa-steps { color: #cbd5e1; }
        .dark .wa-steps li::before { background: #1e293b; color: #cbd5e1; }
        .wa-steps-note { margin-top: .9rem; font-size: .74rem; color: #64748b; display: flex; gap: .4rem; }
        @media (max-width: 1535px) { .wa-hero { flex-wrap: wrap; } .wa-hero-stats { width: 100%; grid-template-columns: repeat(3, minmax(0, 1fr)); } .wa-hero-btn { grid-column: auto; } }
        @media (max-width: 1279px) { .wa-grid { grid-template-columns: minmax(0, 1fr) 290px; } .wa-fields { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 1023px) { .wa-grid { grid-template-columns: minmax(0, 1fr); } .wa-side { position: static; } .wa-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .wa-hero { padding: 1.1rem; border-radius: 1.3rem; gap: .9rem; }
            .wa-hero-ico { width: 3.2rem; height: 3.2rem; font-size: 1.45rem; }
            .wa-hero-txt { flex-basis: calc(100% - 4.5rem); }
            .wa-hero-txt h2 { font-size: 1.1rem; }
            .wa-hero-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .wa-hero-btn { grid-column: 1 / -1; }
            .wa-fields { grid-template-columns: minmax(0, 1fr); }
            .wa-actions > button { flex: 1; justify-content: center; }
        }
    </style>
    </x-settings.layout>
</section>
