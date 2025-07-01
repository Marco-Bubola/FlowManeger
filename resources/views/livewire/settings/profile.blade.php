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

    /**
     * Mount the component.
     */
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
        // Não preenche password nem profile_picture por segurança
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'location' => ['nullable', 'string', 'max:255'],
            'about_me' => ['nullable', 'string', 'max:1000'],
            'google_id' => ['nullable', 'string', 'max:255'],
        ]);

        $user->fill($validated);


        // Upload da foto de perfil se fornecida
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

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="''">
        <div class="flex items-center gap-2 mb-2">
            <!-- Ícone de usuário no heading -->
            <svg class="w-7 h-7 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5a8.25 8.25 0 1115 0v.75A2.25 2.25 0 0117.25 22.5h-10.5A2.25 2.25 0 014.5 20.25v-.75z" /></svg>
            <span class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('Perfil') }}</span>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
            <!-- Ícone de lápis no subtítulo -->
            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 113.182 3.182L7.125 19.586a2.25 2.25 0 01-1.06.592l-4.125 1.031a.563.563 0 01-.686-.686l1.03-4.125a2.25 2.25 0 01.593-1.06L16.862 3.487z" /></svg>
            <span>{{ __('Atualize seu nome e endereço de e-mail') }}</span>
        </div>
        <form wire:submit="updateProfileInformation" class="">
            <!-- Foto de perfil -->
            <div class="flex flex-col items-center gap-4 pb-6 border-b border-gray-200 dark:border-gray-700">
                @if (auth()->user()->profile_picture)
                    <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="Foto de perfil atual" class="w-24 h-24 rounded-full object-cover border-4 border-primary-500 shadow" />
                @else
                    <div class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center border-4 border-primary-500 shadow">
                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5a8.25 8.25 0 1115 0v.75A2.25 2.25 0 0117.25 22.5h-10.5A2.25 2.25 0 014.5 20.25v-.75z" /></svg>
                    </div>
                @endif
                <div class="w-full max-w-xs">
                    <flux:input wire:model="profile_picture" :label="__('Foto de perfil')" type="file" accept="image/*" icon="photo" />
                    @error('profile_picture') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            <!-- Dados principais -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <flux:input wire:model="name" :label="__('Nome')" type="text" required autofocus autocomplete="name" icon="user" />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:input wire:model="email" :label="__('E-mail')" type="email" required autocomplete="email" icon="envelope" />
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:input wire:model="phone" :label="__('Telefone')" type="text" autocomplete="tel" icon="phone" inputmode="tel" pattern="\+\d{2} \(\d{2}\) \d{5}-\d{4}" placeholder="+99 (99) 99999-9999" />
                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:input wire:model="location" :label="__('Localização')" type="text" icon="map-pin" />
                    @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <flux:input wire:model="about_me" :label="__('Sobre mim')" type="text" icon="information-circle" />
                    @error('about_me') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <flux:input wire:model="google_id" :label="__('Google ID')" type="text" icon="link" />
                    @error('google_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- E-mail não verificado -->
            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                <div>
                    <flux:text class="mt-4">
                        {{ __('Seu endereço de e-mail não está verificado.') }}
                        <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification" icon="arrow-path">
                        {{ __('Clique aqui para reenviar o e-mail de verificação.') }}
                        </flux:link>
                    </flux:text>
                    @if (session('status') === 'verification-link-sent')
                    <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                        {{ __('Um novo link de verificação foi enviado para o seu endereço de e-mail.') }}
                    </flux:text>
                    @endif
                </div>
                @endif
            </div>

            <div class="flex flex-col md:flex-row items-center gap-4 mt-8">
                <div class="flex-1 flex items-center justify-end w-full">
                    <flux:button variant="primary" type="submit" class="w-full md:w-auto flex items-center justify-center gap-2 py-3 px-8 text-lg font-semibold rounded-xl shadow-md transition-all duration-200 bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-400" icon="check-circle">
                        {{ __('Salvar') }}
                    </flux:button>
                </div>
                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Salvo.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>

<!-- Máscara de telefone usando IMask.js -->
<script src="https://unpkg.com/imask"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var phoneInput = document.querySelector('input[placeholder="+99 (99) 99999-9999"]');
    if (phoneInput) {
        var maskOptions = {
            mask: '+00 (00) 00000-0000',
            lazy: false, // mostra a máscara completa
            overwrite: true,
            prepare: function(appended, masked) {
                // Garante que o + não seja removido
                if (masked.value === '' && appended !== '+') {
                    return '+' + appended;
                }
                return appended;
            }
        };
        IMask(phoneInput, maskOptions);
    }
});
</script>