<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>
@php
    /** @var array|null $dbSystem */
    $dbSystem = auth()->user()?->preferences['system'] ?? null;
@endphp

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
            const payload = {
                language: this.language,
                timezone: this.timezone,
                date_format: this.date_format,
                time_format: this.time_format,
                first_dow: this.first_dow,
                currency: this.currency,
                number_format: this.number_format,
                paper_size: this.paper_size,
            };
            localStorage.setItem('flowmanager:system', JSON.stringify(payload));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch('/settings/preferences/system', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                body: JSON.stringify({data: payload})
            }).catch(()=>{});
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
        const dbData = @json($dbSystem ?? null);
        if (dbData) {
            if (dbData.language) language = dbData.language;
            if (dbData.timezone) timezone = dbData.timezone;
            if (dbData.date_format) date_format = dbData.date_format;
            if (dbData.time_format) time_format = dbData.time_format;
            if (dbData.first_dow) first_dow = dbData.first_dow;
            if (dbData.currency) currency = dbData.currency;
            if (dbData.number_format) number_format = dbData.number_format;
            if (dbData.paper_size) paper_size = dbData.paper_size;
        } else {
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
        }
    ">

    {{-- Toast salvo --}}
    <div x-show="saved" x-transition.opacity
         style="display:none;position:fixed;top:1.25rem;right:1.25rem;z-index:9999;padding:0.65rem 1.1rem;border-radius:0.75rem;background:var(--s-accent);color:#fff;font-size:0.82rem;font-weight:700;box-shadow:0 4px 20px rgba(0,0,0,0.18);display:flex;align-items:center;gap:0.4rem">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:0.9rem;height:0.9rem"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        Configurações salvas!
    </div>

    <x-settings.layout :heading="''">

        <div class="s-pg-grid">
        <div class="s-col-main">

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

        </div>{{-- /s-col-main --}}

        <div class="s-col-side">

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

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:1rem;margin-top:1rem">
            <div class="settings-card" style="padding:1.25rem;background:linear-gradient(135deg,rgba(var(--s-accent-rgb),0.08),transparent)">
                <div class="settings-card-header" style="margin-bottom:.9rem">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(var(--s-accent-rgb),0.14)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h12A2.25 2.25 0 0 0 20.25 14.25V3M3.75 3h16.5M3.75 3l2.25 2.25m12-2.25L15.75 5.25M8.25 21h7.5"/></svg>
                        </div>
                        <div>
                            <p class="settings-card-title">Resumo operacional</p>
                            <p class="settings-card-desc">Combinação atual de idioma, formato e impressão</p>
                        </div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem">
                    <div class="settings-info-tile"><p class="settings-info-tile-label">Idioma</p><p class="settings-info-tile-val" x-text="language"></p></div>
                    <div class="settings-info-tile"><p class="settings-info-tile-label">Fuso</p><p class="settings-info-tile-val" x-text="timezone.replace('_',' ').split('/').pop()"></p></div>
                    <div class="settings-info-tile"><p class="settings-info-tile-label">Data/Hora</p><p class="settings-info-tile-val"><span x-text="datePreview"></span> · <span x-text="timePreview"></span></p></div>
                    <div class="settings-info-tile"><p class="settings-info-tile-label">Papel</p><p class="settings-info-tile-val" x-text="paper_size"></p></div>
                </div>
            </div>

            <div class="settings-card" style="padding:1.25rem">
                <div class="settings-card-header" style="margin-bottom:.9rem">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(16,185,129,.12);color:#10b981">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75h19.5v10.5H2.25V6.75Zm3 3.75h6m-6 3h12"/></svg>
                        </div>
                        <div>
                            <p class="settings-card-title">Exemplo em documento</p>
                            <p class="settings-card-desc">Prévia de um PDF usando suas preferências</p>
                        </div>
                    </div>
                </div>
                <div style="padding:1rem;border-radius:.95rem;background:linear-gradient(180deg,#ffffff,#f8fafc);border:1px solid #e2e8f0;box-shadow:inset 0 1px 0 rgba(255,255,255,.7)">
                    <div style="display:flex;justify-content:space-between;gap:1rem;margin-bottom:.8rem">
                        <div>
                            <p style="font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;margin:0">Relatório</p>
                            <p style="font-size:1rem;font-weight:800;color:#0f172a;margin:.15rem 0 0">Painel Financeiro</p>
                        </div>
                        <div style="text-align:right">
                            <p style="font-size:.72rem;color:#94a3b8;margin:0">Gerado em</p>
                            <p style="font-size:.82rem;font-weight:700;color:#334155;margin:.15rem 0 0"><span x-text="datePreview"></span> · <span x-text="timePreview"></span></p>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.55rem;margin-bottom:.8rem">
                        <div style="padding:.65rem;border-radius:.7rem;background:rgba(var(--s-accent-rgb),.07)">
                            <p style="font-size:.68rem;color:#94a3b8;margin:0">Receita</p>
                            <p style="font-size:.88rem;font-weight:800;color:var(--s-accent);margin:.15rem 0 0" x-text="currencyPreview"></p>
                        </div>
                        <div style="padding:.65rem;border-radius:.7rem;background:rgba(16,185,129,.07)">
                            <p style="font-size:.68rem;color:#94a3b8;margin:0">Formato</p>
                            <p style="font-size:.88rem;font-weight:800;color:#047857;margin:.15rem 0 0" x-text="paper_size"></p>
                        </div>
                        <div style="padding:.65rem;border-radius:.7rem;background:rgba(99,102,241,.07)">
                            <p style="font-size:.68rem;color:#94a3b8;margin:0">Semana</p>
                            <p style="font-size:.88rem;font-weight:800;color:#4338ca;margin:.15rem 0 0" x-text="first_dow === 'sun' ? 'Domingo' : 'Segunda' "></p>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:.35rem">
                        <div style="height:6px;border-radius:999px;background:#e2e8f0;width:100%"></div>
                        <div style="height:6px;border-radius:999px;background:rgba(var(--s-accent-rgb),.18);width:78%"></div>
                        <div style="height:6px;border-radius:999px;background:#e2e8f0;width:61%"></div>
                    </div>
                </div>
            </div>
        </div>

        </div>{{-- /s-col-side --}}
        </div>{{-- /s-pg-grid --}}

    </x-settings.layout>
</section>
