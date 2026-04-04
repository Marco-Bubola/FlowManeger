<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="settings-system-page w-full mobile-393-base"
    x-data="{
        language: 'pt_BR',
        timezone: 'America/Sao_Paulo',
        date_format: 'dd/mm/yyyy',
        time_format: '24h',
        first_dow: 'sun',
        currency: 'BRL',
        number_format: 'br',
        paper_size: 'A4',
        saved: false,
        saveAll() {
            localStorage.setItem('flowmanager:system', JSON.stringify({
                language: this.language,
                timezone: this.timezone,
                date_format: this.date_format,
                time_format: this.time_format,
                first_dow: this.first_dow,
                currency: this.currency,
                number_format: this.number_format,
                paper_size: this.paper_size,
            }));
            this.saved = true;
            setTimeout(() => this.saved = false, 2000);
        },
        get datePreview() {
            const d = new Date();
            const dd = String(d.getDate()).padStart(2,'0');
            const mm = String(d.getMonth()+1).padStart(2,'0');
            const yyyy = d.getFullYear();
            if (this.date_format === 'dd/mm/yyyy') return dd+'/'+mm+'/'+yyyy;
            if (this.date_format === 'mm/dd/yyyy') return mm+'/'+dd+'/'+yyyy;
            return yyyy+'-'+mm+'-'+dd;
        },
        get timePreview() {
            const d = new Date();
            if (this.time_format === '24h') {
                return String(d.getHours()).padStart(2,'0')+':'+String(d.getMinutes()).padStart(2,'0');
            }
            let h = d.getHours(); const m = String(d.getMinutes()).padStart(2,'0');
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            return h+':'+m+' '+ampm;
        },
        get currencyPreview() {
            const val = 1234567.89;
            if (this.currency === 'BRL') return 'R$ ' + (this.number_format === 'br' ? '1.234.567,89' : '1,234,567.89');
            if (this.currency === 'USD') return '$ ' + (this.number_format === 'br' ? '1.234.567,89' : '1,234,567.89');
            return '€ ' + (this.number_format === 'br' ? '1.234.567,89' : '1,234,567.89');
        }
    }"
    x-init="
        try {
            const s = JSON.parse(localStorage.getItem('flowmanager:system') || 'null');
            if (s) {
                if (s.language) language = s.language;
                if (s.timezone) timezone = s.timezone;
                if (s.date_format) date_format = s.date_format;
                if (s.time_format) time_format = s.time_format;
                if (s.first_dow) first_dow = s.first_dow;
                if (s.currency) currency = s.currency;
                if (s.number_format) number_format = s.number_format;
                if (s.paper_size) paper_size = s.paper_size;
            }
        } catch(e){}
    ">

    <style>
        @media (max-width: 767px) {
            .settings-system-page { padding-bottom: calc(82px + env(safe-area-inset-bottom)); padding-inline: 0.75rem; }
            .settings-sys-grid { grid-template-columns: 1fr !important; }
            .settings-sys-preview { flex-direction: column; }
        }
        @media (max-width: 430px) {
            .settings-system-page { padding-inline: 0.5rem; }
        }
    </style>

    {{-- Toast salvo --}}
    <div x-show="saved" x-transition.opacity
         style="display:none;position:fixed;top:1.25rem;right:1.25rem;z-index:9999;padding:0.65rem 1.1rem;border-radius:0.75rem;background:var(--s-accent);color:#fff;font-size:0.82rem;font-weight:700;box-shadow:0 4px 20px rgba(0,0,0,0.18);display:flex;align-items:center;gap:0.4rem">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:0.9rem;height:0.9rem"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        Configurações salvas!
    </div>

    @include('partials.settings-heading')

    <x-settings.layout :heading="''">

        {{-- ── CARD: Preview dinamico ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(16,185,129,0.1);color:#10b981">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Pré-visualização dos formatos</p>
                        <p class="settings-card-desc">Veja como data, hora e valores serão exibidos</p>
                    </div>
                </div>
            </div>

            <div class="settings-sys-preview" style="display:flex;gap:0.75rem;flex-wrap:wrap;padding:0 1.25rem 1.25rem">
                <div style="flex:1;min-width:140px;padding:0.85rem 1rem;border-radius:0.7rem;background:rgba(var(--s-accent-rgb),0.05);border:1.5px solid rgba(var(--s-accent-rgb),0.1)">
                    <p style="font-size:0.7rem;color:#64748b;margin-bottom:0.2rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:600">Data</p>
                    <p style="font-size:1.05rem;font-weight:700;color:var(--s-accent)" x-text="datePreview"></p>
                </div>
                <div style="flex:1;min-width:140px;padding:0.85rem 1rem;border-radius:0.7rem;background:rgba(99,102,241,0.05);border:1.5px solid rgba(99,102,241,0.1)">
                    <p style="font-size:0.7rem;color:#64748b;margin-bottom:0.2rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:600">Hora</p>
                    <p style="font-size:1.05rem;font-weight:700;color:#6366f1" x-text="timePreview"></p>
                </div>
                <div style="flex:1;min-width:140px;padding:0.85rem 1rem;border-radius:0.7rem;background:rgba(16,185,129,0.05);border:1.5px solid rgba(16,185,129,0.1)">
                    <p style="font-size:0.7rem;color:#64748b;margin-bottom:0.2rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:600">Valor</p>
                    <p style="font-size:1.05rem;font-weight:700;color:#10b981" x-text="currencyPreview"></p>
                </div>
            </div>
        </div>

        {{-- ── CARD: Idioma e região ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Idioma e região</p>
                        <p class="settings-card-desc">Idioma do sistema e fuso horário</p>
                    </div>
                </div>
            </div>

            <div class="settings-sys-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;padding:0 1.25rem 1.25rem">
                <div>
                    <label class="settings-field-label">Idioma do sistema</label>
                    <select x-model="language" @change="saveAll()" class="settings-select">
                        <option value="pt_BR">🇧🇷 Português (Brasil)</option>
                        <option value="en_US">🇺🇸 English (US)</option>
                        <option value="es">🇪🇸 Español</option>
                        <option value="fr">🇫🇷 Français</option>
                        <option value="de">🇩🇪 Deutsch</option>
                    </select>
                </div>
                <div>
                    <label class="settings-field-label">Fuso horário</label>
                    <select x-model="timezone" @change="saveAll()" class="settings-select">
                        <optgroup label="Brasil">
                            <option value="America/Sao_Paulo">São Paulo (GMT-3)</option>
                            <option value="America/Manaus">Manaus (GMT-4)</option>
                            <option value="America/Fortaleza">Fortaleza (GMT-3)</option>
                            <option value="America/Cuiaba">Cuiabá (GMT-4)</option>
                            <option value="America/Belem">Belém (GMT-3)</option>
                            <option value="America/Porto_Velho">Porto Velho (GMT-4)</option>
                            <option value="America/Boa_Vista">Boa Vista (GMT-4)</option>
                            <option value="America/Noronha">Fernando de Noronha (GMT-2)</option>
                        </optgroup>
                        <optgroup label="América">
                            <option value="America/New_York">New York (GMT-5)</option>
                            <option value="America/Los_Angeles">Los Angeles (GMT-8)</option>
                            <option value="America/Chicago">Chicago (GMT-6)</option>
                            <option value="America/Buenos_Aires">Buenos Aires (GMT-3)</option>
                            <option value="America/Santiago">Santiago (GMT-3)</option>
                        </optgroup>
                        <optgroup label="Europa">
                            <option value="Europe/Lisbon">Lisboa (GMT+0)</option>
                            <option value="Europe/London">Londres (GMT+0)</option>
                            <option value="Europe/Madrid">Madrid (GMT+1)</option>
                            <option value="Europe/Paris">Paris (GMT+1)</option>
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>

        {{-- ── CARD: Formatos ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Formatos de data e hora</p>
                        <p class="settings-card-desc">Como datas e horários aparecem no sistema</p>
                    </div>
                </div>
            </div>

            <div style="padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:1.25rem">
                <div>
                    <label class="settings-field-label">Formato de data</label>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.4rem">
                        @foreach([['dd/mm/yyyy','DD/MM/AAAA (Brasil)'],['mm/dd/yyyy','MM/DD/AAAA (EUA)'],['yyyy-mm-dd','AAAA-MM-DD (ISO)']] as [$v,$l])
                        <button type="button"
                            :class="date_format === '{{ $v }}' ? 'settings-format-btn sf-on' : 'settings-format-btn'"
                            @click="date_format = '{{ $v }}'; saveAll()">{{ $l }}</button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="settings-field-label">Formato de hora</label>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.4rem">
                        <button type="button"
                            :class="time_format === '24h' ? 'settings-format-btn sf-on' : 'settings-format-btn'"
                            @click="time_format = '24h'; saveAll()">24 horas (14:30)</button>
                        <button type="button"
                            :class="time_format === '12h' ? 'settings-format-btn sf-on' : 'settings-format-btn'"
                            @click="time_format = '12h'; saveAll()">12 horas (2:30 PM)</button>
                    </div>
                </div>

                <div>
                    <label class="settings-field-label">Primeiro dia da semana</label>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.4rem">
                        <button type="button"
                            :class="first_dow === 'sun' ? 'settings-format-btn sf-on' : 'settings-format-btn'"
                            @click="first_dow = 'sun'; saveAll()">Domingo</button>
                        <button type="button"
                            :class="first_dow === 'mon' ? 'settings-format-btn sf-on' : 'settings-format-btn'"
                            @click="first_dow = 'mon'; saveAll()">Segunda-feira</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── CARD: Moeda e números ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Moeda e formato numérico</p>
                        <p class="settings-card-desc">Símbolo de moeda e separadores de milhar/decimal</p>
                    </div>
                </div>
            </div>

            <div style="padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:1.25rem">
                <div>
                    <label class="settings-field-label">Moeda principal</label>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.4rem">
                        @foreach([['BRL','🇧🇷 Real (R$)'],['USD','🇺🇸 Dólar ($)'],['EUR','🇪🇺 Euro (€)'],['GBP','🇬🇧 Libra (£)'],['ARS','🇦🇷 Peso (ARS)']] as [$v,$l])
                        <button type="button"
                            :class="currency === '{{ $v }}' ? 'settings-format-btn sf-on' : 'settings-format-btn'"
                            @click="currency = '{{ $v }}'; saveAll()">{{ $l }}</button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="settings-field-label">Separadores numéricos</label>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.4rem">
                        <button type="button"
                            :class="number_format === 'br' ? 'settings-format-btn sf-on' : 'settings-format-btn'"
                            @click="number_format = 'br'; saveAll()">1.000,00 (Brasil)</button>
                        <button type="button"
                            :class="number_format === 'us' ? 'settings-format-btn sf-on' : 'settings-format-btn'"
                            @click="number_format = 'us'; saveAll()">1,000.00 (EUA)</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── CARD: Relatórios e impressão ── --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-6 0h.008v.008H12V10.5Zm-3.75 0h.008v.008H8.25V10.5Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Relatórios e impressão</p>
                        <p class="settings-card-desc">Configurações padrão para PDFs e exportações</p>
                    </div>
                </div>
            </div>

            <div style="padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:1.25rem">
                <div>
                    <label class="settings-field-label">Tamanho de papel padrão</label>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.4rem">
                        @foreach([['A4','A4 (210×297mm)'],['Letter','Carta (215×279mm)'],['Legal','Legal (216×356mm)']] as [$v,$l])
                        <button type="button"
                            :class="paper_size === '{{ $v }}' ? 'settings-format-btn sf-on' : 'settings-format-btn'"
                            @click="paper_size = '{{ $v }}'; saveAll()">{{ $l }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </x-settings.layout>
</section>
</section>
