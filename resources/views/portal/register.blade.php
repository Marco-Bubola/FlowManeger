<x-portal-auth-layout title="Criar conta" heading="Criar sua conta" subtitle="Leva menos de um minuto. Depois é só finalizar o pedido." :store="$store">
    <ol class="pa-steps">
        <li class="is-on"><span class="pa-dot">1</span> Conta</li>
        <li class="pa-step-line"></li>
        <li><span class="pa-dot">2</span> Entrega</li>
        <li class="pa-step-line"></li>
        <li><span class="pa-dot">3</span> Pedido</li>
    </ol>

    <form method="POST" action="{{ route('portal.register.post') }}" class="pa-form">
        @csrf
        <input type="hidden" name="loja" value="{{ $store->id }}">

        <div>
            <label class="pa-label" for="name">Nome completo</label>
            <div class="pa-input-wrap">
                <i class="fas fa-user"></i>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Maria da Silva" class="pa-input @error('name') is-invalid @enderror">
            </div>
            @error('name')<p class="pa-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="pa-label" for="phone">Celular (WhatsApp)</label>
            <div class="pa-input-wrap">
                <i class="fab fa-whatsapp"></i>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="(11) 98765-4321" class="pa-input @error('phone') is-invalid @enderror">
            </div>
            @error('phone')<p class="pa-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="pa-label" for="email">E-mail</label>
            <div class="pa-input-wrap">
                <i class="fas fa-envelope"></i>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autocapitalize="none" placeholder="voce@email.com" class="pa-input @error('email') is-invalid @enderror">
            </div>
            @error('email')<p class="pa-error">{{ $message }}</p>@enderror
            <p class="pa-hint">Você vai usar esse e-mail para entrar.</p>
        </div>

        <div class="pa-grid-2">
            <div>
                <label class="pa-label" for="password">Senha</label>
                <div class="pa-input-wrap">
                    <i class="fas fa-lock"></i>
                    <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="mín. 8" class="pa-input pa-has-eye @error('password') is-invalid @enderror">
                    <button type="button" class="pa-eye" onclick="paTogglePass(this)" aria-label="Mostrar senha"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div>
                <label class="pa-label" for="password_confirmation">Repita a senha</label>
                <div class="pa-input-wrap">
                    <i class="fas fa-lock"></i>
                    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="••••••••" class="pa-input">
                </div>
            </div>
        </div>
        @error('password')<p class="pa-error" style="margin-top:-8px">{{ $message }}</p>@enderror

        <button type="submit" class="pa-btn">Criar conta e continuar <i class="fas fa-arrow-right"></i></button>
    </form>

    <div class="pa-foot">
        <p class="pa-muted">Já tem conta?
            <a href="{{ route('portal.login', session('portal_intended') === 'cart' ? ['redirect' => 'cart'] : []) }}" class="pa-link">Entrar</a>
        </p>
        <a href="{{ route('portal.catalog', ['userId' => $store->id]) }}" class="pa-back"><i class="fas fa-arrow-left"></i> Voltar ao catálogo</a>
    </div>
</x-portal-auth-layout>
