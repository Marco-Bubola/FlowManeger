<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recuperar senha — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top_right,#0f172a_0%,#0f172a_20%,#172554_55%,#0f172a_100%)] p-4 flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-white/20 bg-white/10 text-white shadow-xl">
                <i class="fas fa-key text-2xl"></i>
            </div>
            <h1 class="text-2xl font-black text-white">Recuperar senha</h1>
            <p class="mt-2 text-sm text-sky-200">Informe o login do portal para receber o link de redefinicao no e-mail cadastrado.</p>
        </div>

        <div class="rounded-[1.75rem] border border-white/15 bg-white/10 p-8 shadow-2xl backdrop-blur-xl">
            @if(session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('portal.password.email') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-sky-100">Login do portal</label>
                    <input type="text" name="login" value="{{ old('login') }}" required class="w-full rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder-white/30 focus:border-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-400" placeholder="cli-000123">
                    @error('login')<p class="mt-1.5 text-xs text-rose-200">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.01] hover:from-sky-400 hover:to-indigo-500">
                    <i class="fas fa-paper-plane text-xs"></i>
                    Enviar link de redefinicao
                </button>
            </form>

            <a href="{{ route('portal.login') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-sky-200 hover:text-white transition-colors">
                <i class="fas fa-arrow-left text-xs"></i>
                Voltar ao login
            </a>
        </div>
    </div>
</body>
</html>