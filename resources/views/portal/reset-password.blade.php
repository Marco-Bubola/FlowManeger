<x-portal-auth-layout title="Nova senha" heading="Definir nova senha" :subtitle="!empty($clientName) ? 'Olá, '.$clientName.'. Crie uma nova senha para voltar a acessar o portal.' : 'Crie uma nova senha para voltar a acessar o portal.'">
    <form method="POST" action="{{ route('portal.password.update') }}" class="pa-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label class="pa-label" for="login">Login do portal</label>
            <div class="pa-input-wrap">
                <i class="fas fa-user"></i>
                <input id="login" type="text" name="login" value="{{ old('login', $login) }}" readonly required class="pa-input">
            </div>
            @error('login')<p class="pa-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="pa-label" for="password">Nova senha</label>
            <div class="pa-input-wrap">
                <i class="fas fa-lock"></i>
                <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="mín. 8 caracteres" class="pa-input pa-has-eye @error('password') is-invalid @enderror">
                <button type="button" class="pa-eye" onclick="paTogglePass(this)" aria-label="Mostrar senha"><i class="fas fa-eye"></i></button>
            </div>
            @error('password')<p class="pa-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="pa-label" for="password_confirmation">Confirmar senha</label>
            <div class="pa-input-wrap">
                <i class="fas fa-lock"></i>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" class="pa-input pa-has-eye">
                <button type="button" class="pa-eye" onclick="paTogglePass(this)" aria-label="Mostrar senha"><i class="fas fa-eye"></i></button>
            </div>
        </div>

        <button type="submit" class="pa-btn"><i class="fas fa-check"></i> Salvar nova senha</button>
    </form>
</x-portal-auth-layout>
