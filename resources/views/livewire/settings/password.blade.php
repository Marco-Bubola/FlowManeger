<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('password-updated');
    }
}; ?>

<section class="settings-password-page w-full mobile-393-base">

    <x-settings.layout :heading="''">

        <div class="settings-pw-layout">

            {{-- CARD PRINCIPAL — Formulário --}}
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75A4.5 4.5 0 0 0 7.5 6.75v3.75m9 0a2.25 2.25 0 0 1 2.25 2.25v6A2.25 2.25 0 0 1 16.5 21H7.5a2.25 2.25 0 0 1-2.25-2.25v-6a2.25 2.25 0 0 1 2.25-2.25m9 0h-9"/>
                            </svg>
                        </div>
                        <div>
                            <p class="settings-card-title">{{ __('Atualizar senha') }}</p>
                            <p class="settings-card-desc">{{ __('Use uma senha longa e única para maior segurança') }}</p>
                        </div>
                    </div>
                </div>

                <form wire:submit="updatePassword" style="display:flex;flex-direction:column;gap:1.25rem">

                    {{-- Senha Atual --}}
                    <div>
                        <label class="settings-field-label" for="currentPasswordField">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.85rem;height:0.85rem;display:inline;vertical-align:-0.12em;margin-right:0.2rem;opacity:0.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z"/></svg>
                            {{ __('Senha atual') }}
                        </label>
                        <div class="settings-pass-wrap">
                            <flux:input
                                wire:model="current_password"
                                type="password"
                                id="currentPasswordField"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            />
                            <button type="button" class="settings-pass-toggle"
                                data-pass-toggle="currentPasswordField"
                                title="Mostrar/ocultar senha">
                                <svg data-eye-open xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                <svg data-eye-close class="hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                        @error('current_password') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                    </div>

                    {{-- Nova Senha --}}
                    <div>
                        <label class="settings-field-label" for="newPasswordField">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.85rem;height:0.85rem;display:inline;vertical-align:-0.12em;margin-right:0.2rem;opacity:0.6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75A4.5 4.5 0 0 0 7.5 6.75v3.75m9 0a2.25 2.25 0 0 1 2.25 2.25v6A2.25 2.25 0 0 1 16.5 21H7.5a2.25 2.25 0 0 1-2.25-2.25v-6a2.25 2.25 0 0 1 2.25-2.25m9 0h-9"/></svg>
                            {{ __('Nova senha') }}
                        </label>
                        <div class="settings-pass-wrap">
                            <flux:input
                                wire:model="password"
                                type="password"
                                id="newPasswordField"
                                required
                                autocomplete="new-password"
                                placeholder="Mínimo 8 caracteres"
                                data-password-strength
                            />
                            <button type="button" class="settings-pass-toggle"
                                data-pass-toggle="newPasswordField"
                                title="Mostrar/ocultar senha">
                                <svg data-eye-open xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                <svg data-eye-close class="hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                        {{-- Medidor de força --}}
                        <div class="password-strength-wrap" id="passwordStrengthWrap">
                            <div class="password-strength-bars">
                                <div class="password-strength-bar"></div>
                                <div class="password-strength-bar"></div>
                                <div class="password-strength-bar"></div>
                                <div class="password-strength-bar"></div>
                                <div class="password-strength-bar"></div>
                            </div>
                            <p class="password-strength-text"></p>
                        </div>
                        @error('password') <p style="color:#ef4444;font-size:0.73rem;margin-top:0.2rem">{{ $message }}</p> @enderror
                    </div>

                    {{-- Confirmar Senha --}}
                    <div>
                        <label class="settings-field-label" for="confirmPasswordField">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:0.85rem;height:0.85rem;display:inline;vertical-align:-0.12em;margin-right:0.2rem;opacity:0.6"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Confirmar nova senha') }}
                        </label>
                        <div class="settings-pass-wrap">
                            <flux:input
                                wire:model="password_confirmation"
                                type="password"
                                id="confirmPasswordField"
                                required
                                autocomplete="new-password"
                                placeholder="Repita a nova senha"
                            />
                            <button type="button" class="settings-pass-toggle"
                                data-pass-toggle="confirmPasswordField"
                                title="Mostrar/ocultar senha">
                                <svg data-eye-open xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                <svg data-eye-close class="hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                    </div>

                    <hr class="settings-divider">

                    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
                        <button type="submit" class="settings-save-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Atualizar senha') }}
                        </button>
                        <x-action-message on="password-updated">
                            <span style="display:inline-flex;align-items:center;gap:0.35rem;color:#059669;font-size:0.83rem;font-weight:600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ __('Senha atualizada!') }}
                            </span>
                        </x-action-message>
                    </div>
                </form>
            </div>

            {{-- COLUNA LATERAL --}}
            <div class="settings-pw-side">

                {{-- CARD: Gerador de senha --}}
                <div class="settings-card" x-data="{
                    genLen: 16,
                    useUpper: true,
                    useLower: true,
                    useNum: true,
                    useSym: true,
                    generated: '',
                    copied: false,
                    generate() {
                        const up='ABCDEFGHIJKLMNOPQRSTUVWXYZ',lo='abcdefghijklmnopqrstuvwxyz',nu='0123456789',sy='!@#$%^&*()_+-=[]{}';
                        let pool='';
                        if(this.useUpper) pool+=up;
                        if(this.useLower) pool+=lo;
                        if(this.useNum) pool+=nu;
                        if(this.useSym) pool+=sy;
                        if(!pool) return;
                        let pw='';
                        for(let i=0;i<this.genLen;i++) pw+=pool[Math.floor(Math.random()*pool.length)];
                        this.generated=pw;
                    },
                    copy() {
                        if(!this.generated) return;
                        navigator.clipboard.writeText(this.generated).then(()=>{this.copied=true;setTimeout(()=>this.copied=false,2000);});
                    }
                }" x-init="generate()">
                    <div class="settings-card-header" style="border-bottom:1px solid rgba(0,0,0,.05);padding-bottom:.75rem;margin-bottom:.75rem">
                        <div class="settings-card-title-row">
                            <div class="settings-card-icon" style="background:rgba(99,102,241,.12);color:#6366f1">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"/></svg>
                            </div>
                            <div>
                                <p class="settings-card-title" style="font-size:.82rem">Gerador de senha</p>
                                <p class="settings-card-desc" style="font-size:.72rem">Crie uma senha forte aleatória</p>
                            </div>
                        </div>
                    </div>
                    <div style="padding:.25rem 0 .75rem;display:flex;flex-direction:column;gap:.65rem">
                        {{-- Output --}}
                        <div style="padding:.6rem .85rem;border-radius:.55rem;background:rgba(99,102,241,.06);border:1.5px solid rgba(99,102,241,.15);font-size:.82rem;font-family:monospace;color:#4338ca;word-break:break-all;min-height:2.4rem;letter-spacing:.05em" x-text="generated || '—'"></div>
                        {{-- Length --}}
                        <div style="display:flex;align-items:center;gap:.6rem">
                            <label style="font-size:.72rem;color:#64748b;white-space:nowrap;font-weight:600">Comprimento: <span style="color:#6366f1" x-text="genLen"></span></label>
                            <input type="range" min="8" max="32" x-model.number="genLen" @input="generate()" style="flex:1;accent-color:#6366f1;cursor:pointer">
                        </div>
                        {{-- Options --}}
                        <div style="display:flex;flex-wrap:wrap;gap:.4rem">
                            <template x-for="[key,lbl] in [['useUpper','A-Z'],['useLower','a-z'],['useNum','0-9'],['useSym','!@#']]" :key="key">
                                <button type="button"
                                    :class="$data[key] ? 'spw-chip spw-chip--on' : 'spw-chip'"
                                    @click="$data[key]=!$data[key];generate()" x-text="lbl"></button>
                            </template>
                        </div>
                        {{-- Actions --}}
                        <div style="display:flex;gap:.5rem">
                            <button type="button" @click="generate()" style="flex:1;padding:.45rem .6rem;border-radius:.5rem;font-size:.76rem;font-weight:700;background:rgba(99,102,241,.1);color:#6366f1;border:1.5px solid rgba(99,102,241,.2);cursor:pointer">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:.8rem;height:.8rem;display:inline;vertical-align:-.1em;margin-right:.25rem"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                Gerar
                            </button>
                            <button type="button" @click="copy()" style="flex:1;padding:.45rem .6rem;border-radius:.5rem;font-size:.76rem;font-weight:700;cursor:pointer;transition:all .2s"
                                :style="copied ? 'background:#10b981;color:#fff;border:1.5px solid #10b981' : 'background:rgba(16,185,129,.1);color:#059669;border:1.5px solid rgba(16,185,129,.2)'">
                                <span x-show="!copied">
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:.8rem;height:.8rem;display:inline;vertical-align:-.1em;margin-right:.25rem"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/></svg>
                                    Copiar
                                </span>
                                <span x-show="copied">Copiado!</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- CARD: Dicas de segurança --}}
                <div class="settings-card">
                    <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:1rem">
                        <div style="width:1.8rem;height:1.8rem;border-radius:0.45rem;background:rgba(16,185,129,0.1);display:flex;align-items:center;justify-content:center;color:#059669;flex-shrink:0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.9rem;height:0.9rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        </div>
                        <p class="settings-card-subtitle">Dicas de segurança</p>
                    </div>
                    <ul class="settings-security-tips">
                        <li><span class="tip-dot"></span>Use pelo menos 12 caracteres</li>
                        <li><span class="tip-dot"></span>Misture letras maiúsculas e minúsculas</li>
                        <li><span class="tip-dot"></span>Inclua números e símbolos (!@#$%)</li>
                        <li><span class="tip-dot tip-warn"></span>Não reutilize senhas antigas</li>
                        <li><span class="tip-dot tip-warn"></span>Evite dados pessoais óbvios</li>
                        <li><span class="tip-dot tip-danger"></span>Nunca compartilhe sua senha</li>
                    </ul>
                </div>

                <div class="settings-card" style="background:linear-gradient(135deg,rgba(var(--s-accent-rgb),0.06),transparent)">
                    <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.75rem">
                        <div style="width:1.8rem;height:1.8rem;border-radius:0.45rem;background:rgba(var(--s-accent-rgb),0.1);display:flex;align-items:center;justify-content:center;color:var(--s-accent);flex-shrink:0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:0.9rem;height:0.9rem"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                        </div>
                        <p class="settings-card-subtitle">Autenticação 2FA</p>
                    </div>
                    <p style="font-size:0.78rem;color:#64748b;line-height:1.5" class="dark:text-slate-400">Ative a autenticação em dois fatores para uma camada extra de segurança na sua conta.</p>
                    <p style="font-size:0.72rem;color:#94a3b8;margin-top:0.5rem">Em breve disponível</p>
                </div>
            </div>
        </div>

    </x-settings.layout>
</section>
