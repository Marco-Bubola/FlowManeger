<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nova senha — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 p-4 flex items-center justify-center">
    <div class="w-full max-w-md rounded-[1.75rem] border border-white/15 bg-white/10 p-8 shadow-2xl backdrop-blur-xl">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-white/20 bg-white/10 text-white shadow-xl">
                <i class="fas fa-shield-alt text-2xl"></i>
            </div>
            <h1 class="text-2xl font-black text-white">Definir nova senha</h1>
            <p class="mt-2 text-sm text-sky-200">Crie uma nova senha para voltar a acessar o portal.</p>
            @if(!empty($clientName))
                <p class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-sky-300">Cliente: {{ $clientName }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('portal.password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label class="mb-1.5 block text-sm font-medium text-sky-100">Login do portal</label>
                <input type="text" name="login" value="{{ old('login', $login) }}" readonly required class="w-full rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder-white/30 focus:border-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-400">
                @error('login')<p class="mt-1.5 text-xs text-rose-200">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-sky-100">Nova senha</label>
                <div class="relative">
                    <input type="password" name="password" id="resetPasswordField" required class="w-full rounded-2xl border border-white/20 bg-white/10 px-4 py-3 pr-11 text-sm text-white placeholder-white/30 focus:border-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <button type="button" onclick="togglePasswordField('resetPasswordField', 'resetPasswordEye')" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/80 transition-colors">
                        <i id="resetPasswordEye" class="fas fa-eye text-sm"></i>
                    </button>
                </div>
                @error('password')<p class="mt-1.5 text-xs text-rose-200">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-sky-100">Confirmar senha</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="resetPasswordConfirmationField" required class="w-full rounded-2xl border border-white/20 bg-white/10 px-4 py-3 pr-11 text-sm text-white placeholder-white/30 focus:border-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <button type="button" onclick="togglePasswordField('resetPasswordConfirmationField', 'resetPasswordConfirmationEye')" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/80 transition-colors">
                        <i id="resetPasswordConfirmationEye" class="fas fa-eye text-sm"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.01] hover:from-sky-400 hover:to-indigo-500">
                <i class="fas fa-check text-xs"></i>
                Salvar nova senha
            </button>
        </form>
    </div>
    <script>
    function togglePasswordField(fieldId, iconId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(iconId);
        if (!field || !icon) {
            return;
        }

        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
            return;
        }

        field.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
    </script>
</body>
</html>