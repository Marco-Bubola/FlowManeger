<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    //
}; ?>

<section class="settings-devices-page w-full mobile-393-base">
    <x-settings.layout :heading="''">

        @php
            $user       = Auth::user();
            $ip         = request()->ip();
            $ua         = request()->userAgent() ?? '';
            $isMobile   = str_contains(strtolower($ua), 'mobile') || str_contains(strtolower($ua), 'android');
            $isTablet   = str_contains(strtolower($ua), 'ipad') || str_contains(strtolower($ua), 'tablet');
            $deviceType = $isMobile ? 'Smartphone' : ($isTablet ? 'Tablet' : 'Computador');
            $deviceIcon = $isMobile ? 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3' :
                         ($isTablet ? 'M10.5 19.5H3m7.5 0H21m0 0V2.25A2.25 2.25 0 0 0 18.75 0H5.25A2.25 2.25 0 0 0 3 2.25V19.5m18 0v1.5a.75.75 0 0 1-.75.75H3.75a.75.75 0 0 1-.75-.75V19.5' :
                         'M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3');
            $browser = 'Navegador desconhecido';
            if (str_contains($ua, 'Chrome') && !str_contains($ua, 'Edg')) $browser = 'Google Chrome';
            elseif (str_contains($ua, 'Firefox')) $browser = 'Mozilla Firefox';
            elseif (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) $browser = 'Safari';
            elseif (str_contains($ua, 'Edg')) $browser = 'Microsoft Edge';
            elseif (str_contains($ua, 'Opera') || str_contains($ua, 'OPR')) $browser = 'Opera';
            $os = 'SO desconhecido';
            if (str_contains($ua, 'Windows NT')) $os = 'Windows';
            elseif (str_contains($ua, 'Macintosh')) $os = 'macOS';
            elseif (str_contains($ua, 'Linux') && !str_contains($ua, 'Android')) $os = 'Linux';
            elseif (str_contains($ua, 'Android')) $os = 'Android';
            elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) $os = 'iOS';
        @endphp

        <div class="s-pg-grid">
        <div class="s-col-main">

        {{-- CARD: Sessão atual --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(16,185,129,0.1);color:#10b981">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $deviceIcon }}"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Dispositivo atual</p>
                        <p class="settings-card-desc">Sessão autenticada neste momento</p>
                    </div>
                </div>
                <span class="s-badge s-badge-success" style="display:flex;align-items:center;gap:0.3rem">
                    <svg viewBox="0 0 6 6" fill="currentColor" style="width:0.45rem;height:0.45rem"><circle cx="3" cy="3" r="3"/></svg>
                    Online agora
                </span>
            </div>

            <div style="padding:0 1.25rem 1.5rem">
                <div class="sh-dev-active-card">
                    <div class="sh-dev-icon-wrap" style="background:rgba(16,185,129,0.1);color:#10b981">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:1.6rem;height:1.6rem"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $deviceIcon }}"/></svg>
                    </div>
                    <div class="sh-dev-main-info">
                        <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin:0 0 .2rem">{{ $deviceType }} · {{ $os }}</h3>
                        <p style="font-size:.82rem;color:#64748b;margin:0 0 .6rem">{{ $browser }}</p>
                        <div style="display:flex;flex-wrap:wrap;gap:.5rem">
                            <span class="sh-dev-chip sh-dev-chip--green">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.7rem;height:.7rem"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                {{ $ip }}
                            </span>
                            <span class="sh-dev-chip">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.7rem;height:.7rem"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Agora · {{ now()->format('H:i') }}
                            </span>
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <span class="s-badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0">Este dispositivo</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD: Outros dispositivos (demo) --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Histórico de acessos</p>
                        <p class="settings-card-desc">Dispositivos e navegadores que acessaram sua conta</p>
                    </div>
                </div>
                <span class="s-badge">1 sessão ativa</span>
            </div>

            @foreach([
                ['Computador','Windows','Google Chrome','192.168.1.1','Agora','ativo','M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3','settings-device-status-icon--active'],
                ['Smartphone','Android','Chrome Mobile','179.XX.XX.XX','2 dias atrás','inativo','M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3','settings-device-status-icon--inactive'],
                ['Tablet','iOS','Safari','200.XX.XX.XX','5 dias atrás','inativo','M10.5 19.5H3m7.5 0H21m0 0V2.25A2.25 2.25 0 0 0 18.75 0H5.25A2.25 2.25 0 0 0 3 2.25V19.5m18 0v1.5','settings-device-status-icon--inactive'],
            ] as [$dt, $os2, $br, $devIp, $when, $status, $icon, $statusClass])
            <div class="sh-dev-row" style="display:flex;align-items:center;gap:1rem;padding:.75rem 1.25rem;border-bottom:1px solid #f1f5f9">
                <div class="{{ $statusClass }}" style="width:2.25rem;height:2.25rem;border-radius:.6rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:1.1rem;height:1.1rem"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </div>
                <div style="flex:1;min-width:0">
                    <p style="font-size:.84rem;font-weight:700;color:#1e293b;margin:0">{{ $dt }} · {{ $os2 }}</p>
                    <p style="font-size:.75rem;color:#64748b;margin:.1rem 0 0">{{ $br }} · {{ $devIp }}</p>
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;flex-shrink:0">
                    <span style="font-size:.75rem;color:#94a3b8">{{ $when }}</span>
                    @if($status === 'ativo')
                        <span class="s-badge s-badge-success">Ativa</span>
                    @else
                        <span class="s-badge">Expirada</span>
                    @endif
                </div>
            </div>
            @endforeach

            <div style="padding:.75rem 1.25rem">
                <button type="button"
                    style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:.6rem;font-size:.8rem;font-weight:700;background:#fee2e2;color:#dc2626;border:1.5px solid #fecaca;cursor:pointer;transition:all .18s"
                    onclick="alert('Em produção, isso encerraria todas as outras sessões.')">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.85rem;height:.85rem"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                    Encerrar todas as outras sessões
                </button>
            </div>
        </div>

        {{-- CARD: Info do dispositivo atual --}}
        <div class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Detalhes do acesso atual</p>
                        <p class="settings-card-desc">Informações técnicas da sessão em andamento</p>
                    </div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem;padding:0 1.25rem 1.25rem">
                @foreach([
                    ['Tipo de dispositivo',$deviceType],
                    ['Sistema operacional',$os],
                    ['Navegador',$browser],
                    ['Endereço IP',$ip],
                    ['Protocolo','HTTPS'],
                    ['Horário da sessão',now()->format('d/m/Y H:i')],
                ] as [$lbl,$val])
                <div class="settings-info-tile">
                    <p class="settings-info-tile-label">{{ $lbl }}</p>
                    <p class="settings-info-tile-val">{{ $val }}</p>
                </div>
                @endforeach
            </div>
        </div>

        </div>{{-- /s-col-main --}}

        <div class="s-col-side">

        {{-- CARD: Configurações de segurança de dispositivo --}}
        @php $dbDevices = auth()->user()?->preferences['devices'] ?? null; @endphp
        <div class="settings-card" x-data="{
            trust_known: true,
            notify_new: true,
            auto_lock: false,
            saved: false,
            save() {
                const payload = {trust_known:this.trust_known,notify_new:this.notify_new,auto_lock:this.auto_lock};
                localStorage.setItem('flowmanager:devices', JSON.stringify(payload));
                const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.content ?? '';
                fetch('/settings/preferences/devices',{
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                    body:JSON.stringify({data:payload})
                }).catch(()=>{});
                this.saved = true; setTimeout(()=>this.saved=false, 2000);
            }
        }" x-init="
            const dbDev = @json($dbDevices ?? null);
            if(dbDev) {
                trust_known=dbDev.trust_known??true;
                notify_new=dbDev.notify_new??true;
                auto_lock=dbDev.auto_lock??false;
            } else {
                try{const s=JSON.parse(localStorage.getItem('flowmanager:devices')||'null');
                if(s){trust_known=s.trust_known??true;notify_new=s.notify_new??true;auto_lock=s.auto_lock??false;}
                }catch(e){}
            }
        ">
            <div class="settings-card-header">
                <div class="settings-card-title-row">
                    <div class="settings-card-icon" style="background:rgba(99,102,241,0.1);color:#6366f1">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                    </div>
                    <div>
                        <p class="settings-card-title">Configurações de segurança</p>
                        <p class="settings-card-desc">Como o sistema gerencia o reconhecimento de dispositivos</p>
                    </div>
                </div>
                <div x-show="saved" x-transition style="display:none;padding:.4rem .8rem;border-radius:.5rem;background:var(--s-accent);color:#fff;font-size:.75rem;font-weight:700">Salvo ✓</div>
            </div>

            <div class="settings-notif-list">
                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(16,185,129,0.1);color:#10b981">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.9rem;height:.9rem"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Dispositivos confiáveis</p>
                            <p class="settings-notif-desc">Não pedir verificação em dispositivos já autorizados</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="trust_known?'active':''" @click="trust_known=!trust_known;save()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>
                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.9rem;height:.9rem"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Alerta por e-mail em novo login</p>
                            <p class="settings-notif-desc">Receba um e-mail ao acessar de um dispositivo novo</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="notify_new?'active':''" @click="notify_new=!notify_new;save()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>
                <label class="settings-notif-row">
                    <div class="settings-notif-info">
                        <div class="settings-notif-icon" style="background:rgba(99,102,241,0.1);color:#6366f1">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:.9rem;height:.9rem"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75A4.5 4.5 0 0 0 7.5 6.75v3.75m9 0a2.25 2.25 0 0 1 2.25 2.25v6A2.25 2.25 0 0 1 16.5 21H7.5a2.25 2.25 0 0 1-2.25-2.25v-6a2.25 2.25 0 0 1 2.25-2.25m9 0h-9"/></svg>
                        </div>
                        <div>
                            <p class="settings-notif-title">Bloqueio automático (30 min)</p>
                            <p class="settings-notif-desc">Encerra a sessão após 30 minutos de inatividade</p>
                        </div>
                    </div>
                    <div class="settings-toggle-switch" :class="auto_lock?'active':''" @click="auto_lock=!auto_lock;save()">
                        <div class="settings-toggle-track"></div>
                        <div class="settings-toggle-thumb"></div>
                    </div>
                </label>
            </div>
        </div>

        @php
            $deviceFingerprint = strtoupper(substr(md5(($ua ?: 'unknown') . '|' . $ip), 0, 12));
            $trustScore = ($os !== 'SO desconhecido' ? 35 : 15) + ($browser !== 'Navegador desconhecido' ? 35 : 15) + ($isMobile || $isTablet ? 15 : 25);
            $trustLabel = $trustScore >= 80 ? 'Alta confiança' : ($trustScore >= 60 ? 'Confiável' : 'Revisar');
            $trustColor = $trustScore >= 80 ? '#10b981' : ($trustScore >= 60 ? '#f59e0b' : '#ef4444');
            $trustToneClass = $trustScore >= 80 ? 'settings-tone-good' : ($trustScore >= 60 ? 'settings-tone-warn' : 'settings-tone-danger');
            $trustTextClass = $trustScore >= 80 ? 'settings-text-good' : ($trustScore >= 60 ? 'settings-text-warn' : 'settings-text-danger');
            $practiceCards = [
                ['Ative alerta por novo login', 'Receba aviso imediato sempre que um novo dispositivo acessar sua conta.', 'settings-advice-card--good', 'settings-advice-title--good'],
                ['Use bloqueio automático', 'Ideal para notebooks compartilhados ou acesso em lojas e escritórios.', 'settings-advice-card--info', 'settings-advice-title--info'],
                ['Revogue sessões antigas', 'Mantenha apenas dispositivos que você realmente reconhece.', 'settings-advice-card--warn', 'settings-advice-title--warn'],
            ];
        @endphp

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem;margin-top:1rem">
            <div class="settings-card" style="padding:1.25rem;background:linear-gradient(135deg,rgba(var(--s-accent-rgb),0.07),transparent)">
                <div class="settings-card-header" style="margin-bottom:.9rem">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon {{ $trustToneClass }}">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-card-title">Nível de confiança</p>
                            <p class="settings-card-desc">Avaliação rápida deste dispositivo e sessão atual</p>
                        </div>
                    </div>
                    <span class="s-badge {{ $trustToneClass }}">{{ $trustLabel }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:1rem;margin-bottom:.9rem">
                    <div style="position:relative;width:4.6rem;height:4.6rem;flex-shrink:0">
                        <svg width="74" height="74" viewBox="0 0 74 74">
                            <circle cx="37" cy="37" r="30" fill="none" stroke="#e2e8f0" stroke-width="7" />
                            <circle cx="37" cy="37" r="30" fill="none" stroke="{{ $trustColor }}" stroke-width="7" stroke-linecap="round"
                                stroke-dasharray="{{ round(2 * M_PI * 30 * $trustScore / 100) }} {{ round(2 * M_PI * 30) }}" transform="rotate(-90 37 37)" />
                        </svg>
                        <div class="{{ $trustTextClass }}" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:800">{{ $trustScore }}%</div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:.35rem">
                        <p style="font-size:.78rem;color:#64748b;margin:0">A confiança leva em conta navegador identificado, sistema operacional e consistência da sessão.</p>
                        <div style="display:flex;flex-wrap:wrap;gap:.45rem">
                            <span class="s-badge s-badge-success">{{ $browser }}</span>
                            <span class="s-badge s-badge-accent">{{ $os }}</span>
                            <span class="s-badge">{{ $deviceType }}</span>
                        </div>
                    </div>
                </div>
                <div class="settings-info-tile" style="background:rgba(15,23,42,.04)">
                    <p class="settings-info-tile-label">Fingerprint</p>
                    <p class="settings-info-tile-val" style="letter-spacing:.12em">{{ $deviceFingerprint }}</p>
                </div>
            </div>

            <div class="settings-card" style="padding:1.25rem">
                <div class="settings-card-header" style="margin-bottom:.9rem">
                    <div class="settings-card-title-row">
                        <div class="settings-card-icon" style="background:rgba(59,130,246,.12);color:#2563eb">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.674 1.216-.832l5.06-1.686a.75.75 0 0 0 .45-.95l-1.686-5.06a1.875 1.875 0 0 0-2.372-1.185L11.528 4.11a2.25 2.25 0 0 0-.832 1.216l-1.686 5.06a.75.75 0 0 0 .45.95l5.06 1.686c.476.158.899.448 1.216.832l3.03 2.496M6.75 7.5h.008v.008H6.75V7.5Z"/></svg>
                        </div>
                        <div>
                            <p class="settings-card-title">Boas práticas para este acesso</p>
                            <p class="settings-card-desc">Ajustes simples para reduzir risco operacional</p>
                        </div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:.6rem">
                    @foreach($practiceCards as [$title, $desc, $cardClass, $titleClass])
                    <div class="{{ $cardClass }}" style="padding:.8rem .9rem;border-radius:.8rem">
                        <p class="{{ $titleClass }}" style="font-size:.8rem;font-weight:700;margin:0">{{ $title }}</p>
                        <p style="font-size:.74rem;color:#64748b;margin:.18rem 0 0">{{ $desc }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        </div>{{-- /s-col-side --}}
        </div>{{-- /s-pg-grid --}}

    </x-settings.layout>
</section>
