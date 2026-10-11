@props(['steps' => []])

{{-- Passo a passo curto das telas de importação (1. Enviar · 2. Conferir · 3. Salvar). --}}
<ol class="fm-steps">
    @foreach($steps as $i => $step)
        <li class="fm-step">
            <span class="fm-step-num">{{ $i + 1 }}</span>
            <span class="fm-step-text">
                <strong>{{ $step[0] }}</strong>
                <small>{{ $step[1] }}</small>
            </span>
        </li>
    @endforeach
</ol>

@once
<style>
    .fm-steps { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin: 0; padding: 0; list-style: none; }
    .fm-step { display: flex; align-items: flex-start; gap: 8px; padding: 10px; border-radius: 14px; background: rgb(248 250 252); border: 1px solid rgb(241 245 249); min-width: 0; }
    .dark .fm-step { background: rgb(30 41 59 / .6); border-color: rgb(51 65 85); }
    .fm-step-num { width: 24px; height: 24px; flex-shrink: 0; border-radius: 999px; display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 800; color: #fff; background: linear-gradient(135deg, rgb(99 102 241), rgb(168 85 247)); }
    .fm-step-text { display: flex; flex-direction: column; min-width: 0; }
    .fm-step-text strong { font-size: 13px; font-weight: 700; color: rgb(15 23 42); }
    .fm-step-text small { font-size: 11px; line-height: 1.3; color: rgb(100 116 139); }
    .dark .fm-step-text strong { color: rgb(241 245 249); }
    .dark .fm-step-text small { color: rgb(148 163 184); }
    @media (max-width: 640px) {
        .fm-step { flex-direction: column; align-items: center; text-align: center; padding: 8px 6px; gap: 4px; }
        .fm-step-text small { display: none; }
        .fm-step-text strong { font-size: 12px; }
    }

    /* Cartões das telas de importação (envio e histórico) */
    .fm-imp-card { background: #fff; border: 1px solid rgb(226 232 240 / .9); border-radius: 20px; padding: 18px; box-shadow: 0 1px 2px rgb(15 23 42 / .04); }
    .dark .fm-imp-card { background: rgb(15 23 42 / .8); border-color: rgb(51 65 85 / .7); }
    .fm-imp-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .fm-imp-head-icon { width: 42px; height: 42px; flex-shrink: 0; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #fff;
        background: linear-gradient(135deg, rgb(99 102 241), rgb(168 85 247)); box-shadow: 0 8px 18px -10px rgb(99 102 241 / .8); }
    .fm-imp-head h3 { font-size: 16px; font-weight: 800; color: rgb(15 23 42); line-height: 1.2; }
    .fm-imp-head p { font-size: 12px; color: rgb(100 116 139); }
    .dark .fm-imp-head h3 { color: #fff; }
    .dark .fm-imp-head p { color: rgb(148 163 184); }
    .fm-imp-head-chip { margin-left: auto; display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;
        background: rgb(238 242 255); color: rgb(67 56 202); white-space: nowrap; }
    .dark .fm-imp-head-chip { background: rgb(99 102 241 / .12); color: rgb(165 180 252); }
    .fm-imp-go { display: flex; width: 100%; align-items: center; justify-content: center; gap: 8px; margin-top: 12px; padding: 12px 16px; border-radius: 14px;
        font-size: 14px; font-weight: 700; color: #fff; background: linear-gradient(90deg, rgb(79 70 229), rgb(147 51 234)); box-shadow: 0 10px 22px -12px rgb(79 70 229 / .8); transition: filter .2s; }
    .fm-imp-go:hover { filter: brightness(1.07); }
    .fm-imp-go:disabled { opacity: .6; cursor: not-allowed; }
    .fm-imp-note { display: flex; gap: 6px; align-items: flex-start; margin-top: 10px; font-size: 12px; color: rgb(100 116 139); }
    .dark .fm-imp-note { color: rgb(148 163 184); }
    @media (max-width: 640px) { .fm-imp-card { padding: 14px; border-radius: 18px; } }
</style>
@endonce
