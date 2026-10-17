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

<section class="settings-whatsapp-page w-full mobile-393-base">
    <x-settings.layout :heading="''">
        <div class="s-pg-grid">
        <div class="s-col-main">
        <form wire:submit="save" style="display:flex;flex-direction:column;gap:1rem">

        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(37,211,102,.12);color:#16a34a"><i class="bi bi-whatsapp" style="font-size:1rem"></i></div>
                    <div>
                        <p class="settings-card-title">WhatsApp: lançar por print</p>
                        <p class="settings-card-desc">Mande o print da notificação do banco e ele vai para a fatura ou para o livro caixa</p>
                    </div>
                </div>
                @if($ready)
                    <span class="s-badge s-badge-success">Ativo</span>
                @else
                    <span class="s-badge">Desligado</span>
                @endif
            </div>

            <div style="padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:1rem">
                <label class="settings-notif-row" style="margin:0">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-power"></i></div>
                        <div>
                            <p class="settings-notif-title">Receber prints pelo WhatsApp</p>
                            <p class="settings-notif-desc">Só os números abaixo podem lançar</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch {{ $enabled ? 'active' : '' }}" wire:click="$toggle('enabled')" data-testid="toggle-wa">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>

                <div class="settings-form-grid">
                    <div class="settings-form-full">
                        <label class="settings-field-label">Seu WhatsApp (quem pode mandar)</label>
                        <flux:input wire:model="allowed_numbers" type="text" inputmode="tel" placeholder="(15) 99999-9999"/>
                        <p class="wa-hint">Com DDD. Mais de um número: separe por vírgula.</p>
                        @error('allowed_numbers') <p class="wa-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="settings-form-full">
                        <label class="settings-field-label">Identificador do número de telefone</label>
                        <flux:input wire:model="phone_number_id" type="text" inputmode="numeric" autocomplete="off" placeholder="Ex.: 123456789012345"/>
                        @error('phone_number_id') <p class="wa-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="settings-form-full">
                        <label class="settings-field-label">Token de acesso (permanente)</label>
                        @if($hasToken)
                            <div class="wa-saved"><i class="bi bi-lock-fill"></i> Salvo, termina em <b>…{{ $tokenEnd }}</b>
                                <button type="button" wire:click="removeToken" wire:confirm="Remover o token? O WhatsApp para de lançar na hora." class="wa-link-danger">Remover</button>
                            </div>
                        @endif
                        <flux:input wire:model="access_token" type="password" autocomplete="off" placeholder="{{ $hasToken ? 'Cole um novo só se quiser trocar' : 'EAAG...' }}"/>
                        @error('access_token') <p class="wa-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="settings-form-full">
                        <label class="settings-field-label">Chave secreta do app <span class="s-badge s-badge-accent" style="margin-left:.35rem">Recomendado</span></label>
                        @if($hasSecret)<div class="wa-saved"><i class="bi bi-lock-fill"></i> Salva</div>@endif
                        <flux:input wire:model="app_secret" type="password" autocomplete="off" placeholder="{{ $hasSecret ? 'Cole uma nova só se quiser trocar' : '32 letras e números' }}"/>
                        <p class="wa-hint">Com ela o app só aceita mensagens assinadas pela Meta.</p>
                        @error('app_secret') <p class="wa-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="settings-form-full">
                        <label class="settings-field-label">URL de callback (cole na Meta, em WhatsApp › Configuração › Webhook)</label>
                        <div class="wa-copy" x-data="{ok:false}">
                            <code>{{ $webhookUrl }}</code>
                            <button type="button" @click="navigator.clipboard.writeText(@js($webhookUrl)); ok=true; setTimeout(()=>ok=false,1500)"><i class="bi" :class="ok ? 'bi-check2' : 'bi-copy'"></i><span x-text="ok ? 'Copiado' : 'Copiar'"></span></button>
                        </div>
                    </div>
                    <div class="settings-form-full">
                        <label class="settings-field-label">Token de verificação (cole junto, na Meta)</label>
                        <div class="wa-copy" x-data="{ok:false}">
                            <code>{{ $verifyToken }}</code>
                            <button type="button" @click="navigator.clipboard.writeText(@js($verifyToken)); ok=true; setTimeout(()=>ok=false,1500)"><i class="bi" :class="ok ? 'bi-check2' : 'bi-copy'"></i><span x-text="ok ? 'Copiado' : 'Copiar'"></span></button>
                        </div>
                    </div>
                </div>

                <label class="settings-notif-row" style="margin:0">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="bi bi-lightning-charge"></i></div>
                        <div>
                            <p class="settings-notif-title">Gravar direto quando entender tudo</p>
                            <p class="settings-notif-desc">Desligado, tudo fica em Lançar por print para você conferir antes</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch {{ $auto_save ? 'active' : '' }}" wire:click="$toggle('auto_save')">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
            <button data-mobile-save type="submit" class="settings-save-btn"><i class="bi bi-check2"></i> Salvar</button>
            <button type="button" wire:click="testConnection" class="wa-test-btn" wire:loading.attr="disabled" wire:target="testConnection">
                <i class="bi bi-plug"></i><span wire:loading.remove wire:target="testConnection">Testar conexão</span><span wire:loading wire:target="testConnection">Testando...</span>
            </button>
            <x-action-message on="whatsapp-saved"><span style="display:inline-flex;align-items:center;gap:.35rem;color:#059669;font-size:.83rem;font-weight:600"><i class="bi bi-check-circle"></i> Salvo!</span></x-action-message>
        </div>
        @if($testResult)
            <div class="wa-test {{ $testOk ? 'ok' : 'bad' }}"><i class="bi {{ $testOk ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i> {{ $testResult }}</div>
        @endif
        </form>
        </div>

        <div class="s-col-side">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-chat-dots" style="font-size:1rem"></i></div>
                        <div>
                            <p class="settings-card-title">Pelo WhatsApp</p>
                            <p class="settings-card-desc">{{ $lastAt ? 'Última mensagem ' . $lastAt->diffForHumans() : 'Nenhuma mensagem ainda' }}</p>
                        </div>
                    </div>
                </div>
                <div style="padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:.6rem">
                    <div class="settings-info-tile" style="background:rgba(16,185,129,.08);border-color:transparent">
                        <p class="settings-info-tile-label">Lançados pelo WhatsApp</p>
                        <p class="settings-info-tile-val" style="color:#10b981">{{ $count }}</p>
                    </div>
                    <a href="{{ route('finance.print-capture') }}" wire:navigate class="wa-test-btn" style="justify-content:center"><i class="bi bi-phone"></i> Abrir Lançar por print</a>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(245,158,11,.1);color:#f59e0b"><i class="bi bi-signpost-split" style="font-size:1rem"></i></div>
                        <div>
                            <p class="settings-card-title">Como ligar</p>
                            <p class="settings-card-desc">Uma vez só, uns 20 minutos</p>
                        </div>
                    </div>
                </div>
                <ol class="wa-steps">
                    <li>Entre em <b>developers.facebook.com</b>, <b>Meus apps › Criar app</b>, tipo <b>Empresa</b>, e adicione o produto <b>WhatsApp</b>.</li>
                    <li>Em <b>WhatsApp › Configuração da API</b> a Meta dá um número de teste grátis. Em "Para", cadastre o seu celular.</li>
                    <li>Copie o <b>Identificador do número de telefone</b> para cá.</li>
                    <li>Gere um <b>token permanente</b>: Configurações do negócio › Usuários do sistema › Adicionar (admin) › Gerar token, com <b>whatsapp_business_messaging</b> e <b>whatsapp_business_management</b>. O token da tela da API vence em 24 horas.</li>
                    <li>Em <b>Configurações do app › Básico</b>, copie a <b>Chave secreta do app</b>.</li>
                    <li>Salve aqui. Na Meta, em <b>WhatsApp › Configuração › Webhook</b>, cole a URL de callback e o token de verificação, e assine o campo <b>messages</b>.</li>
                    <li>Salve o número do app nos contatos e mande <b>ajuda</b>.</li>
                </ol>
                <p class="wa-steps-note"><i class="bi bi-shield-lock"></i> O token e a chave ficam criptografados e não aparecem de novo. Não mande essas chaves por mensagem para ninguém.</p>
            </div>
        </div>
        </div>
    </x-settings.layout>

    <style>
        .settings-whatsapp-page .wa-err{color:#ef4444;font-size:.73rem;margin-top:.2rem}
        .settings-whatsapp-page .wa-hint{color:#64748b;font-size:.72rem;margin-top:.3rem}
        .settings-whatsapp-page .wa-saved{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;font-size:.78rem;color:#059669;margin:.1rem 0 .35rem}
        .settings-whatsapp-page .wa-link-danger{margin-left:auto;background:none;border:0;color:#ef4444;font-weight:600;font-size:.78rem;cursor:pointer;text-decoration:underline}
        .settings-whatsapp-page .wa-copy{display:flex;align-items:center;gap:.5rem;border:1.5px dashed rgba(var(--s-accent-rgb),.3);border-radius:.7rem;padding:.55rem .6rem .55rem .8rem;background:rgba(var(--s-accent-rgb),.04)}
        .settings-whatsapp-page .wa-copy code{flex:1;min-width:0;font-size:.76rem;word-break:break-all;color:inherit}
        .settings-whatsapp-page .wa-copy button{display:inline-flex;align-items:center;gap:.3rem;flex-shrink:0;border:0;border-radius:.55rem;padding:.4rem .7rem;background:var(--s-accent);color:#fff;font-size:.76rem;font-weight:700;cursor:pointer}
        .settings-whatsapp-page .wa-test-btn{display:inline-flex;align-items:center;gap:.4rem;border:1.5px solid rgba(var(--s-accent-rgb),.35);color:var(--s-accent);background:transparent;border-radius:.75rem;padding:.6rem 1rem;font-size:.85rem;font-weight:700;cursor:pointer}
        .settings-whatsapp-page .wa-test{border-radius:.75rem;padding:.7rem .9rem;font-size:.83rem;font-weight:600;display:flex;gap:.5rem;align-items:flex-start}
        .settings-whatsapp-page .wa-test.ok{background:rgba(16,185,129,.1);color:#047857}
        .settings-whatsapp-page .wa-test.bad{background:rgba(239,68,68,.1);color:#b91c1c}
        .dark .settings-whatsapp-page .wa-test.ok{color:#6ee7b7}
        .dark .settings-whatsapp-page .wa-test.bad{color:#fca5a5}
        .settings-whatsapp-page .wa-steps{margin:0;padding:0 1.25rem 0 2.6rem;display:flex;flex-direction:column;gap:.55rem;font-size:.82rem;line-height:1.45;list-style:decimal}
        .settings-whatsapp-page .wa-steps-note{margin:.9rem 1.25rem 1.25rem;font-size:.76rem;color:#64748b;display:flex;gap:.4rem}
        .dark .settings-whatsapp-page .wa-steps-note{color:#94a3b8}
    </style>
</section>
