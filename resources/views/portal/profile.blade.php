<x-portal-layout title="Meu Perfil">

<div class="max-w-4xl mx-auto">

    {{-- ── HEADER glassmorphism ── --}}
    <div class="portal-page-header">
        <div class="pph-blur-tr"></div>
        <div class="pph-blur-bl"></div>
        <div class="pph-shine"></div>

        <div class="pph-row1">
            <div class="pph-icon" style="background:linear-gradient(135deg,#8b5cf6,#6366f1);">
                <i class="fas fa-circle-user"></i>
            </div>
            <div class="pph-title-wrap">
                <div class="pph-breadcrumb">
                    <a href="{{ route('portal.dashboard') }}"><i class="fas fa-house-chimney text-[8px]"></i> Início</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span>Meu Perfil</span>
                </div>
                <h1 class="pph-title">{{ $client->name }}</h1>
            </div>
            <div class="hidden sm:flex flex-wrap items-center gap-2 ml-auto">
                <span class="pph-badge info">
                    <i class="fas fa-user text-[8px]"></i>
                    {{ $client->portal_login }}
                </span>
                @if($client->email)
                <span class="pph-badge">
                    <i class="fas fa-envelope text-[8px]"></i>
                    {{ $client->email }}
                </span>
                @endif
                @if($client->google_id)
                <span class="pph-badge success">
                    <i class="fab fa-google text-[8px]"></i>
                    Google conectado
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-sky-100 dark:border-sky-800/50 bg-white dark:bg-slate-800 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-sky-500">Login do portal</p>
            <p class="mt-2 text-base font-black text-slate-900 dark:text-slate-100">{{ $client->portal_login }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Esse login foi criado com base no nome do cliente e nao depende do e-mail.</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 dark:border-emerald-800/50 bg-white dark:bg-slate-800 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-500">Google</p>
                    <p class="mt-2 text-sm font-bold text-slate-900 dark:text-slate-100">{{ $client->google_id ? 'Conta conectada' : 'Conexao opcional' }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $client->google_id ? 'Voce pode entrar com Google a partir de agora.' : 'Se quiser, conecte sua conta Google depois do primeiro acesso.' }}</p>
                </div>
                @if($client->google_id)
                    <span class="inline-flex items-center rounded-full border border-emerald-200 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-400">Conectado</span>
                @else
                    <a href="{{ route('portal.google.redirect', ['connect' => 1]) }}" class="inline-flex items-center gap-2 rounded-xl bg-white dark:bg-slate-700 px-4 py-2 text-xs font-bold text-slate-800 dark:text-slate-200 shadow-sm ring-1 ring-slate-200 dark:ring-slate-600 transition hover:bg-slate-50 dark:hover:bg-slate-600">
                        <svg width="16" height="16" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                            <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.243 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.959 3.041l5.657-5.657C34.053 6.053 29.277 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.651-.389-3.917Z"/>
                            <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.959 3.041l5.657-5.657C34.053 6.053 29.277 4 24 4c-7.682 0-14.347 4.337-17.694 10.691Z"/>
                            <path fill="#4CAF50" d="M24 44c5.176 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.146 35.091 26.705 36 24 36c-5.222 0-9.618-3.317-11.283-7.946l-6.522 5.025C9.5 39.556 16.227 44 24 44Z"/>
                            <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.084 5.571l.003-.002 6.19 5.238C36.971 39.19 44 34 44 24c0-1.341-.138-2.651-.389-3.917Z"/>
                        </svg>
                        Conectar Google
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if($requiresOnboarding)
    <div class="mb-4 rounded-2xl border border-sky-200 dark:border-sky-800/50 bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-sky-900/20 dark:to-indigo-900/20 p-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-sky-900 dark:text-sky-300">Finalização do acesso</p>
                <p class="text-sm text-sky-700 dark:text-sky-400 mt-1">
                    @if($requiresPasswordSetup)
                        Antes de usar o portal, defina sua nova senha e complete seu cadastro. O CEP preenche rua, bairro, cidade e estado automaticamente.
                    @elseif($requiresProfileCompletion)
                        Sua senha já foi definida. Agora falta apenas completar seu cadastro para liberar o portal por completo.
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('portal.profile.update') }}" class="space-y-5">
        @csrf
        @method('PATCH')

        {{-- Basic info (read-only — filled by admin) --}}
        <div class="portal-card overflow-hidden">
            <div class="px-4 py-3.5 border-b border-gray-50 dark:border-slate-700 bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-sky-900/20 dark:to-indigo-900/20">
                <h2 class="font-black text-sm text-gray-900 dark:text-slate-200 flex items-center gap-2">
                    <i class="fas fa-user text-sky-500"></i>
                    Dados Cadastrais
                </h2>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Informações preenchidas pelo vendedor</p>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Nome</label>
                    <p class="px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-100 dark:border-slate-600 rounded-xl text-sm text-gray-700 dark:text-slate-300 font-medium">{{ $client->name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">E-mail</label>
                    <p class="px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-100 dark:border-slate-600 rounded-xl text-sm text-gray-700 dark:text-slate-300 font-medium">{{ $client->email ?: 'Ainda nao conectado' }}</p>
                </div>
            </div>
        </div>

        {{-- Editable info --}}
        <div class="portal-card overflow-hidden">
            <div class="px-4 py-3.5 border-b border-gray-50 dark:border-slate-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-emerald-900/20 dark:to-green-900/20">
                <h2 class="font-black text-sm text-gray-900 dark:text-slate-200 flex items-center gap-2">
                    <i class="fas fa-edit text-green-500"></i>
                    Seus Dados <span class="text-green-600 dark:text-emerald-400 text-xs font-normal">(você pode editar)</span>
                </h2>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                        id="phoneField"
                        placeholder="(00) 00000-0000"
                        class="portal-input @error('phone') border-red-300 dark:border-red-600 @enderror">
                    @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">CPF / CNPJ</label>
                    <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj', $client->cpf_cnpj) }}"
                        id="documentField"
                        placeholder="000.000.000-00"
                        class="portal-input @error('cpf_cnpj') border-red-300 dark:border-red-600 @enderror">
                    @error('cpf_cnpj') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Data de Nascimento</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $client->birth_date?->format('Y-m-d')) }}"
                        class="portal-input @error('birth_date') border-red-300 dark:border-red-600 @enderror">
                    @error('birth_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Empresa</label>
                    <input type="text" name="company" value="{{ old('company', $client->company) }}"
                        placeholder="Nome da empresa (opcional)"
                        class="portal-input @error('company') border-red-300 dark:border-red-600 @enderror">
                    @error('company') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">CEP</label>
                    <div class="relative">
                        <input id="cepField" type="text" name="cep" value="{{ old('cep', $client->cep) }}"
                            placeholder="00000-000"
                            class="portal-input pr-10 @error('cep') border-red-300 dark:border-red-600 @enderror">
                        <button type="button" id="cepLookupBtn" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/50 transition-colors">
                            <i class="fas fa-search-location text-xs"></i>
                        </button>
                    </div>
                    @error('cep') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Rua</label>
                    <input id="streetField" type="text" name="street" value="{{ old('street', $client->street) }}"
                        placeholder="Rua / avenida"
                        class="portal-input @error('street') border-red-300 dark:border-red-600 @enderror">
                    @error('street') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Número</label>
                    <input type="text" name="number" value="{{ old('number', $client->number) }}"
                        placeholder="123"
                        class="portal-input @error('number') border-red-300 dark:border-red-600 @enderror">
                    @error('number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Complemento</label>
                    <input type="text" name="complement" value="{{ old('complement', $client->complement) }}"
                        placeholder="Apto, bloco, sala..."
                        class="portal-input @error('complement') border-red-300 dark:border-red-600 @enderror">
                    @error('complement') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Bairro</label>
                    <input id="neighborhoodField" type="text" name="neighborhood" value="{{ old('neighborhood', $client->neighborhood) }}"
                        placeholder="Bairro"
                        class="portal-input @error('neighborhood') border-red-300 dark:border-red-600 @enderror">
                    @error('neighborhood') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Cidade</label>
                    <input id="cityField" type="text" name="city" value="{{ old('city', $client->city) }}"
                        placeholder="Cidade"
                        class="portal-input @error('city') border-red-300 dark:border-red-600 @enderror">
                    @error('city') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">UF</label>
                    <input id="stateField" type="text" name="state" maxlength="2" value="{{ old('state', $client->state) }}"
                        placeholder="SP"
                        class="portal-input uppercase @error('state') border-red-300 dark:border-red-600 @enderror">
                    @error('state') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Endereço completo gerado</label>
                    <p class="px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-100 dark:border-slate-600 rounded-xl text-sm text-gray-600 dark:text-slate-400">{{ $client->formattedPortalAddress() ?: 'O endereço será montado automaticamente com os campos acima.' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Observações pessoais</label>
                    <textarea name="portal_notes" rows="3"
                        placeholder="Preferências de contato, horários, informações adicionais..."
                        class="portal-input resize-none @error('portal_notes') border-red-300 dark:border-red-600 @enderror">{{ old('portal_notes', $client->portal_notes) }}</textarea>
                    @error('portal_notes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="portal-card overflow-hidden">
            <div class="px-4 py-3.5 border-b border-gray-50 dark:border-slate-700 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20">
                <h2 class="font-black text-sm text-gray-900 dark:text-slate-200 flex items-center gap-2">
                    <i class="fas fa-lock text-amber-500"></i>
                    {{ $requiresPasswordSetup ? 'Definir Nova Senha' : 'Alterar Senha' }} <span class="text-gray-400 dark:text-slate-500 text-xs font-normal">{{ $requiresPasswordSetup ? '(obrigatório no primeiro acesso)' : '(deixe em branco para manter a atual)' }}</span>
                </h2>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div @class(['hidden' => $requiresPasswordSetup])>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Senha Atual</label>
                    <input type="password" name="current_password"
                        placeholder="••••••••"
                        class="portal-input @error('current_password') border-red-300 dark:border-red-600 @enderror">
                    @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Nova Senha</label>
                    <div class="relative">
                        <input type="password" name="new_password" id="profileNewPassword"
                            placeholder="mínimo 8 caracteres"
                            class="portal-input pr-11 @error('new_password') border-red-300 dark:border-red-600 @enderror">
                        <button type="button" onclick="togglePortalPassword('profileNewPassword', 'profileNewPasswordEye')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                            <i id="profileNewPasswordEye" class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                    @error('new_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1.5">Confirmar Nova Senha</label>
                    <div class="relative">
                        <input type="password" name="new_password_confirmation" id="profileNewPasswordConfirmation"
                            placeholder="repita a senha"
                            class="portal-input pr-11">
                        <button type="button" onclick="togglePortalPassword('profileNewPasswordConfirmation', 'profileNewPasswordConfirmationEye')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                            <i id="profileNewPasswordConfirmationEye" class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="px-7 py-2.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-black text-xs rounded-xl shadow-lg hover:shadow-sky-500/30 transition-all hover:scale-105 flex items-center gap-2">
                <i class="fas fa-save text-[10px]"></i>
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
const cepField = document.getElementById('cepField');
const cepLookupBtn = document.getElementById('cepLookupBtn');
const phoneField = document.getElementById('phoneField');
const documentField = document.getElementById('documentField');

function onlyDigits(value) {
    return value.replace(/\D/g, '');
}

function formatPhone(value) {
    const digits = onlyDigits(value).slice(0, 11);
    if (digits.length <= 10) {
        return digits.replace(/^(\d{0,2})(\d{0,4})(\d{0,4}).*/, (match, d1, d2, d3) => {
            let output = '';
            if (d1) output += `(${d1}`;
            if (d1.length === 2) output += ') ';
            if (d2) output += d2;
            if (d3) output += '-' + d3;
            return output;
        });
    }
    return digits.replace(/^(\d{0,2})(\d{0,5})(\d{0,4}).*/, (match, d1, d2, d3) => {
        let output = '';
        if (d1) output += `(${d1}`;
        if (d1.length === 2) output += ') ';
        if (d2) output += d2;
        if (d3) output += '-' + d3;
        return output;
    });
}

function formatDocument(value) {
    const digits = onlyDigits(value).slice(0, 14);
    if (digits.length <= 11) {
        return digits
            .replace(/^(\d{3})(\d)/, '$1.$2')
            .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1-$2');
    }

    return digits
        .replace(/^(\d{2})(\d)/, '$1.$2')
        .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1/$2')
        .replace(/(\d{4})(\d)/, '$1-$2');
}

function formatCep(value) {
    const digits = onlyDigits(value).slice(0, 8);
    return digits.replace(/^(\d{5})(\d)/, '$1-$2');
}

async function lookupCep() {
    if (!cepField) {
        return;
    }

    const cep = cepField.value.replace(/\D/g, '');
    if (cep.length !== 8) {
        return;
    }

    cepLookupBtn?.setAttribute('disabled', 'disabled');

    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await response.json();

        if (data.erro) {
            return;
        }

        const streetField = document.getElementById('streetField');
        const neighborhoodField = document.getElementById('neighborhoodField');
        const cityField = document.getElementById('cityField');
        const stateField = document.getElementById('stateField');

        if (streetField && !streetField.value) streetField.value = data.logradouro ?? '';
        if (neighborhoodField && !neighborhoodField.value) neighborhoodField.value = data.bairro ?? '';
        if (cityField && !cityField.value) cityField.value = data.localidade ?? '';
        if (stateField && !stateField.value) stateField.value = data.uf ?? '';
    } catch (error) {
        console.error('Falha ao consultar CEP', error);
    } finally {
        cepLookupBtn?.removeAttribute('disabled');
    }
}

cepLookupBtn?.addEventListener('click', lookupCep);
cepField?.addEventListener('blur', lookupCep);
cepField?.addEventListener('input', (event) => {
    event.target.value = formatCep(event.target.value);
});
phoneField?.addEventListener('input', (event) => {
    event.target.value = formatPhone(event.target.value);
});
documentField?.addEventListener('input', (event) => {
    event.target.value = formatDocument(event.target.value);
});

function togglePortalPassword(fieldId, iconId) {
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
@endpush

</x-portal-layout>
