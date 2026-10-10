<x-portal-auth-layout title="Recuperar senha" heading="Recuperar senha" subtitle="Informe seu login do portal. Enviamos o link para redefinir no e-mail cadastrado.">
    <form method="POST" action="{{ route('portal.password.email') }}" class="pa-form">
        @csrf
        <div>
            <label class="pa-label" for="login">Login do portal</label>
            <div class="pa-input-wrap">
                <i class="fas fa-user"></i>
                <input id="login" type="text" name="login" value="{{ old('login') }}" required autocapitalize="none" placeholder="cli-000123" class="pa-input @error('login') is-invalid @enderror">
            </div>
            @error('login')<p class="pa-error">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="pa-btn"><i class="fas fa-paper-plane"></i> Enviar link</button>
    </form>

    <div class="pa-foot">
        <a href="{{ route('portal.login') }}" class="pa-back"><i class="fas fa-arrow-left"></i> Voltar ao login</a>
    </div>
</x-portal-auth-layout>
