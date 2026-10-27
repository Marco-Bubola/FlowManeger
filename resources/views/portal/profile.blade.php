{{-- Meus dados: cadastro, endereço e senha do cliente, no visual do catálogo. --}}
<x-portal-catalog-layout title="Meus dados" :store="$store" :owner-id="$client->user_id" :cart-href="route('portal.quotes.create')">

@push('styles')
    @include('portal.partials.orders-styles')
    <style>
        .pf-pass { position: relative; }
        .pf-pass .pf-input { padding-right: 44px; }
        .pf-eye { position: absolute; right: 4px; top: 4px; width: 36px; height: 36px; border: 0; background: none; color: #888; cursor: pointer; border-radius: 6px; }
        .pf-cep { position: relative; }
        .pf-cep .pf-input { padding-right: 44px; }
        .pf-acc { display: flex; align-items: center; gap: 12px; }
        .pf-avatar { width: 48px; height: 48px; flex-shrink: 0; border-radius: 50%; background: var(--ml-blue-soft); color: var(--ml-blue-dark); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; }
        .pf-google { display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 14px; border-radius: 8px; border: 1px solid var(--ml-line); background: #fff; font-weight: 700; font-size: 13px; color: #333; white-space: nowrap; }
        .pf-tag { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; background: #e6f7ee; color: #00733a; font-size: 12px; font-weight: 700; }
        .pf-save { position: sticky; bottom: 0; z-index: 5; padding: 12px 0 calc(12px + env(safe-area-inset-bottom)); background: linear-gradient(transparent, var(--ml-bg) 30%); }
    </style>
@endpush

<div class="po-page">
    <a href="{{ route('portal.dashboard') }}" class="po-back"><i class="fas fa-chevron-left"></i> Minha conta</a>
    <div class="po-head">
        <div>
            <h1>Meus dados</h1>
            <p>Usados para a loja entregar e emitir seus pedidos.</p>
        </div>
    </div>

    @include('portal.partials.flash')

    @if($requiresOnboarding)
        <div class="pa-alert">
            <i class="fas fa-user-check"></i>
            <span>
                @if($requiresPasswordSetup)
                    Antes de usar o portal, defina sua nova senha e complete seu cadastro. O CEP preenche rua, bairro, cidade e estado sozinho.
                @else
                    Falta completar seu cadastro para liberar o portal por completo.
                @endif
            </span>
        </div>
    @endif

    <section class="po-card">
        <div class="po-card-body pf-acc">
            <div class="pf-avatar">{{ mb_strtoupper(mb_substr($client->name, 0, 1)) }}</div>
            <div style="flex:1;min-width:0">
                <div style="font-size:16px;font-weight:800;color:#222;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $client->name }}</div>
                <div class="po-hint" style="margin:2px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">Login: <strong>{{ $client->portal_login }}</strong>@if($client->email) · {{ $client->email }}@endif</div>
            </div>
        </div>
        <div class="po-card-foot">
            <div style="min-width:0">
                <div style="font-weight:700;font-size:14px">Entrar com Google</div>
                <div class="po-hint" style="margin:0">{{ $client->google_id ? 'Você já pode entrar com a sua conta Google.' : 'Opcional: entre sem digitar a senha.' }}</div>
            </div>
            @if($client->google_id)
                <span class="pf-tag"><i class="fas fa-check"></i> Conectado</span>
            @else
                <a href="{{ route('portal.google.redirect', ['connect' => 1]) }}" class="pf-google">
                    <svg width="16" height="16" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.243 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.959 3.041l5.657-5.657C34.053 6.053 29.277 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.651-.389-3.917Z"/>
                        <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.959 3.041l5.657-5.657C34.053 6.053 29.277 4 24 4c-7.682 0-14.347 4.337-17.694 10.691Z"/>
                        <path fill="#4CAF50" d="M24 44c5.176 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.146 35.091 26.705 36 24 36c-5.222 0-9.618-3.317-11.283-7.946l-6.522 5.025C9.5 39.556 16.227 44 24 44Z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.084 5.571l.003-.002 6.19 5.238C36.971 39.19 44 34 44 24c0-1.341-.138-2.651-.389-3.917Z"/>
                    </svg>
                    Conectar
                </a>
            @endif
        </div>
    </section>

    <form method="POST" action="{{ route('portal.profile.update') }}">
        @csrf
        @method('PATCH')

        <section class="po-card">
            <div class="pa-sec-head"><h2>Contato e documento</h2></div>
            <div class="po-card-body pf-grid">
                <div class="pf-row2">
                    <div class="pf-field">
                        <label class="pf-label" for="phoneField">Telefone (WhatsApp)</label>
                        <input type="tel" name="phone" id="phoneField" value="{{ old('phone', $client->phone) }}" placeholder="(00) 00000-0000" inputmode="tel" autocomplete="tel" class="pf-input">
                        @error('phone')<span class="pf-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="documentField">CPF ou CNPJ</label>
                        <input type="text" name="cpf_cnpj" id="documentField" value="{{ old('cpf_cnpj', $client->cpf_cnpj) }}" placeholder="000.000.000-00" inputmode="numeric" class="pf-input">
                        @error('cpf_cnpj')<span class="pf-err">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="pf-row2">
                    <div class="pf-field">
                        <label class="pf-label" for="birthField">Data de nascimento</label>
                        <input type="date" name="birth_date" id="birthField" value="{{ old('birth_date', $client->birth_date?->format('Y-m-d')) }}" class="pf-input">
                        @error('birth_date')<span class="pf-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="companyField">Empresa <small>(opcional)</small></label>
                        <input type="text" name="company" id="companyField" value="{{ old('company', $client->company) }}" placeholder="Nome da empresa" class="pf-input">
                        @error('company')<span class="pf-err">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="po-card">
            <div class="pa-sec-head"><h2>Endereço de entrega</h2></div>
            <div class="po-card-body pf-grid">
                <div class="pf-row2">
                    <div class="pf-field">
                        <label class="pf-label" for="cepField">CEP</label>
                        <div class="pf-cep">
                            <input id="cepField" type="text" name="cep" value="{{ old('cep', $client->cep) }}" placeholder="00000-000" inputmode="numeric" autocomplete="postal-code" class="pf-input">
                            <button type="button" id="cepLookupBtn" class="pf-eye" aria-label="Buscar endereço pelo CEP"><i class="fas fa-magnifying-glass"></i></button>
                        </div>
                        @error('cep')<span class="pf-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="streetField">Rua</label>
                        <input id="streetField" type="text" name="street" value="{{ old('street', $client->street) }}" placeholder="Rua, avenida..." autocomplete="address-line1" class="pf-input">
                        @error('street')<span class="pf-err">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="pf-row2">
                    <div class="pf-field">
                        <label class="pf-label" for="numberField">Número</label>
                        <input id="numberField" type="text" name="number" value="{{ old('number', $client->number) }}" placeholder="123" class="pf-input">
                        @error('number')<span class="pf-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="complementField">Complemento <small>(opcional)</small></label>
                        <input id="complementField" type="text" name="complement" value="{{ old('complement', $client->complement) }}" placeholder="Apto, bloco, referência" class="pf-input">
                        @error('complement')<span class="pf-err">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="pf-row3">
                    <div class="pf-field">
                        <label class="pf-label" for="neighborhoodField">Bairro</label>
                        <input id="neighborhoodField" type="text" name="neighborhood" value="{{ old('neighborhood', $client->neighborhood) }}" class="pf-input">
                        @error('neighborhood')<span class="pf-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="cityField">Cidade</label>
                        <input id="cityField" type="text" name="city" value="{{ old('city', $client->city) }}" autocomplete="address-level2" class="pf-input">
                        @error('city')<span class="pf-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="stateField">UF</label>
                        <input id="stateField" type="text" name="state" maxlength="2" value="{{ old('state', $client->state) }}" placeholder="SP" style="text-transform:uppercase" class="pf-input">
                        @error('state')<span class="pf-err">{{ $message }}</span>@enderror
                    </div>
                </div>
                @if($client->formattedPortalAddress())
                    <p class="pf-help"><i class="fas fa-location-dot"></i> {{ $client->formattedPortalAddress() }}</p>
                @endif
                <div class="pf-field">
                    <label class="pf-label" for="notesField">Observações <small>(opcional)</small></label>
                    <textarea name="portal_notes" id="notesField" rows="3" placeholder="Melhor horário para entrega, ponto de referência..." class="pf-input">{{ old('portal_notes', $client->portal_notes) }}</textarea>
                    @error('portal_notes')<span class="pf-err">{{ $message }}</span>@enderror
                </div>
            </div>
        </section>

        <section class="po-card">
            <div class="pa-sec-head">
                <h2>{{ $requiresPasswordSetup ? 'Definir nova senha' : 'Alterar senha' }}</h2>
                <span class="po-hint" style="margin:0">{{ $requiresPasswordSetup ? 'obrigatório' : 'deixe em branco para manter' }}</span>
            </div>
            <div class="po-card-body pf-grid">
                @unless($requiresPasswordSetup)
                <div class="pf-field">
                    <label class="pf-label" for="currentPassword">Senha atual</label>
                    <input type="password" name="current_password" id="currentPassword" placeholder="••••••••" autocomplete="current-password" class="pf-input">
                    @error('current_password')<span class="pf-err">{{ $message }}</span>@enderror
                </div>
                @endunless
                <div class="pf-row2">
                    <div class="pf-field">
                        <label class="pf-label" for="profileNewPassword">Nova senha</label>
                        <div class="pf-pass">
                            <input type="password" name="new_password" id="profileNewPassword" placeholder="mínimo 8 caracteres" autocomplete="new-password" class="pf-input">
                            <button type="button" class="pf-eye" onclick="togglePortalPassword('profileNewPassword', 'profileNewPasswordEye')" aria-label="Mostrar senha"><i id="profileNewPasswordEye" class="fas fa-eye"></i></button>
                        </div>
                        @error('new_password')<span class="pf-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="profileNewPasswordConfirmation">Confirmar nova senha</label>
                        <div class="pf-pass">
                            <input type="password" name="new_password_confirmation" id="profileNewPasswordConfirmation" placeholder="repita a senha" autocomplete="new-password" class="pf-input">
                            <button type="button" class="pf-eye" onclick="togglePortalPassword('profileNewPasswordConfirmation', 'profileNewPasswordConfirmationEye')" aria-label="Mostrar senha"><i id="profileNewPasswordConfirmationEye" class="fas fa-eye"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="pf-save">
            <button type="submit" class="po-btn po-btn-primary po-btn-block" style="height:48px;font-size:15px"><i class="fas fa-check"></i> Salvar alterações</button>
        </div>
    </form>

    {{-- Pontos de fidelidade (quando a loja ligou) --}}
    @php $R = \App\Services\Portal\Rewards::class; $rwOwner = (int) $client->user_id; @endphp
    @if($R::on($rwOwner))
        @php $ptsBal = $R::balance($client); $rwc = $R::for($rwOwner); $ptsHist = $R::history($client, 10); @endphp
        <section class="po-card" id="pontos" data-testid="points-card" style="margin-top:14px">
            <div class="pa-sec-head"><h2>Meus pontos</h2></div>
            <div class="po-card-body pf-pts">
                <div class="pf-pts-top">
                    <span class="pf-pts-ico"><i class="fas fa-star"></i></span>
                    <div style="min-width:0">
                        <b class="pf-pts-n" data-testid="points-balance">{{ number_format($ptsBal, 0, ',', '.') }} pontos</b>
                        <small>Valem R$ {{ number_format($R::money($rwOwner, $ptsBal), 2, ',', '.') }} de desconto{{ $rwc['min_use'] > 1 ? ' (a partir de ' . number_format($rwc['min_use'], 0, ',', '.') . ' pontos)' : '' }}.</small>
                    </div>
                </div>
                <p class="po-hint" style="margin:10px 0 0">Ganhe {{ rtrim(rtrim(number_format($rwc['earn'], 2, ',', ''), '0'), ',') }} {{ $rwc['earn'] == 1 ? 'ponto' : 'pontos' }} a cada R$ 1,00 comprado. Cada 100 pontos valem R$ {{ number_format($rwc['worth'], 2, ',', '.') }}. Use no carrinho, marcando “Usar meus pontos”.@if($rwc['expire']) Os pontos vencem depois de {{ mb_strtolower($R::EXPIRE[$rwc['expire']]) }}.@endif</p>
                @if($ptsHist->isNotEmpty())
                    <ul class="pf-pts-list">
                        @foreach($ptsHist as $h)
                            @php [$kn, $ki] = $R::KINDS[$h->kind] ?? [$h->kind, 'bi-dot']; @endphp
                            <li><span>{{ $kn }}<small>{{ $h->created_at->format('d/m/Y') }}@if($h->note) · {{ $h->note }}@endif</small></span><b class="{{ $h->points >= 0 ? 'pos' : 'neg' }}">{{ $h->points >= 0 ? '+' : '' }}{{ number_format($h->points, 0, ',', '.') }}</b></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
        <style>
            .pf-pts-top { display: flex; align-items: center; gap: 12px; }
            .pf-pts-ico { flex-shrink: 0; width: 46px; height: 46px; border-radius: 14px; background: #fff6d6; color: #e0a000; display: flex; align-items: center; justify-content: center; font-size: 20px; }
            .pf-pts-n { display: block; font-size: 20px; font-weight: 900; color: var(--ml-text); }
            .pf-pts-top small { display: block; font-size: 13px; color: var(--ml-muted); }
            .pf-pts-list { list-style: none; margin: 12px 0 0; padding: 0; border-top: 1px solid var(--ml-line); }
            .pf-pts-list li { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 9px 0; border-bottom: 1px solid var(--ml-line); font-size: 13px; color: var(--ml-text); }
            .pf-pts-list li span { min-width: 0; }
            .pf-pts-list small { display: block; font-size: 11.5px; color: var(--ml-muted); }
            .pf-pts-list b.pos { color: var(--ml-green); }
            .pf-pts-list b.neg { color: #dc2626; }
        </style>
    @endif

    {{-- LGPD: ver os dados e encerrar a conta do site --}}
    <section class="po-card" data-testid="privacy-card" x-data="{ closing: false }" style="margin-top:14px">
        <div class="pa-sec-head"><h2>Seus dados e privacidade</h2></div>
        <div class="po-card-body" style="display:grid;gap:10px">
            <p style="margin:0;font-size:13px;color:var(--ml-muted,#777);line-height:1.5">A loja usa os seus dados só para os pedidos e a entrega. Veja como em <a href="{{ route('portal.store-page', ['userId' => $client->user_id, 'page' => 'privacidade']) }}" style="color:var(--ml-blue)">Política de privacidade</a>.</p>
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                <a href="{{ route('portal.profile.data') }}" class="po-btn po-btn-soft" data-testid="privacy-download"><i class="fas fa-download"></i> Baixar meus dados</a>
                <button type="button" class="po-btn po-btn-ghost" style="color:#dc2626" @click="closing = !closing" data-testid="privacy-close-open"><i class="fas fa-user-xmark"></i> Encerrar minha conta</button>
            </div>
            <form method="POST" action="{{ route('portal.profile.close') }}" x-show="closing" x-cloak style="display:grid;gap:10px;padding:12px;border-radius:12px;background:rgba(220,38,38,.06)">
                @csrf
                <p style="margin:0;font-size:13px;line-height:1.5">Você não vai mais conseguir entrar no site com esta conta, e a loja recebe o pedido para apagar os seus dados pessoais. Os registros das compras ficam com a loja pelo tempo que a lei fiscal pede.</p>
                <label style="display:flex;gap:8px;align-items:flex-start;font-size:13px"><input type="checkbox" name="confirmar" value="1" required> Quero encerrar a minha conta</label>
                <button type="submit" class="po-btn" style="background:#dc2626;color:#fff;justify-self:start" data-testid="privacy-close"><i class="fas fa-user-xmark"></i> Encerrar conta</button>
            </form>
        </div>
    </section>
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

</x-portal-catalog-layout>
