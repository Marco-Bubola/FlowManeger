{{-- Estilos dos cartões das janelas de Configurações e Enviar (inline: o deploy não reenvia public/assets) --}}
<style>
    .set-card { border-radius: 1rem; padding: .85rem; background: rgba(248,250,252,.9); border: 1px solid rgba(226,232,240,.9); }
    .dark .set-card { background: rgba(15,23,42,.5); border-color: rgba(51,65,85,.8); }
    .set-head { display: flex; gap: .65rem; align-items: flex-start; margin-bottom: .6rem; }
    .set-head h4 { font-size: .88rem; font-weight: 800; color: rgb(30 41 59); line-height: 1.2; }
    .set-head p { font-size: .72rem; color: rgb(100 116 139); margin-top: .1rem; }
    .dark .set-head h4 { color: #fff; }
    .set-icon { width: 2.1rem; height: 2.1rem; flex-shrink: 0; border-radius: .7rem; display: inline-flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,.12); }
    .set-row { display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; }
    .set-input { display: inline-flex; align-items: center; border-radius: .75rem; border: 1px solid rgba(203,213,225,.9); background: #fff; overflow: hidden; }
    .set-input input { width: 4.2rem; padding: .4rem .5rem; border: 0; background: transparent; font-size: .85rem; font-weight: 700; text-align: right; color: rgb(30 41 59); }
    .set-input input:focus { outline: none; box-shadow: none; }
    .set-input span { padding: 0 .55rem 0 0; font-size: .8rem; color: rgb(148 163 184); font-weight: 700; }
    .dark .set-input { background: rgb(15 23 42); border-color: rgb(51 65 85); }
    .dark .set-input input { color: rgb(226 232 240); }
    .set-example { margin-top: .55rem; font-size: .72rem; color: rgb(5 150 105); }
    .set-error { margin-top: .35rem; font-size: .72rem; font-weight: 600; color: rgb(220 38 38); }
    .set-option { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .2rem; padding: .6rem .4rem; border-radius: .9rem; text-align: center;
        background: #fff; border: 1.5px solid rgba(226,232,240,.95); color: rgb(71 85 105); transition: all .15s; min-height: 3.6rem; }
    .set-option:hover { border-color: rgba(244,63,94,.5); color: rgb(225 29 72); }
    .set-option-on { border-color: transparent !important; color: #fff !important; background: linear-gradient(135deg, #f43f5e, #ec4899, #f97316) !important; box-shadow: 0 6px 16px rgba(244,63,94,.3); }
    .dark .set-option { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(203 213 225); }
    .set-textarea { width: 100%; padding: .6rem .7rem; border-radius: .9rem; font-size: .8rem; border: 1px solid rgba(203,213,225,.9); background: #fff; color: rgb(30 41 59); }
    .set-textarea:focus { outline: none; border-color: #f43f5e; box-shadow: 0 0 0 3px rgba(244,63,94,.15); }
    .dark .set-textarea { background: rgb(15 23 42); border-color: rgb(51 65 85); color: rgb(226 232 240); }
    .set-var { padding: .2rem .55rem; border-radius: 999px; font-size: .68rem; font-weight: 600; background: rgba(244,63,94,.1); color: rgb(190 18 60); border: 1px solid rgba(244,63,94,.2); }
    .set-var:hover { background: rgba(244,63,94,.2); }
    .dark .set-var { color: rgb(253 164 175); }
    .set-wa { border-radius: 1rem; padding: .8rem; min-height: 12rem; background: #efeae2 radial-gradient(rgba(0,0,0,.04) 1px, transparent 1px) 0 0 / 14px 14px; }
    .dark .set-wa { background-color: #0b141a; }
    .set-wa-bubble { position: relative; max-width: 95%; margin-left: auto; padding: .55rem .7rem; border-radius: .8rem .2rem .8rem .8rem; background: #d9fdd3; color: #111b21; font-size: .8rem; line-height: 1.35; box-shadow: 0 1px 1px rgba(0,0,0,.1); word-break: break-word; }
    .dark .set-wa-bubble { background: #005c4b; color: #e9edef; }
    .set-toggle { display: flex; align-items: center; gap: .6rem; font-size: .8rem; color: rgb(71 85 105); cursor: pointer; }
    .dark .set-toggle { color: rgb(203 213 225); }
    .set-toggle-card { display: flex; align-items: center; gap: .75rem; cursor: pointer; }
    .set-toggle-card b { display: block; font-size: .86rem; color: rgb(30 41 59); }
    .set-toggle-card small { display: block; font-size: .72rem; color: rgb(100 116 139); margin-top: .1rem; }
    .dark .set-toggle-card b { color: #fff; }
    .set-switch { position: relative; width: 2.6rem; height: 1.5rem; flex-shrink: 0; border-radius: 999px; background: rgb(203 213 225); transition: background .2s; }
    .set-switch::after { content: ''; position: absolute; top: .18rem; left: .18rem; width: 1.14rem; height: 1.14rem; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .2s; }
    .peer:checked + .set-switch { background: linear-gradient(135deg, #f43f5e, #f97316); }
    .peer:checked + .set-switch::after { transform: translateX(1.1rem); }
    .dark .set-switch { background: rgb(71 85 105); }
</style>
