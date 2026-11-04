<?php

use App\Models\InstagramPost;
use App\Models\InstagramSetting;
use App\Services\Instagram\CaptionBuilder;
use App\Services\Instagram\InstagramClient;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $app_id = '';
    // Segredo: nunca volta para a tela; vazio = manter o salvo
    public string $app_secret = '';
    public bool $hasSecret = false;

    public string $caption_product = '';
    public string $caption_promo = '';
    public string $hashtags = '';

    public ?string $testResult = null;
    public bool $testOk = false;

    public function mount(): void
    {
        $s = InstagramSetting::forUser(Auth::id());
        $this->app_id = (string) $s->app_id;
        $this->hasSecret = filled($s->app_secret);
        $this->caption_product = $s->captionProduct();
        $this->caption_promo = $s->captionPromo();
        $this->hashtags = (string) $s->hashtags;
    }

    public function saveApp(): void
    {
        $this->app_id = preg_replace('/\D/', '', $this->app_id);
        $this->app_secret = trim($this->app_secret);
        $this->validate([
            'app_id' => ['required', 'digits_between:8,25'],
            'app_secret' => ['nullable', 'string', 'regex:/^[a-f0-9]{32}$/i'],
        ], [
            'app_id.required' => 'Cole o ID do app do Instagram.',
            'app_id.digits_between' => 'O ID do app do Instagram tem só números (uns 15 ou 16).',
            'app_secret.regex' => 'A chave secreta do app do Instagram tem 32 letras e números.',
        ]);
        $s = InstagramSetting::forUser(Auth::id());
        if ($this->app_secret === '' && blank($s->app_secret)) {
            $this->addError('app_secret', 'Cole a chave secreta do app do Instagram.');

            return;
        }
        $changed = $s->app_id !== $this->app_id || $this->app_secret !== '';
        $s->app_id = $this->app_id;
        if ($this->app_secret !== '') {
            $s->app_secret = $this->app_secret;
        }
        $s->save();
        $this->app_secret = '';
        $this->hasSecret = true;
        $this->dispatch('ig-app-saved');
        if ($changed && $s->connected()) {
            $this->testResult = null;
        }
    }

    public function saveCaptions(): void
    {
        $this->validate([
            'caption_product' => ['required', 'string', 'max:2000'],
            'caption_promo' => ['required', 'string', 'max:2000'],
            'hashtags' => ['nullable', 'string', 'max:1000'],
        ], ['caption_product.required' => 'Escreva o modelo da legenda.', 'caption_promo.required' => 'Escreva o modelo da legenda.']);
        InstagramSetting::forUser(Auth::id())->update([
            'caption_product' => trim($this->caption_product),
            'caption_promo' => trim($this->caption_promo),
            'hashtags' => CaptionBuilder::hashtags($this->hashtags) ?: null,
        ]);
        $this->hashtags = CaptionBuilder::hashtags($this->hashtags);
        $this->dispatch('ig-captions-saved');
    }

    public function resetCaptions(): void
    {
        $this->caption_product = InstagramSetting::DEFAULT_PRODUCT;
        $this->caption_promo = InstagramSetting::DEFAULT_PROMO;
    }

    public function disconnect(): void
    {
        InstagramSetting::forUser(Auth::id())->update([
            'access_token' => null, 'token_expires_at' => null, 'ig_user_id' => null, 'username' => null,
            'name' => null, 'picture_url' => null, 'account_type' => null, 'connected_at' => null,
        ]);
        InstagramPost::where('user_id', Auth::id())->where('status', InstagramPost::AGENDADO)->update(['status' => InstagramPost::CANCELADO, 'error' => 'Instagram desconectado']);
        $this->testResult = null;
    }

    public function testConnection(): void
    {
        $s = InstagramSetting::forUser(Auth::id());
        if (! $s->connected()) {
            $this->testOk = false;
            $this->testResult = 'Conecte a conta antes de testar.';

            return;
        }
        try {
            $client = new InstagramClient($s);
            $me = $client->loadProfile();
            $used = $client->usage();
            $this->testOk = true;
            $this->testResult = 'Tudo certo com @' . ($me['username'] ?? $s->username) . '.'
                . ($used !== null ? " Posts pelo app nas últimas 24 horas: {$used} de 100." : '');
            $s->update(['last_error' => null]);
        } catch (\Throwable $e) {
            $this->testOk = false;
            $this->testResult = $e->getMessage();
        }
    }

    public function with(): array
    {
        $s = InstagramSetting::forUser(Auth::id());
        $posts = InstagramPost::where('user_id', Auth::id());

        return [
            's' => $s,
            'connected' => $s->connected(),
            'callbackUrl' => route('instagram.callback'),
            'siteUrl' => rtrim(config('app.url') ?: url('/'), '/'),
            'postedCount' => (clone $posts)->where('status', InstagramPost::POSTADO)->count(),
            'scheduledCount' => (clone $posts)->where('status', InstagramPost::AGENDADO)->count(),
            'tags' => CaptionBuilder::TAGS,
        ];
    }
}; ?>

@php
    $igSteps = [
        ['App da Meta', filled($app_id) && $hasSecret, 'bi-box'],
        ['Conta conectada', $connected, 'bi-instagram'],
        ['Primeiro post', $postedCount > 0, 'bi-send'],
    ];
    $daysLeft = $s->token_expires_at ? (int) now()->diffInDays($s->token_expires_at, false) : null;
@endphp

<section class="settings-instagram-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    <div class="ig-page">
        {{-- Topo: situação e atalhos --}}
        <div class="ig-hero">
            <span class="ig-glow"></span>
            <div class="ig-hero-ico">
                @if($connected && $s->picture_url)
                    <img src="{{ $s->picture_url }}" alt="" referrerpolicy="no-referrer">
                @else
                    <i class="bi bi-instagram"></i>
                @endif
            </div>
            <div class="ig-hero-txt">
                <span class="ig-kicker"><i class="bi bi-send"></i> Postar pelo app</span>
                <h2 data-testid="ig-state">{{ $connected ? 'Conectado como @' . $s->username : ($s->access_token ? 'O acesso venceu, conecte de novo' : 'Instagram não conectado') }}</h2>
                <p>Poste fotos, carrosséis e stories dos seus produtos e promoções direto do app, na hora ou agendado.</p>
                <div class="ig-progress">
                    @foreach($igSteps as [$label, $ok, $icon])
                        <span class="{{ $ok ? 'is-ok' : '' }}"><i class="bi {{ $ok ? 'bi-check-circle-fill' : $icon }}"></i> {{ $label }}</span>
                    @endforeach
                </div>
            </div>
            <div class="ig-hero-stats">
                <div><b>{{ $postedCount }}</b><small>posts feitos pelo app</small></div>
                <div><b>{{ $scheduledCount }}</b><small>{{ $scheduledCount === 1 ? 'agendado' : 'agendados' }}</small></div>
                <a href="{{ route('instagram.create') }}" wire:navigate class="ig-hero-btn"><i class="bi bi-plus-lg"></i> Novo post</a>
            </div>
        </div>

        @if(session('ig_ok'))
            <div class="ig-test ok"><i class="bi bi-check-circle-fill"></i> {{ session('ig_ok') }}</div>
        @endif
        @if(session('ig_error'))
            <div class="ig-test bad" data-testid="ig-flash-error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('ig_error') }}</div>
        @endif

        <div class="ig-grid">
        <div class="ig-main">

            {{-- 1. App da Meta --}}
            <form wire:submit="saveApp" class="ap-card">
                <div class="ap-card-head">
                    <span class="ig-num">1</span>
                    <div class="min-w-0">
                        <h2>App da Meta</h2>
                        <p>Copiados de developers.facebook.com › seu app › Instagram › Configuração da API com login do Instagram. A chave fica guardada criptografada.</p>
                    </div>
                </div>
                <div class="ig-fields">
                    <div class="ig-field">
                        <label class="settings-field-label">ID do app do Instagram</label>
                        <flux:input wire:model="app_id" type="text" inputmode="numeric" autocomplete="off" placeholder="Ex.: 1234567890123456"/>
                        @error('app_id') <p class="ig-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="ig-field">
                        <label class="settings-field-label">Chave secreta do app do Instagram</label>
                        @if($hasSecret)<div class="ig-saved"><i class="bi bi-lock-fill"></i> Salva</div>@endif
                        <flux:input wire:model="app_secret" type="password" autocomplete="off" placeholder="{{ $hasSecret ? 'Cole uma nova só se quiser trocar' : '32 letras e números' }}"/>
                        @error('app_secret') <p class="ig-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="ig-field ig-field-full">
                        <label class="settings-field-label">Endereço de retorno (cole na Meta em "URL de redirecionamento do OAuth")</label>
                        <div class="ig-copy" x-data="{ok:false}">
                            <code>{{ $callbackUrl }}</code>
                            <button type="button" @click="navigator.clipboard.writeText(@js($callbackUrl)); ok=true; setTimeout(()=>ok=false,1500)"><i class="bi" :class="ok ? 'bi-check2' : 'bi-copy'"></i><span x-text="ok ? 'Copiado' : 'Copiar'"></span></button>
                        </div>
                    </div>
                </div>
                <div class="ig-actions mt-3">
                    <button type="submit" class="ig-btn-save" data-testid="ig-save-app"><i class="bi bi-check2"></i> Salvar app</button>
                    <x-action-message on="ig-app-saved"><span class="ig-ok-msg"><i class="bi bi-check-circle"></i> Salvo!</span></x-action-message>
                </div>
            </form>

            {{-- 2. Conta --}}
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="ig-num">2</span>
                    <div class="min-w-0">
                        <h2>Conta do Instagram</h2>
                        <p>O login é feito na tela do próprio Instagram. A conta precisa ser profissional (Empresa ou Criador de conteúdo).</p>
                    </div>
                </div>
                @if($connected)
                    <div class="ig-account">
                        <span class="ig-avatar">
                            @if($s->picture_url)<img src="{{ $s->picture_url }}" alt="" referrerpolicy="no-referrer">@else<i class="bi bi-person"></i>@endif
                        </span>
                        <span class="ig-account-txt">
                            <b>{{ '@' . $s->username }}</b>
                            <small>{{ $s->name }}{{ $s->account_type ? ' · ' . ($s->account_type === 'MEDIA_CREATOR' ? 'Criador' : 'Empresa') : '' }}</small>
                            <small class="{{ $daysLeft !== null && $daysLeft < 7 ? 'text-amber-600' : '' }}"><i class="bi bi-arrow-repeat"></i> Acesso renova sozinho{{ $daysLeft !== null ? ' · vale mais ' . max(0, $daysLeft) . ' dias' : '' }}</small>
                        </span>
                        <a href="https://instagram.com/{{ $s->username }}" target="_blank" rel="noopener" class="ig-btn-ghost"><i class="bi bi-box-arrow-up-right"></i><span>Ver perfil</span></a>
                    </div>
                    <div class="ig-actions mt-3">
                        <button type="button" wire:click="testConnection" class="ig-btn-test" wire:loading.attr="disabled" wire:target="testConnection">
                            <i class="bi bi-plug"></i><span wire:loading.remove wire:target="testConnection">Testar conexão</span><span wire:loading wire:target="testConnection">Testando...</span>
                        </button>
                        <a href="{{ route('instagram.connect') }}" class="ig-btn-test"><i class="bi bi-arrow-repeat"></i> Conectar outra conta</a>
                        <button type="button" wire:click="disconnect" wire:confirm="Desconectar o Instagram? Os posts agendados são cancelados." class="ig-link-danger">Desconectar</button>
                    </div>
                @else
                    <a href="{{ route('instagram.connect') }}" class="ig-connect {{ filled($app_id) && $hasSecret ? '' : 'is-off' }}" data-testid="ig-connect">
                        <i class="bi bi-instagram"></i>
                        <span><b>Conectar Instagram</b><small>{{ filled($app_id) && $hasSecret ? 'Abre o Instagram para você entrar e permitir' : 'Salve o app da Meta no passo 1 primeiro' }}</small></span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                @endif
                @if($s->last_error && ! session('ig_error'))
                    <p class="ig-err mt-2"><i class="bi bi-exclamation-triangle"></i> Último erro: {{ $s->last_error }}</p>
                @endif
                @if($testResult)
                    <div class="ig-test mt-3 {{ $testOk ? 'ok' : 'bad' }}"><i class="bi {{ $testOk ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i> {{ $testResult }}</div>
                @endif
            </div>

            {{-- 3. Legendas --}}
            <form wire:submit="saveCaptions" class="ap-card" x-data="{ last: 'caption_product', put(t) { const el = this.$refs[this.last]; const s = el.selectionStart ?? el.value.length; el.setRangeText(t, s, el.selectionEnd ?? s, 'end'); el.dispatchEvent(new Event('input')); el.focus(); } }">
                <div class="ap-card-head">
                    <span class="ig-num">3</span>
                    <div class="min-w-0">
                        <h2>Legenda pronta e hashtags</h2>
                        <p>O app já preenche a legenda com isso. Você ainda pode mudar em cada post.</p>
                    </div>
                </div>
                <div class="ig-tags">
                    @foreach($tags as $tag => $label)
                        <button type="button" @click="put(@js($tag))" title="{{ $label }}">{{ $tag }}</button>
                    @endforeach
                </div>
                <div class="ig-fields">
                    <div class="ig-field">
                        <label class="settings-field-label">Post de produto</label>
                        <textarea wire:model="caption_product" x-ref="caption_product" @focus="last = 'caption_product'" rows="8" class="ig-textarea"></textarea>
                        @error('caption_product') <p class="ig-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="ig-field">
                        <label class="settings-field-label">Post de promoção</label>
                        <textarea wire:model="caption_promo" x-ref="caption_promo" @focus="last = 'caption_promo'" rows="8" class="ig-textarea"></textarea>
                        @error('caption_promo') <p class="ig-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="ig-field ig-field-full">
                        <label class="settings-field-label">Suas hashtags (até 30)</label>
                        <flux:input wire:model="hashtags" type="text" placeholder="#moveis #decoracao #ferroemadeira"/>
                        <p class="ig-hint">Separe com espaço. Entram onde estiver {hashtags}.</p>
                    </div>
                </div>
                <div class="ig-actions mt-3">
                    <button type="submit" class="ig-btn-save"><i class="bi bi-check2"></i> Salvar legendas</button>
                    <button type="button" wire:click="resetCaptions" class="ig-btn-test"><i class="bi bi-arrow-counterclockwise"></i> Voltar ao modelo do app</button>
                    <x-action-message on="ig-captions-saved"><span class="ig-ok-msg"><i class="bi bi-check-circle"></i> Salvo!</span></x-action-message>
                </div>
            </form>
        </div>

        <aside class="ig-side">
            <div class="ap-card">
                <div class="ap-card-head">
                    <span class="ap-ico ig-grad"><i class="bi bi-signpost-split"></i></span>
                    <div class="min-w-0">
                        <h2>Como ligar</h2>
                        <p>Uma vez só, uns 15 minutos.</p>
                    </div>
                </div>
                <ol class="ig-steps">
                    <li>No Instagram, em <b>Configurações › Tipo de conta</b>, mude para <b>conta profissional</b> (Empresa ou Criador).</li>
                    <li>Em <b>developers.facebook.com › Meus apps</b>, abra o app que você já usa no WhatsApp (ou crie um, tipo <b>Empresa</b>) e adicione o produto <b>Instagram</b>.</li>
                    <li>Em <b>Instagram › Configuração da API com login do Instagram</b>, no passo <b>Configurar login comercial do Instagram</b>, cole o <b>endereço de retorno</b> daqui.</li>
                    <li>Em <b>Funções do app › Funções</b>, adicione o seu Instagram como <b>Testador do Instagram</b> e aceite o convite no Instagram (Configurações › Site e apps).</li>
                    <li class="{{ filled($app_id) && $hasSecret ? 'is-done' : '' }}">Copie o <b>ID do app do Instagram</b> e a <b>chave secreta do app do Instagram</b> para o passo 1 e salve.</li>
                    <li class="{{ $connected ? 'is-done' : '' }}">Clique em <b>Conectar Instagram</b>, entre e permita.</li>
                    <li class="{{ $postedCount ? 'is-done' : '' }}">Abra um produto e clique em <b>Instagram</b> para o primeiro post.</li>
                </ol>
                <p class="ig-steps-note"><i class="bi bi-shield-lock"></i> Não mande a chave secreta por mensagem para ninguém.</p>
                <p class="ig-steps-note"><i class="bi bi-clock"></i> Para os agendados saírem na hora, a tarefa automática do servidor (cron) precisa estar ligada na Hostoo.</p>
            </div>
        </aside>
        </div>
    </div>

    <style>
        .ig-grad { background: linear-gradient(135deg, #f58529, #dd2a7b 50%, #8134af); color: #fff; }
        .ig-page { display: flex; flex-direction: column; gap: 1rem; }
        .ig-hero { position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem; padding: 1.4rem 1.6rem; border-radius: 1.6rem; color: #fff; background: linear-gradient(135deg, #feda75 -20%, #f58529 10%, #dd2a7b 50%, #8134af 85%, #515bd4 110%); box-shadow: 0 24px 50px -28px rgba(129, 52, 175, .8); }
        .ig-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 10% 0%, rgba(255, 255, 255, .3), transparent 40%); }
        .ig-hero-ico { position: relative; flex: none; display: grid; place-items: center; width: 4rem; height: 4rem; border-radius: 1.2rem; background: rgba(255, 255, 255, .18); border: 1px solid rgba(255, 255, 255, .3); font-size: 1.9rem; overflow: hidden; }
        .ig-hero-ico img { width: 100%; height: 100%; object-fit: cover; }
        .ig-hero-txt { position: relative; flex: 1; min-width: 0; }
        .ig-kicker { display: inline-flex; align-items: center; gap: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .92; }
        .ig-hero-txt h2 { font-size: 1.4rem; font-weight: 900; line-height: 1.2; margin-top: .2rem; overflow-wrap: anywhere; }
        .ig-hero-txt p { font-size: .8rem; opacity: .92; max-width: 40rem; }
        .ig-progress { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .6rem; }
        .ig-progress span { display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .6rem; border-radius: 999px; background: rgba(0, 0, 0, .15); border: 1px dashed rgba(255, 255, 255, .4); font-size: .72rem; font-weight: 700; opacity: .85; }
        .ig-progress span.is-ok { background: rgba(255, 255, 255, .95); color: #9d174d; border-style: solid; border-color: transparent; opacity: 1; }
        .ig-hero-stats { position: relative; flex: none; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; width: 19rem; }
        .ig-hero-stats > div { padding: .6rem .7rem; border-radius: .9rem; background: rgba(255, 255, 255, .15); border: 1px solid rgba(255, 255, 255, .25); }
        .ig-hero-stats b { display: block; font-size: 1.15rem; font-weight: 900; line-height: 1.15; }
        .ig-hero-stats small { display: block; font-size: .66rem; opacity: .9; line-height: 1.25; }
        .ig-hero-btn { grid-column: 1 / -1; display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .6rem; border-radius: .9rem; background: #fff; color: #be185d; font-size: .82rem; font-weight: 800; }
        .ig-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1rem; align-items: start; }
        .ig-main { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .ig-side { position: sticky; top: 1rem; min-width: 0; }
        .ig-num { flex: none; display: grid; place-items: center; width: 2.3rem; height: 2.3rem; border-radius: .8rem; background: linear-gradient(135deg, #f58529, #dd2a7b 55%, #8134af); color: #fff; font-weight: 900; box-shadow: 0 8px 18px -8px rgba(221, 42, 123, .6); }
        .ig-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .9rem; }
        .ig-field { min-width: 0; }
        .ig-field-full { grid-column: 1 / -1; }
        .ig-err { color: #ef4444; font-size: .73rem; margin-top: .2rem; }
        .ig-hint { color: #64748b; font-size: .72rem; margin-top: .3rem; }
        .ig-saved { display: flex; align-items: center; gap: .4rem; font-size: .78rem; color: #059669; margin: .1rem 0 .35rem; }
        .ig-link-danger { margin-left: auto; background: none; border: 0; color: #ef4444; font-weight: 600; font-size: .8rem; cursor: pointer; text-decoration: underline; }
        .ig-copy { display: flex; align-items: center; gap: .5rem; border: 1.5px dashed #f9a8d4; border-radius: .8rem; padding: .55rem .6rem .55rem .8rem; background: #fdf2f8; }
        .dark .ig-copy { background: rgba(236, 72, 153, .06); border-color: rgba(236, 72, 153, .3); }
        .ig-copy code { flex: 1; min-width: 0; font-size: .76rem; word-break: break-all; color: inherit; }
        .ig-copy button { display: inline-flex; align-items: center; gap: .3rem; flex-shrink: 0; border: 0; border-radius: .6rem; padding: .4rem .7rem; background: #db2777; color: #fff; font-size: .76rem; font-weight: 700; cursor: pointer; }
        .ig-actions { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
        .ig-btn-save { display: inline-flex; align-items: center; gap: .4rem; padding: .7rem 1.4rem; border-radius: .9rem; background: linear-gradient(135deg, #f58529, #dd2a7b 55%, #8134af); color: #fff; font-size: .88rem; font-weight: 800; box-shadow: 0 12px 24px -14px #8134af; }
        .ig-btn-test, .ig-btn-ghost { display: inline-flex; align-items: center; gap: .4rem; padding: .66rem 1.1rem; border-radius: .9rem; border: 1.5px solid #fbcfe8; color: #be185d; background: #fff; font-size: .85rem; font-weight: 800; }
        .dark .ig-btn-test, .dark .ig-btn-ghost { background: #111827; color: #f9a8d4; border-color: rgba(236, 72, 153, .35); }
        .ig-ok-msg { display: inline-flex; align-items: center; gap: .35rem; color: #059669; font-size: .83rem; font-weight: 700; }
        .ig-test { border-radius: .9rem; padding: .7rem .9rem; font-size: .83rem; font-weight: 600; display: flex; gap: .5rem; align-items: flex-start; }
        .ig-test.ok { background: rgba(16, 185, 129, .1); color: #047857; }
        .ig-test.bad { background: rgba(239, 68, 68, .1); color: #b91c1c; }
        .dark .ig-test.ok { color: #6ee7b7; }
        .dark .ig-test.bad { color: #fca5a5; }
        .ig-connect { display: flex; align-items: center; gap: .9rem; padding: 1rem 1.1rem; border-radius: 1.1rem; color: #fff; background: linear-gradient(135deg, #f58529, #dd2a7b 55%, #8134af); box-shadow: 0 16px 30px -18px #8134af; }
        .ig-connect > i:first-child { font-size: 1.8rem; }
        .ig-connect span { flex: 1; min-width: 0; }
        .ig-connect b { display: block; font-size: 1rem; }
        .ig-connect small { display: block; font-size: .75rem; opacity: .9; }
        .ig-connect.is-off { filter: grayscale(.7); opacity: .7; pointer-events: none; }
        .ig-account { display: flex; align-items: center; gap: .85rem; padding: .8rem .9rem; border-radius: 1rem; border: 1px solid #fbcfe8; background: #fff; }
        .dark .ig-account { background: #111827; border-color: rgba(236, 72, 153, .3); }
        .ig-avatar { flex: none; display: grid; place-items: center; width: 3.2rem; height: 3.2rem; border-radius: 999px; overflow: hidden; padding: 2px; background: linear-gradient(135deg, #feda75, #f58529, #dd2a7b, #8134af); }
        .ig-avatar img, .ig-avatar i { width: 100%; height: 100%; border-radius: 999px; object-fit: cover; background: #fff; display: grid; place-items: center; color: #94a3b8; }
        .ig-account-txt { flex: 1; min-width: 0; }
        .ig-account-txt b { display: block; font-size: .95rem; color: #0f172a; overflow-wrap: anywhere; }
        .dark .ig-account-txt b { color: #f1f5f9; }
        .ig-account-txt small { display: block; font-size: .74rem; color: #64748b; }
        .ig-tags { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .75rem; }
        .ig-tags button { padding: .22rem .55rem; border-radius: 999px; border: 1px solid #fbcfe8; background: #fdf2f8; color: #be185d; font-size: .72rem; font-weight: 700; font-family: ui-monospace, monospace; }
        .dark .ig-tags button { background: rgba(236, 72, 153, .1); border-color: rgba(236, 72, 153, .3); color: #f9a8d4; }
        .ig-textarea { width: 100%; border-radius: .8rem; border: 1px solid #e2e8f0; background: #fff; padding: .6rem .75rem; font-size: .85rem; line-height: 1.45; color: #0f172a; resize: vertical; }
        .ig-textarea:focus { outline: none; border-color: #f472b6; box-shadow: 0 0 0 3px rgba(244, 114, 182, .2); }
        .dark .ig-textarea { background: #0f172a; border-color: #334155; color: #f1f5f9; }
        .ig-steps { counter-reset: ig; display: flex; flex-direction: column; gap: .6rem; font-size: .8rem; line-height: 1.45; color: #334155; }
        .ig-steps li { counter-increment: ig; position: relative; padding-left: 2.1rem; }
        .ig-steps li::before { content: counter(ig); position: absolute; left: 0; top: 0; display: grid; place-items: center; width: 1.5rem; height: 1.5rem; border-radius: 999px; background: #f1f5f9; color: #475569; font-size: .72rem; font-weight: 900; }
        .ig-steps li.is-done::before { content: '✓'; background: #db2777; color: #fff; }
        .ig-steps li.is-done { color: #94a3b8; }
        .dark .ig-steps { color: #cbd5e1; }
        .dark .ig-steps li::before { background: #1e293b; color: #cbd5e1; }
        .ig-steps-note { margin-top: .9rem; font-size: .74rem; color: #64748b; display: flex; gap: .4rem; }
        @media (max-width: 1535px) { .ig-hero { flex-wrap: wrap; } .ig-hero-stats { width: 100%; grid-template-columns: repeat(3, minmax(0, 1fr)); } .ig-hero-btn { grid-column: auto; } }
        @media (max-width: 1279px) { .ig-grid { grid-template-columns: minmax(0, 1fr) 290px; } .ig-fields { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 1023px) { .ig-grid { grid-template-columns: minmax(0, 1fr); } .ig-side { position: static; } .ig-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .ig-hero { padding: 1.1rem; border-radius: 1.3rem; gap: .9rem; }
            .ig-hero-ico { width: 3.2rem; height: 3.2rem; font-size: 1.45rem; }
            .ig-hero-txt { flex-basis: calc(100% - 4.5rem); }
            .ig-hero-txt h2 { font-size: 1.1rem; }
            .ig-hero-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ig-hero-btn { grid-column: 1 / -1; }
            .ig-fields { grid-template-columns: minmax(0, 1fr); }
            .ig-actions > button, .ig-actions > a { flex: 1 1 100%; justify-content: center; }
            .ig-link-danger { margin: .2rem auto 0; }
            .ig-account { flex-wrap: wrap; }
            .ig-account .ig-btn-ghost { width: 100%; justify-content: center; }
        }
    </style>
    </x-settings.layout>
</section>
