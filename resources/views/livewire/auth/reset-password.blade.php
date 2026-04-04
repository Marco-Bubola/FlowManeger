<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PasswordReset) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div style="display:flex;flex-direction:column;gap:1.4rem;">

    <div style="text-align:center;margin-bottom:0.2rem;">
        <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:16px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);margin-bottom:0.8rem;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <h1 class="auth-heading">Nova senha</h1>
        <p class="auth-subheading" style="margin-top:0.3rem;">Defina uma nova senha segura para sua conta</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form wire:submit="resetPassword" style="display:flex;flex-direction:column;gap:0.9rem;">
        <div>
            <label class="auth-label">E-mail</label>
            <input wire:model="email" type="email" required autocomplete="email" readonly class="auth-input" style="opacity:0.5;cursor:not-allowed;">
            @error('email') <p class="auth-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="auth-label" for="rp-pw">Nova senha</label>
            <div style="position:relative;">
                <input id="rp-pw" wire:model="password" type="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres" class="auth-input auth-input-pw" oninput="checkPasswordStrength(this.value)">
                <button type="button" class="auth-pw-toggle" onclick="toggleAuthPassword('rp-pw')" aria-label="Mostrar senha">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <div style="margin-top:0.5rem;display:flex;align-items:center;gap:0.6rem;">
                <div style="flex:1;background:rgba(255,255,255,0.07);border-radius:2px;height:3px;overflow:hidden;">
                    <div id="pw-strength-bar" class="pw-strength-bar" style="width:0%;height:100%;"></div>
                </div>
                <span id="pw-strength-text" style="font-size:0.68rem;font-weight:600;min-width:60px;"></span>
            </div>
            @error('password') <p class="auth-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="auth-label" for="rp-pw2">Confirmar nova senha</label>
            <div style="position:relative;">
                <input id="rp-pw2" wire:model="password_confirmation" type="password" required autocomplete="new-password" placeholder="Repita a senha" class="auth-input auth-input-pw">
                <button type="button" class="auth-pw-toggle" onclick="toggleAuthPassword('rp-pw2')" aria-label="Mostrar confirmação">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>
        <button type="submit" class="auth-btn-primary" wire:loading.attr="disabled" style="background:linear-gradient(135deg,#10b981,#06b6d4);">
            <span wire:loading.remove wire:target="resetPassword">Redefinir senha →</span>
            <span wire:loading wire:target="resetPassword" style="display:inline-flex;align-items:center;gap:0.5rem;">
                <svg style="animation:spin 0.8s linear infinite;" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3"/><path d="M12 2a10 10 0 0110 10" stroke="#fff" stroke-width="3" stroke-linecap="round"/></svg>
                Redefinindo...
            </span>
        </button>
    </form>
</div>

@push('scripts')
    @include('partials.firebase-auth')
@endpush
