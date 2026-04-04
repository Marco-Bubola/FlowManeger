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

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->location = $user->location ?? '';
        $this->about_me = $user->about_me ?? '';
        $this->role_id = $user->role_id ?? '';
        $this->google_id = $user->google_id ?? '';
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

<section class="settings-profile-page w-full mobile-393-base">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/settings-profile-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/settings-profile-iphone15.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/settings-profile-ipad-portrait.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/settings-profile-ipad-landscape.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/settings-profile-notebook.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive/settings-profile-ultrawide.css') }}">

    @include('partials.settings-heading')

    <x-settings.layout :heading="''">

        {{-- ── CARD DE PERFIL ── --}}
        <div class="settings-card">

            {{-- Cabeçalho do card --}}
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.5a8.25 8.25 0 0 1 15 0"/>
                        </svg>
                    </div>
                    <div>
                        <p class="settings-card-title">{{ __('Perfil') }}</p>
                        <p class="settings-card-desc">{{ __('Informações pessoais e foto de perfil') }}</p>
                    </div>
                </div>
                <span class="s-badge s-badge-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.75rem;height:0.75rem"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/></svg>
                    Conta pessoal
                </span>
            </div>

            {{-- Barra de completude --}}
            <div class="settings-completion-wrap">
                <div style="width:2.2rem;height:2.2rem;border-radius:50%;background:rgba(var(--s-accent-rgb),0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--s-accent)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <div class="settings-completion-text">
                    <div class="settings-completion-label">
                        Completude do perfil
                        <span class="settings-completion-pct" id="completionPct">—</span>
                    </div>
                    <div class="settings-completion-bar">
                        <div class="settings-completion-fill" id="completionFill" style="width:0%"></div>
                    </div>
                </div>
            </div>

            {{-- Avatar --}}
            <form wire:submit="updateProfileInformation">
                <div class="settings-avatar-row">
                    <div class="settings-avatar-wrap" onclick="document.querySelector('[data-avatar-input]').click()">
                        @if(auth()->user()->profile_picture)
                            <img id="avatarPreviewImg"
                                 src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
                                 alt="Foto de perfil"
                                 class="settings-avatar-img">
                        @else
                            <div class="settings-avatar-placeholder" id="avatarPlaceholder">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:2.5rem;height:2.5rem">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.5a8.25 8.25 0 0 1 15 0"/>
                                </svg>
                            </div>
                            <img id="avatarPreviewImg" src="" alt="Foto de perfil" class="settings-avatar-img hidden">
                        @endif
                        <div class="settings-avatar-overlay">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1.25rem;height:1.25rem">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="settings-avatar-info">
                        <h3>{{ auth()->user()->name }}</h3>
                        <p>{{ auth()->user()->email }}</p>
                        <div>
                            <label style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.45rem 0.9rem;border-radius:0.6rem;font-size:0.8rem;font-weight:600;cursor:pointer;background:rgba(var(--s-accent-rgb),0.1);color:var(--s-accent);border:1.5px solid rgba(var(--s-accent-rgb),0.15);transition:all 0.18s">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.9rem;height:0.9rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                                Trocar foto
                                <input type="file" wire:model="profile_picture" data-avatar-input accept="image/*" style="display:none">
                            </label>
                        </div>
                        <p class="settings-avatar-tips">JPG, PNG ou WebP · Máx 2MB</p>
                        @error('profile_picture') <p style="color:#ef4444;font-size:0.75rem;margin-top:0.25rem">{{ $message }}</p> @enderror
                    </div>
                </div>

                <hr class="settings-divider">

                {{-- Dados do perfil --}}
                <div class="settings-form-grid">
                    {{-- Nome --}}
                    <div>
                        <label class="settings-field-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.9rem;height:0.9rem;display:inline;vertical-align:-0.1em;margin-right:0.25rem;opacity:0.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                            {{ __('Nome completo') }} <span style="color:#ef4444">*</span>
                        </label>
                        <flux:input wire:model="name" type="text" required autofocus autocomplete="name" placeholder="Seu nome completo"/>
                        @error('name') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="settings-field-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.9rem;height:0.9rem;display:inline;vertical-align:-0.1em;margin-right:0.25rem;opacity:0.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                            {{ __('E-mail') }} <span style="color:#ef4444">*</span>
                        </label>
                        <flux:input wire:model="email" type="email" required autocomplete="email" placeholder="seu@email.com"/>
                        @error('email') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                    </div>

                    {{-- Telefone --}}
                    <div>
                        <label class="settings-field-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.9rem;height:0.9rem;display:inline;vertical-align:-0.1em;margin-right:0.25rem;opacity:0.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                            {{ __('Telefone') }}
                        </label>
                        <flux:input wire:model="phone" type="text" autocomplete="tel" id="phoneInput"
                            inputmode="tel" placeholder="+55 (11) 99999-9999"/>
                        @error('phone') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                    </div>

                    {{-- Localização --}}
                    <div>
                        <label class="settings-field-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.9rem;height:0.9rem;display:inline;vertical-align:-0.1em;margin-right:0.25rem;opacity:0.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            {{ __('Localização') }}
                        </label>
                        <flux:input wire:model="location" type="text" placeholder="Cidade, Estado"/>
                        @error('location') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                    </div>

                    {{-- Sobre mim (full) --}}
                    <div class="settings-form-full">
                        <label class="settings-field-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.9rem;height:0.9rem;display:inline;vertical-align:-0.1em;margin-right:0.25rem;opacity:0.6"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                            {{ __('Sobre mim') }}
                        </label>
                        <flux:textarea wire:model="about_me" rows="3" placeholder="Conte um pouco sobre você..."/>
                        @error('about_me') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                    </div>

                    {{-- Google ID (full) --}}
                    <div class="settings-form-full">
                        <label class="settings-field-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.9rem;height:0.9rem;display:inline;vertical-align:-0.1em;margin-right:0.25rem;opacity:0.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                            {{ __('Google ID') }}
                            <span class="s-badge s-badge-accent" style="margin-left:0.35rem">Opcional</span>
                        </label>
                        <flux:input wire:model="google_id" type="text" placeholder="ID Google vinculado"/>
                        @error('google_id') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Email não verificado --}}
                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                <div style="padding:0.875rem 1rem;background:#fefce8;border:1px solid #fde68a;border-radius:0.75rem;margin-top:1rem;font-size:0.83rem;color:#92400e;display:flex;gap:0.75rem;align-items:flex-start">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1.1rem;height:1.1rem;flex-shrink:0;margin-top:0.1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                    <div>
                        {{ __('Seu e-mail não está verificado.') }}
                        <button type="button" wire:click.prevent="resendVerificationNotification" style="text-decoration:underline;cursor:pointer;background:none;border:none;color:#d97706;font-weight:600;font-size:0.83rem;padding:0">
                            {{ __('Reenviar e-mail de verificação') }}
                        </button>
                        @if (session('status') === 'verification-link-sent')
                            <p style="color:#15803d;font-weight:600;margin-top:0.25rem">{{ __('Link enviado! Verifique sua caixa de entrada.') }}</p>
                        @endif
                    </div>
                </div>
                @endif

                <hr class="settings-divider">

                {{-- Botão salvar --}}
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
                    <button type="submit" class="settings-save-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        {{ __('Salvar alterações') }}
                    </button>
                    <x-action-message on="profile-updated">
                        <span style="display:inline-flex;align-items:center;gap:0.35rem;color:#059669;font-size:0.83rem;font-weight:600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ __('Salvo com sucesso!') }}
                        </span>
                    </x-action-message>
                </div>
            </form>
        </div>

        {{-- CARD: Informações da conta --}}
        @php
            $au = Auth::user();
            $auVerified = $au->hasVerifiedEmail();
            $auGoogle   = !empty($au->google_id);
            $auDays     = $au->created_at ? (int)$au->created_at->diffInDays(now()) : 0;
            $auMonths   = $au->created_at ? $au->created_at->diffInMonths(now()) : 0;
        @endphp
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(99,102,241,.1);color:#6366f1">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Informações da conta</p>
                        <p class="settings-card-desc">Detalhes e status da sua conta FlowManager</p>
                    </div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.75rem;padding:0 1.25rem 1.25rem">
                @foreach([
                    ['ID da conta','#'.$au->id,'rgba(99,102,241,.08)','#6366f1'],
                    ['Membro desde',$au->created_at ? $au->created_at->format('d/m/Y') : '—','rgba(16,185,129,.08)','#10b981'],
                    ['Tempo de conta',$auMonths > 0 ? $auMonths.' meses' : $auDays.' dias','rgba(245,158,11,.08)','#f59e0b'],
                    ['E-mail',$auVerified ? 'Verificado ✓' : 'Não verificado',$auVerified ? 'rgba(16,185,129,.08)' : 'rgba(239,68,68,.08)',$auVerified ? '#10b981' : '#ef4444'],
                    ['Login Google',$auGoogle ? 'Vinculado ✓' : 'Não vinculado',$auGoogle ? 'rgba(66,133,244,.08)' : 'rgba(148,163,184,.08)',$auGoogle ? '#4285F4' : '#94a3b8'],
                    ['Última atualização',$au->updated_at ? $au->updated_at->diffForHumans() : '—','rgba(var(--s-accent-rgb),.06)','var(--s-accent)'],
                ] as [$lbl,$val,$bg,$col])
                <div class="settings-info-tile" style="background:{{ $bg }};border-color:transparent">
                    <p class="settings-info-tile-label">{{ $lbl }}</p>
                    <p class="settings-info-tile-val" style="color:{{ $col }}">{{ $val }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- EXCLUIR CONTA --}}
        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>

<script src="https://unpkg.com/imask"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var phoneInput = document.getElementById('phoneInput');
    if (phoneInput) {
        IMask(phoneInput, { mask: '+00 (00) 00000-0000', lazy: false, overwrite: true });
    }
});
document.addEventListener('livewire:navigated', function () {
    var phoneInput = document.getElementById('phoneInput');
    if (phoneInput && !phoneInput._imask) {
        IMask(phoneInput, { mask: '+00 (00) 00000-0000', lazy: false, overwrite: true });
    }
});
</script>
