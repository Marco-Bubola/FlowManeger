<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $location = '';
    public string $about_me = '';
    public $profile_picture = null;
    public string $google_id = '';
    public string $website = '';
    public string $twitter = '';
    public string $instagram = '';
    public string $linkedin = '';
    public string $birth_date = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->location = $user->location ?? '';
        $this->about_me = $user->about_me ?? '';
        $this->google_id = $user->google_id ?? '';
        $this->website = $user->website ?? '';
        $this->twitter = $user->twitter ?? '';
        $this->instagram = $user->instagram ?? '';
        $this->linkedin = $user->linkedin ?? '';
        $this->birth_date = $user->birth_date ?? '';
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone'      => ['nullable', 'string', 'max:30'],
            'location'   => ['nullable', 'string', 'max:255'],
            'about_me'   => ['nullable', 'string', 'max:1000'],
            'google_id'  => ['nullable', 'string', 'max:255'],
            'website'    => ['nullable', 'url', 'max:255'],
            'twitter'    => ['nullable', 'string', 'max:100'],
            'instagram'  => ['nullable', 'string', 'max:100'],
            'linkedin'   => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'string', 'max:20'],
        ]);

        $user->fill($validated);

        if ($this->profile_picture && is_object($this->profile_picture)) {
            $path = $this->profile_picture->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard.index', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }
}; ?>

@php
    $au = Auth::user();
    $auVerified = $au->hasVerifiedEmail();
    $auGoogle = ! empty($au->google_id);
    $auPlan = null;
    try { $auPlan = $au->plan(); } catch (\Throwable $e) { $auPlan = null; }
    $auSince = $au->created_at ? ucfirst($au->created_at->locale('pt_BR')->translatedFormat('F \d\e Y')) : '—';
    $auDays = $au->created_at ? (int) $au->created_at->diffInDays(now()) : 0;
    $photoUrl = ($profile_picture && is_object($profile_picture) && method_exists($profile_picture, 'temporaryUrl'))
        ? rescue(fn () => $profile_picture->temporaryUrl(), null, false)
        : ($au->profile_picture ? asset('storage/' . $au->profile_picture) : null);

    // O que falta para o perfil ficar completo (cada item leva ao campo)
    $checks = [
        ['Foto de perfil', ! empty($au->profile_picture), 'acc-photo', 'bi-camera'],
        ['E-mail confirmado', $auVerified, 'acc-contato', 'bi-envelope-check'],
        ['Telefone', ! empty($au->phone), 'acc-contato', 'bi-telephone'],
        ['Cidade', ! empty($au->location), 'acc-contato', 'bi-geo-alt'],
        ['Sobre você', ! empty($au->about_me), 'acc-dados', 'bi-chat-quote'],
        ['Aniversário', ! empty($au->birth_date), 'acc-dados', 'bi-cake2'],
        ['Site ou rede social', ! empty($au->website) || ! empty($au->instagram) || ! empty($au->linkedin) || ! empty($au->twitter), 'acc-redes', 'bi-share'],
    ];
    $done = collect($checks)->where(1, true)->count();
    $pct = (int) round($done / count($checks) * 100);

    $shortcuts = [
        ['settings.password', 'Senha e login', 'Trocar senha e entrar com Google', 'bi-key', 'from-amber-500 to-orange-500'],
        ['settings.security', 'Segurança', 'Verificação em duas etapas', 'bi-shield-lock', 'from-emerald-500 to-teal-500'],
        ['settings.devices', 'Dispositivos', 'Onde sua conta está aberta', 'bi-laptop', 'from-sky-500 to-blue-600'],
        ['settings.team', 'Equipe', 'Quem mais usa a loja', 'bi-people', 'from-indigo-500 to-purple-500'],
        ['settings.plan', 'Meu plano', $auPlan?->name ? 'Plano ' . $auPlan->name : 'Uso e assinatura', 'bi-credit-card', 'from-purple-500 to-pink-500'],
        ['settings.appearance', 'Aparência do app', 'Cores, fonte e modo escuro', 'bi-palette2', 'from-pink-500 to-rose-500'],
    ];
@endphp

<section class="settings-profile-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

    <div class="acc-page">

        {{-- Capa com foto, nome e progresso --}}
        <div class="acc-hero" id="acc-photo">
            <div class="acc-cover bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500">
                <span class="acc-cover-glow"></span>
            </div>
            <div class="acc-hero-body">
                <label class="acc-avatar" title="Trocar foto">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Foto de perfil">
                    @else
                        <span class="bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">{{ $au->initials() }}</span>
                    @endif
                    <i class="bi bi-camera-fill"></i>
                    <input type="file" wire:model="profile_picture" data-avatar-input accept="image/*" class="hidden">
                    <em wire:loading wire:target="profile_picture"><i class="bi bi-arrow-repeat animate-spin"></i></em>
                </label>
                <div class="acc-id">
                    <h2>{{ $au->name }}
                        @if($auVerified)<i class="bi bi-patch-check-fill text-emerald-500" title="E-mail confirmado"></i>@endif
                    </h2>
                    <p>{{ $au->email }}</p>
                    <div class="acc-chips">
                        <span class="bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="bi bi-patch-check"></i> Plano {{ $auPlan?->name ?? 'Grátis' }}</span>
                        <span class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><i class="bi bi-calendar-heart"></i> Desde {{ $auSince }}</span>
                        @if($auGoogle)
                            <span class="bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300"><i class="bi bi-google"></i> Google vinculado</span>
                        @endif
                        @if($au->location)
                            <span class="bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><i class="bi bi-geo-alt"></i> {{ $au->location }}</span>
                        @endif
                    </div>
                    @error('profile_picture') <p class="acc-err">{{ $message }}</p> @enderror
                    @if($profile_picture && is_object($profile_picture))
                        <p class="acc-hint text-amber-600 dark:text-amber-400"><i class="bi bi-info-circle"></i> Foto nova escolhida. Toque em Salvar para guardar.</p>
                    @else
                        <p class="acc-hint">Toque na foto para trocar. JPG, PNG ou WebP até 2 MB.</p>
                    @endif
                </div>
                <div class="acc-progress" style="--pct: {{ $pct }}">
                    <div class="acc-ring"><b>{{ $pct }}%</b></div>
                    <div>
                        <p>{{ $pct === 100 ? 'Perfil completo' : 'Complete seu perfil' }}</p>
                        <small>{{ $done }} de {{ count($checks) }} itens</small>
                    </div>
                </div>
            </div>
            <div class="acc-checks">
                @foreach($checks as [$lbl, $ok, $anchor, $icon])
                    <a href="#{{ $anchor }}" class="acc-check {{ $ok ? 'is-ok' : '' }}">
                        <i class="bi {{ $ok ? 'bi-check-circle-fill' : $icon }}"></i> {{ $lbl }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="acc-grid">
            <form wire:submit="updateProfileInformation" class="acc-main">
                {{-- Dados pessoais --}}
                <div class="ap-card" id="acc-dados">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-indigo-500 to-purple-500"><i class="bi bi-person-vcard"></i></span>
                        <div class="min-w-0">
                            <h2>Dados pessoais</h2>
                            <p>Como você aparece no app, nos recibos e para a sua equipe.</p>
                        </div>
                    </div>
                    <div class="acc-fields">
                        <div class="acc-field acc-span2">
                            <label for="acc-name">Nome completo <b>*</b></label>
                            <div class="acc-input"><i class="bi bi-person"></i><input id="acc-name" wire:model="name" type="text" required autocomplete="name" placeholder="Seu nome completo"></div>
                            @error('name') <p class="acc-err">{{ $message }}</p> @enderror
                        </div>
                        <div class="acc-field">
                            <label for="acc-birth">Data de nascimento</label>
                            <div class="acc-input"><i class="bi bi-cake2"></i><input id="acc-birth" wire:model="birth_date" type="date"></div>
                            @error('birth_date') <p class="acc-err">{{ $message }}</p> @enderror
                        </div>
                        <div class="acc-field acc-span3">
                            <label for="acc-about">Sobre você</label>
                            <div class="acc-input acc-textarea"><textarea id="acc-about" wire:model="about_me" rows="3" maxlength="1000" placeholder="Conte um pouco sobre você ou sobre a sua loja"></textarea></div>
                            @error('about_me') <p class="acc-err">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Contato --}}
                <div class="ap-card" id="acc-contato">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-sky-500 to-blue-600"><i class="bi bi-envelope-paper"></i></span>
                        <div class="min-w-0">
                            <h2>Contato</h2>
                            <p>Usado para entrar no app, recuperar a senha e receber avisos.</p>
                        </div>
                    </div>
                    <div class="acc-fields">
                        <div class="acc-field acc-span2">
                            <label for="acc-email">E-mail <b>*</b></label>
                            <div class="acc-input"><i class="bi bi-envelope"></i><input id="acc-email" wire:model="email" type="email" required autocomplete="email" placeholder="seu@email.com">
                                @if($auVerified)
                                    <span class="acc-tag text-emerald-700 bg-emerald-50 dark:bg-emerald-500/15 dark:text-emerald-300"><i class="bi bi-check2"></i> Confirmado</span>
                                @else
                                    <span class="acc-tag text-amber-700 bg-amber-50 dark:bg-amber-500/15 dark:text-amber-300">Não confirmado</span>
                                @endif
                            </div>
                            @error('email') <p class="acc-err">{{ $message }}</p> @enderror
                        </div>
                        <div class="acc-field">
                            <label for="phoneInput">Telefone / WhatsApp</label>
                            <div class="acc-input"><i class="bi bi-whatsapp"></i><input wire:model="phone" type="text" autocomplete="tel" id="phoneInput" inputmode="tel" placeholder="+55 (11) 99999-9999"></div>
                            @error('phone') <p class="acc-err">{{ $message }}</p> @enderror
                        </div>
                        <div class="acc-field acc-span3">
                            <label for="acc-loc">Cidade</label>
                            <div class="acc-input"><i class="bi bi-geo-alt"></i><input id="acc-loc" wire:model="location" type="text" placeholder="Cidade, Estado"></div>
                            @error('location') <p class="acc-err">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $auVerified)
                        <div class="acc-alert">
                            <i class="bi bi-envelope-exclamation"></i>
                            <div>
                                <b>Confirme seu e-mail</b>
                                <span>Enviamos um link para {{ $au->email }}. Não chegou?</span>
                                @if (session('status') === 'verification-link-sent')
                                    <span class="text-emerald-700 dark:text-emerald-300 font-semibold">Link enviado! Olhe sua caixa de entrada.</span>
                                @endif
                            </div>
                            <button type="button" wire:click.prevent="resendVerificationNotification">Reenviar link</button>
                        </div>
                    @endif
                </div>

                {{-- Redes --}}
                <div class="ap-card" id="acc-redes">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-pink-500 to-orange-400"><i class="bi bi-share"></i></span>
                        <div class="min-w-0">
                            <h2>Site e redes sociais</h2>
                            <p>Onde seus clientes encontram você. Tudo opcional.</p>
                        </div>
                    </div>
                    <div class="acc-fields acc-fields-2">
                        <div class="acc-field">
                            <label for="acc-site">Site</label>
                            <div class="acc-input"><i class="bi bi-globe2"></i><input id="acc-site" wire:model="website" type="url" placeholder="https://seusite.com.br"></div>
                            @error('website') <p class="acc-err">{{ $message }}</p> @enderror
                        </div>
                        <div class="acc-field">
                            <label for="acc-ig">Instagram</label>
                            <div class="acc-input"><i class="bi bi-instagram"></i><input id="acc-ig" wire:model="instagram" type="text" placeholder="@sualoja"></div>
                            @error('instagram') <p class="acc-err">{{ $message }}</p> @enderror
                        </div>
                        <div class="acc-field">
                            <label for="acc-in">LinkedIn</label>
                            <div class="acc-input"><i class="bi bi-linkedin"></i><input id="acc-in" wire:model="linkedin" type="text" placeholder="seu-perfil"></div>
                            @error('linkedin') <p class="acc-err">{{ $message }}</p> @enderror
                        </div>
                        <div class="acc-field">
                            <label for="acc-x">X (Twitter)</label>
                            <div class="acc-input"><i class="bi bi-twitter-x"></i><input id="acc-x" wire:model="twitter" type="text" placeholder="@seuusuario"></div>
                            @error('twitter') <p class="acc-err">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Barra de salvar: fica presa embaixo e acende quando algo mudou --}}
                <div class="acc-savebar" wire:dirty.class="is-dirty">
                    <span class="acc-savebar-msg">
                        <span wire:dirty.remove><i class="bi bi-cloud-check"></i> Tudo salvo</span>
                        <span wire:dirty><i class="bi bi-pencil-square"></i> Você tem alterações não salvas</span>
                    </span>
                    <x-action-message on="profile-updated" class="acc-saved-msg"><i class="bi bi-check-circle-fill"></i> Salvo!</x-action-message>
                    <button data-mobile-save type="submit" class="acc-save bg-gradient-to-r from-indigo-600 to-purple-600" wire:loading.attr="disabled" wire:target="updateProfileInformation">
                        <i class="bi bi-check2-circle" wire:loading.remove wire:target="updateProfileInformation"></i>
                        <i class="bi bi-arrow-repeat animate-spin" wire:loading wire:target="updateProfileInformation"></i>
                        Salvar alterações
                    </button>
                </div>
            </form>

            <aside class="acc-side">
                {{-- Atalhos da conta --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-slate-600 to-slate-900"><i class="bi bi-grid"></i></span>
                        <div class="min-w-0">
                            <h2>Sua conta</h2>
                            <p>Tudo da conta em um lugar.</p>
                        </div>
                    </div>
                    <div class="acc-links">
                        @foreach($shortcuts as [$route, $title, $desc, $icon, $grad])
                            @if(\Illuminate\Support\Facades\Route::has($route))
                                <a href="{{ route($route) }}" wire:navigate class="acc-link">
                                    <span class="acc-link-ico bg-gradient-to-br {{ $grad }}"><i class="bi {{ $icon }}"></i></span>
                                    <span class="min-w-0"><b>{{ $title }}</b><small>{{ $desc }}</small></span>
                                    <i class="bi bi-chevron-right acc-link-go"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Resumo --}}
                <div class="ap-card">
                    <div class="ap-card-head">
                        <span class="ap-ico bg-gradient-to-br from-emerald-500 to-teal-500"><i class="bi bi-info-circle"></i></span>
                        <div class="min-w-0">
                            <h2>Resumo</h2>
                            <p>Situação da conta hoje.</p>
                        </div>
                    </div>
                    <div class="acc-facts">
                        <div><span>Tempo de conta</span><b>{{ $auDays }} {{ $auDays === 1 ? 'dia' : 'dias' }}</b></div>
                        <div><span>E-mail</span><b class="{{ $auVerified ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">{{ $auVerified ? 'Confirmado' : 'Falta confirmar' }}</b></div>
                        <div><span>Entrar com Google</span><b>{{ $auGoogle ? 'Vinculado' : 'Não vinculado' }}</b></div>
                        <div><span>Última alteração</span><b>{{ $au->updated_at ? $au->updated_at->locale('pt_BR')->diffForHumans() : '—' }}</b></div>
                    </div>
                </div>

                <livewire:settings.delete-user-form />
            </aside>
        </div>
    </div>

    <style>
        .acc-page { display: flex; flex-direction: column; gap: 1rem; }
        .acc-hero { position: relative; border-radius: 1.5rem; background: #fff; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 16px 40px -24px rgba(15, 23, 42, .35); }
        .dark .acc-hero { background: #0f172a; border-color: #1e293b; }
        .acc-cover { position: relative; height: 7.5rem; overflow: hidden; }
        .acc-cover-glow { position: absolute; inset: 0; background: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, .35), transparent 40%), radial-gradient(circle at 85% 80%, rgba(255, 255, 255, .2), transparent 35%); }
        .acc-hero-body { display: flex; align-items: flex-end; gap: 1.1rem; padding: 0 1.4rem 1rem; margin-top: -3rem; position: relative; }
        .acc-avatar { position: relative; flex: none; width: 7rem; height: 7rem; border-radius: 1.75rem; border: 4px solid #fff; background: #fff; box-shadow: 0 12px 30px -12px rgba(15, 23, 42, .5); cursor: pointer; overflow: hidden; }
        .dark .acc-avatar { border-color: #0f172a; background: #0f172a; }
        .acc-avatar img, .acc-avatar > span { width: 100%; height: 100%; object-fit: cover; display: grid; place-items: center; color: #fff; font-size: 2rem; font-weight: 800; }
        .acc-avatar > i { position: absolute; right: .35rem; bottom: .35rem; display: grid; place-items: center; width: 2rem; height: 2rem; border-radius: 999px; background: rgba(15, 23, 42, .75); color: #fff; font-size: .85rem; }
        .acc-avatar em { position: absolute; inset: 0; display: grid; place-items: center; background: rgba(15, 23, 42, .55); color: #fff; font-size: 1.5rem; }
        .acc-id { flex: 1; min-width: 0; padding-top: 3.4rem; padding-bottom: .2rem; }
        .acc-id h2 { display: flex; align-items: center; gap: .4rem; font-size: 1.45rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
        .dark .acc-id h2 { color: #f8fafc; }
        .acc-id > p { font-size: .85rem; color: #64748b; }
        .acc-chips { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .5rem; }
        .acc-chips span { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 700; padding: .25rem .6rem; border-radius: 999px; }
        .acc-hint { margin-top: .45rem; font-size: .72rem; color: #94a3b8; }
        .acc-err { margin-top: .3rem; font-size: .74rem; color: #ef4444; font-weight: 600; }
        .acc-progress { flex: none; display: flex; align-items: center; gap: .7rem; padding: .7rem .9rem; border-radius: 1.1rem; background: #f8fafc; border: 1px solid #e2e8f0; }
        .dark .acc-progress { background: #111827; border-color: #1f2937; }
        .acc-progress p { font-size: .82rem; font-weight: 800; color: #0f172a; }
        .acc-progress small { font-size: .72rem; color: #64748b; }
        .dark .acc-progress p { color: #f1f5f9; }
        .acc-ring { position: relative; width: 3.4rem; height: 3.4rem; border-radius: 999px; background: conic-gradient(var(--color-indigo-600) calc(var(--pct) * 1%), #e2e8f0 0); display: grid; place-items: center; }
        .dark .acc-ring { background: conic-gradient(var(--color-indigo-400) calc(var(--pct) * 1%), #1f2937 0); }
        .acc-ring::before { content: ''; position: absolute; inset: .35rem; border-radius: 999px; background: #f8fafc; }
        .dark .acc-ring::before { background: #111827; }
        .acc-ring b { position: relative; font-size: .78rem; font-weight: 800; color: #0f172a; }
        .dark .acc-ring b { color: #f1f5f9; }
        .acc-checks { display: flex; flex-wrap: wrap; gap: .4rem; padding: .85rem 1.4rem 1.1rem; border-top: 1px dashed #e2e8f0; }
        .dark .acc-checks { border-color: #1e293b; }
        .acc-check { display: inline-flex; align-items: center; gap: .35rem; font-size: .74rem; font-weight: 700; padding: .35rem .7rem; border-radius: 999px; color: #b45309; background: #fffbeb; border: 1px dashed #fcd34d; }
        .acc-check.is-ok { color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; }
        .dark .acc-check { color: #fcd34d; background: rgba(245, 158, 11, .1); border-color: rgba(245, 158, 11, .35); }
        .dark .acc-check.is-ok { color: #6ee7b7; background: rgba(16, 185, 129, .1); border-color: rgba(16, 185, 129, .3); }

        .acc-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 1rem; align-items: start; }
        .acc-main, .acc-side { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .acc-main .ap-card { scroll-margin-top: 1rem; }
        .acc-fields { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .85rem 1rem; }
        .acc-fields-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .acc-span2 { grid-column: span 2; }
        .acc-span3 { grid-column: 1 / -1; }
        .acc-field label { display: block; font-size: .76rem; font-weight: 700; color: #334155; margin-bottom: .35rem; }
        .acc-field label b { color: #ef4444; }
        .dark .acc-field label { color: #cbd5e1; }
        .acc-input { display: flex; align-items: center; gap: .55rem; padding: 0 .8rem; min-height: 2.85rem; border-radius: .9rem; border: 1.5px solid #e2e8f0; background: #f8fafc; transition: border-color .15s, box-shadow .15s, background .15s; }
        .acc-input:focus-within { border-color: var(--color-indigo-500); background: #fff; box-shadow: 0 0 0 4px color-mix(in oklab, var(--color-indigo-500) 15%, transparent); }
        .dark .acc-input { background: #111827; border-color: #1f2937; }
        .dark .acc-input:focus-within { background: #0b1220; border-color: var(--color-indigo-400); }
        .acc-input > i { color: #94a3b8; font-size: .95rem; }
        .acc-input input, .acc-input textarea { flex: 1; min-width: 0; border: 0 !important; outline: none !important; box-shadow: none !important; background: transparent !important; font-size: .9rem; color: #0f172a; padding: .6rem 0; }
        .dark .acc-input input, .dark .acc-input textarea { color: #f1f5f9; color-scheme: dark; }
        .acc-textarea { align-items: stretch; padding: 0 .8rem; }
        .acc-textarea textarea { resize: vertical; min-height: 5rem; }
        .acc-tag { flex: none; display: inline-flex; align-items: center; gap: .2rem; font-size: .66rem; font-weight: 800; padding: .2rem .5rem; border-radius: 999px; }
        .acc-alert { display: flex; align-items: center; gap: .75rem; margin-top: 1rem; padding: .8rem .9rem; border-radius: 1rem; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
        .acc-alert > i { font-size: 1.3rem; }
        .acc-alert div { flex: 1; min-width: 0; display: flex; flex-direction: column; font-size: .78rem; }
        .acc-alert button { flex: none; font-size: .76rem; font-weight: 800; padding: .45rem .8rem; border-radius: .7rem; background: #f59e0b; color: #fff; }
        .dark .acc-alert { background: rgba(245, 158, 11, .1); border-color: rgba(245, 158, 11, .3); color: #fcd34d; }

        .acc-savebar { position: sticky; bottom: 1rem; z-index: 5; display: flex; align-items: center; gap: .75rem; padding: .65rem .7rem .65rem 1rem; border-radius: 1.1rem; background: rgba(255, 255, 255, .92); backdrop-filter: blur(12px); border: 1px solid #e2e8f0; box-shadow: 0 14px 30px -18px rgba(15, 23, 42, .45); }
        .dark .acc-savebar { background: rgba(15, 23, 42, .92); border-color: #1e293b; }
        .acc-savebar.is-dirty { border-color: var(--color-indigo-400); box-shadow: 0 0 0 4px color-mix(in oklab, var(--color-indigo-500) 14%, transparent), 0 14px 30px -18px rgba(15, 23, 42, .45); }
        .acc-savebar-msg { flex: 1; min-width: 0; font-size: .78rem; font-weight: 700; color: #64748b; }
        .acc-savebar.is-dirty .acc-savebar-msg { color: var(--color-indigo-700); }
        .dark .acc-savebar.is-dirty .acc-savebar-msg { color: var(--color-indigo-300); }
        .acc-saved-msg { font-size: .78rem; font-weight: 800; color: #059669; }
        .acc-save { display: inline-flex; align-items: center; gap: .4rem; color: #fff; font-size: .85rem; font-weight: 800; padding: .65rem 1.15rem; border-radius: .9rem; box-shadow: 0 10px 20px -10px color-mix(in oklab, var(--color-indigo-600) 70%, transparent); }
        .acc-save:disabled { opacity: .7; }

        .acc-links { display: flex; flex-direction: column; gap: .3rem; }
        .acc-link { display: flex; align-items: center; gap: .7rem; padding: .55rem .6rem; border-radius: .9rem; transition: background .15s; }
        .acc-link:hover { background: #f8fafc; }
        .dark .acc-link:hover { background: #111827; }
        .acc-link-ico { flex: none; display: grid; place-items: center; width: 2.2rem; height: 2.2rem; border-radius: .75rem; color: #fff; }
        .acc-link b { display: block; font-size: .83rem; color: #0f172a; }
        .acc-link small { display: block; font-size: .7rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dark .acc-link b { color: #f1f5f9; }
        .dark .acc-link small { color: #94a3b8; }
        .acc-link-go { margin-left: auto; color: #cbd5e1; }
        .acc-facts { display: flex; flex-direction: column; }
        .acc-facts div { display: flex; justify-content: space-between; gap: .75rem; padding: .55rem 0; border-top: 1px dashed #e2e8f0; font-size: .8rem; }
        .acc-facts div:first-child { border-top: 0; }
        .dark .acc-facts div { border-color: #1e293b; }
        .acc-facts span { color: #64748b; }
        .acc-facts b { color: #0f172a; text-align: right; }
        .dark .acc-facts b { color: #f1f5f9; }
        .acc-side .settings-card { margin: 0 !important; border-radius: 1.25rem; }

        @media (max-width: 1279px) { .acc-grid { grid-template-columns: minmax(0, 1fr) 320px; } }
        @media (max-width: 1023px) {
            .acc-grid { grid-template-columns: minmax(0, 1fr); }
            .acc-side { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .acc-side > :last-child { grid-column: 1 / -1; }
            .acc-hero-body { flex-wrap: wrap; }
            .acc-progress { width: 100%; }
        }
        @media (max-width: 767px) {
            .acc-cover { height: 5.5rem; }
            .acc-hero-body { padding: 0 1rem .9rem; margin-top: -2.6rem; gap: .8rem; }
            .acc-avatar { width: 5.5rem; height: 5.5rem; border-radius: 1.4rem; }
            .acc-id { flex-basis: 100%; padding-top: 0; }
            .acc-id h2 { font-size: 1.15rem; }
            .acc-checks { padding: .75rem 1rem 1rem; }
            .acc-fields, .acc-fields-2 { grid-template-columns: minmax(0, 1fr); }
            .acc-span2 { grid-column: auto; }
            .acc-side { display: flex; }
            .acc-savebar { bottom: calc(env(safe-area-inset-bottom) + 84px); }
            .acc-savebar-msg { font-size: .72rem; }
        }
    </style>
    </x-settings.layout>
</section>

<script src="https://unpkg.com/imask@7.6.1/dist/imask.min.js"></script>
<script>
// Máscara do telefone. O script do IMask pode chegar depois desta parte (navegação sem recarregar),
// então espera ele existir e registra os eventos uma vez só.
(function () {
    function maskPhone(tries) {
        var phoneInput = document.getElementById('phoneInput');
        if (!phoneInput || phoneInput._imask) return;
        if (typeof window.IMask === 'undefined') {
            if ((tries || 0) < 40) setTimeout(function () { maskPhone((tries || 0) + 1); }, 100);
            return;
        }
        phoneInput._imask = window.IMask(phoneInput, { mask: '+00 (00) 00000-0000', lazy: false, overwrite: true });
    }
    if (!window.__fmPhoneMaskBound) {
        window.__fmPhoneMaskBound = true;
        document.addEventListener('DOMContentLoaded', function () { maskPhone(0); });
        document.addEventListener('livewire:navigated', function () { maskPhone(0); });
    }
    maskPhone(0);
})();
</script>
