<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Acesso ao Portal — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full bg-gradient-to-br from-sky-900 via-indigo-900 to-slate-900 flex items-center justify-center p-4 min-h-screen">

    {{-- Background pattern --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-sky-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-violet-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/10 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 mb-4">
                <i class="fas fa-store text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">{{ config('app.name') }}</h1>
            <p class="text-sky-300 text-sm mt-1">Portal exclusivo para clientes</p>
        </div>

        {{-- Card --}}
        <div class="absolute -inset-2 bg-gradient-to-r from-sky-400/20 via-cyan-300/10 to-indigo-400/20 blur-2xl rounded-[2rem]"></div>
        <div class="relative bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl p-8 overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl from-white/10 to-transparent rounded-full translate-x-10 -translate-y-10"></div>
            <h2 class="text-xl font-bold text-white mb-1">Bem-vindo de volta!</h2>
            <p class="text-sky-200 text-sm mb-6">Acesse com o login exclusivo baseado no nome do cliente</p>

            <div class="grid grid-cols-3 gap-2 mb-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3 text-center">
                    <p class="text-[10px] uppercase tracking-[0.18em] text-sky-300">Compras</p>
                    <p class="mt-1 text-sm font-bold text-white">Historico</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3 text-center">
                    <p class="text-[10px] uppercase tracking-[0.18em] text-sky-300">Produtos</p>
                    <p class="mt-1 text-sm font-bold text-white">Estoque</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3 text-center">
                    <p class="text-[10px] uppercase tracking-[0.18em] text-sky-300">Orcamentos</p>
                    <p class="mt-1 text-sm font-bold text-white">Pedidos</p>
                </div>
            </div>

            @if(session('success'))
                <div class="flex items-center gap-3 p-3 bg-green-500/20 border border-green-400/30 text-green-200 rounded-xl text-sm mb-4">
                    <i class="fas fa-check-circle flex-shrink-0"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 p-3 bg-red-500/20 border border-red-400/30 text-red-200 rounded-xl text-sm mb-4">
                    <i class="fas fa-exclamation-circle flex-shrink-0"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('portal.login.post') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-sky-200 mb-1.5">Login do portal</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/40">
                            <i class="fas fa-id-badge text-sm"></i>
                        </span>
                        <input type="text" name="login" value="{{ old('login') }}" required autofocus
                            placeholder="maria-silva"
                            class="w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-transparent transition-all text-sm @error('login') border-red-400/50 @enderror">
                    </div>
                    @error('login')
                        <p class="mt-1.5 text-xs text-red-300">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-sky-300/80">Esse login nao depende de e-mail e normalmente usa o nome do cliente de forma amigavel.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-sky-200 mb-1.5">Senha</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/40">
                            <i class="fas fa-lock text-sm"></i>
                        </span>
                        <input type="password" name="password" id="passwordField" required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-10 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-transparent transition-all text-sm @error('password') border-red-400/50 @enderror">
                        <button type="button" onclick="togglePass()" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/80 transition-colors">
                            <i id="eyeIcon" class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-white/30 bg-white/10 text-sky-500 focus:ring-sky-400 focus:ring-offset-0">
                        <span class="text-sm text-sky-200">Lembrar-me</span>
                    </label>
                    <a href="{{ route('portal.password.request') }}" class="text-xs font-semibold text-sky-200 hover:text-white transition-colors">
                        Esqueci minha senha
                    </a>
                </div>

                <button type="submit"
                    class="w-full py-3.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-bold rounded-xl shadow-lg hover:shadow-sky-500/30 transition-all duration-300 transform hover:scale-[1.02] text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar no Portal
                </button>
            </form>

            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-white/10"></div></div>
                <div class="relative flex justify-center text-[11px] uppercase tracking-[0.24em] text-sky-200">
                    <span class="bg-transparent px-3">ou</span>
                </div>
            </div>

            <a href="{{ route('portal.google.redirect') }}"
               class="w-full inline-flex items-center justify-center gap-3 py-3.5 bg-white text-slate-800 font-bold rounded-xl shadow-lg transition-all duration-300 transform hover:scale-[1.02] text-sm">
                <svg width="18" height="18" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                    <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.243 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.959 3.041l5.657-5.657C34.053 6.053 29.277 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.651-.389-3.917Z"/>
                    <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.959 3.041l5.657-5.657C34.053 6.053 29.277 4 24 4c-7.682 0-14.347 4.337-17.694 10.691Z"/>
                    <path fill="#4CAF50" d="M24 44c5.176 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.146 35.091 26.705 36 24 36c-5.222 0-9.618-3.317-11.283-7.946l-6.522 5.025C9.5 39.556 16.227 44 24 44Z"/>
                    <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.084 5.571l.003-.002 6.19 5.238C36.971 39.19 44 34 44 24c0-1.341-.138-2.651-.389-3.917Z"/>
                </svg>
                Entrar com Google
            </a>

            <div class="mt-6 pt-6 border-t border-white/10 text-center">
                <p class="text-sky-300 text-xs">
                    Não tem acesso? Entre em contato com seu vendedor.
                </p>
            </div>
        </div>

        <p class="text-center text-white/30 text-xs mt-6">
            {{ config('app.name') }} · Portal Seguro · {{ date('Y') }}
        </p>
    </div>

    <script>
    function togglePass() {
        const f = document.getElementById('passwordField');
        const i = document.getElementById('eyeIcon');
        if (f.type === 'password') {
            f.type = 'text';
            i.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            f.type = 'password';
            i.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    </script>
</body>
</html>
