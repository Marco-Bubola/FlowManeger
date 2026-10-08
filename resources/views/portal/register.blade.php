<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Criar conta · {{ $store->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full bg-gradient-to-br from-sky-900 via-indigo-900 to-slate-900 flex items-center justify-center p-4 min-h-screen">

    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-sky-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md py-6">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-white/10 rounded-2xl border border-white/20 mb-3">
                <i class="fas fa-user-plus text-white text-xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">Criar sua conta</h1>
            <p class="text-sky-300 text-sm mt-1">para comprar em <strong class="text-white">{{ $store->name }}</strong></p>
        </div>

        <div class="relative bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl p-6 sm:p-8">
            <ol class="flex items-center gap-2 text-[11px] font-semibold text-sky-200 mb-6">
                <li class="flex items-center gap-1.5"><span class="w-5 h-5 rounded-full bg-sky-400 text-slate-900 flex items-center justify-center text-[10px] font-black">1</span> Conta</li>
                <li class="flex-1 h-px bg-white/20"></li>
                <li class="flex items-center gap-1.5 opacity-70"><span class="w-5 h-5 rounded-full border border-white/40 flex items-center justify-center text-[10px]">2</span> Entrega</li>
                <li class="flex-1 h-px bg-white/20"></li>
                <li class="flex items-center gap-1.5 opacity-70"><span class="w-5 h-5 rounded-full border border-white/40 flex items-center justify-center text-[10px]">3</span> Pedido</li>
            </ol>

            <form method="POST" action="{{ route('portal.register.post') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="loja" value="{{ $store->id }}">

                @php
                    $field = 'w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-transparent transition-all text-sm';
                @endphp

                <div>
                    <label class="block text-sm font-medium text-sky-200 mb-1.5">Nome completo</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-white/40 text-sm"></i>
                        <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Maria da Silva" class="{{ $field }}">
                    </div>
                    @error('name')<p class="mt-1.5 text-xs text-red-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-sky-200 mb-1.5">Celular (WhatsApp)</label>
                    <div class="relative">
                        <i class="fab fa-whatsapp absolute left-3.5 top-1/2 -translate-y-1/2 text-white/40 text-sm"></i>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="(11) 98765-4321" class="{{ $field }}">
                    </div>
                    @error('phone')<p class="mt-1.5 text-xs text-red-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-sky-200 mb-1.5">E-mail</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-white/40 text-sm"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autocapitalize="none" placeholder="voce@email.com" class="{{ $field }}">
                    </div>
                    @error('email')<p class="mt-1.5 text-xs text-red-300">{{ $message }}</p>@enderror
                    <p class="mt-1.5 text-xs text-sky-300/80">Você vai usar esse e-mail para entrar.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-sky-200 mb-1.5">Senha</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-white/40 text-sm"></i>
                            <input type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="mín. 8 caracteres" class="{{ $field }}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-sky-200 mb-1.5">Repita a senha</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-white/40 text-sm"></i>
                            <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="••••••••" class="{{ $field }}">
                        </div>
                    </div>
                </div>
                @error('password')<p class="-mt-2 text-xs text-red-300">{{ $message }}</p>@enderror

                <button type="submit"
                    class="w-full py-3.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-bold rounded-xl shadow-lg transition-all text-sm flex items-center justify-center gap-2">
                    Criar conta e continuar <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-sky-200">
                Já tem conta?
                <a href="{{ route('portal.login', session('portal_intended') === 'cart' ? ['redirect' => 'cart'] : []) }}" class="font-bold text-white hover:underline">Entrar</a>
            </p>
        </div>

        <p class="text-center mt-5">
            <a href="{{ route('portal.catalog', ['userId' => $store->id]) }}" class="text-xs font-semibold text-sky-300 hover:text-white">
                <i class="fas fa-arrow-left mr-1"></i> Voltar ao catálogo
            </a>
        </p>
    </div>
</body>
</html>
