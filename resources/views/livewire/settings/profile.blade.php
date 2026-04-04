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

<section class="settings-profile-page w-full mobile-393-base">

    <x-settings.layout :heading="''">
        <div class="s-pg-grid">
        <div class="s-col-main">

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

                    {{-- Data de nascimento --}}
                    <div>
                        <label class="settings-field-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.9rem;height:0.9rem;display:inline;vertical-align:-0.1em;margin-right:0.25rem;opacity:0.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5A2.25 2.25 0 0 0 12.75 4.5h-1.5A2.25 2.25 0 0 0 9 6.75v1.5m3 13.5V21m-3-3h6m-6 0a1.5 1.5 0 0 1-1.5-1.5V15a1.5 1.5 0 0 1 1.5-1.5h6a1.5 1.5 0 0 1 1.5 1.5v.375a1.5 1.5 0 0 1-1.5 1.5H9Z"/></svg>
                            {{ __('Data de nascimento') }}
                        </label>
                        <flux:input wire:model="birth_date" type="date" placeholder="dd/mm/aaaa"/>
                        @error('birth_date') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                    </div>
                </div>

                <hr class="settings-divider">

                {{-- Redes Sociais --}}
                <div style="margin-bottom:1rem">
                    <p class="settings-sect-label">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.9rem;height:0.9rem;color:var(--s-accent)"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                        Presenca Online
                    </p>
                    <div class="settings-form-grid">
                        {{-- Website --}}
                        <div>
                            <label class="settings-field-label">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.9rem;height:0.9rem;display:inline;vertical-align:-0.1em;margin-right:0.25rem;opacity:0.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253M3 12c0 .778.099 1.533.284 2.253"/></svg>
                                Website
                            </label>
                            <flux:input wire:model="website" type="url" placeholder="https://seusite.com"/>
                            @error('website') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                        </div>

                        {{-- LinkedIn --}}
                        <div>
                            <label class="settings-field-label">
                                <svg viewBox="0 0 24 24" fill="currentColor" style="width:0.9rem;height:0.9rem;display:inline;vertical-align:-0.1em;margin-right:0.25rem;opacity:0.6;color:#0077b5"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                LinkedIn
                            </label>
                            <flux:input wire:model="linkedin" type="text" placeholder="usuario-linkedin"/>
                            @error('linkedin') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                        </div>

                        {{-- Instagram --}}
                        <div>
                            <label class="settings-field-label">
                                <svg viewBox="0 0 24 24" fill="currentColor" style="width:0.9rem;height:0.9rem;display:inline;vertical-align:-0.1em;margin-right:0.25rem;opacity:0.6;color:#e1306c"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                                Instagram
                            </label>
                            <flux:input wire:model="instagram" type="text" placeholder="@seuperfil"/>
                            @error('instagram') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                        </div>

                        {{-- Twitter/X --}}
                        <div>
                            <label class="settings-field-label">
                                <svg viewBox="0 0 24 24" fill="currentColor" style="width:0.9rem;height:0.9rem;display:inline;vertical-align:-0.1em;margin-right:0.25rem;opacity:0.6"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.258 5.63L18.244 2.25Zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                Twitter / X
                            </label>
                            <flux:input wire:model="twitter" type="text" placeholder="@seuusername"/>
                            @error('twitter') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                        </div>
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

        </div>{{-- /s-col-main --}}

        <div class="s-col-side">

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

        </div>{{-- /s-col-side --}}

        <div class="s-col-full">
        {{-- EXCLUIR CONTA --}}
        <livewire:settings.delete-user-form />
        </div>{{-- /s-col-full --}}

        </div>{{-- /s-pg-grid --}}
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
