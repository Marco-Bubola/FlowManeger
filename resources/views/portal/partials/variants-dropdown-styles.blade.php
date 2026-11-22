{{-- Menu colorido das variações (página do produto e a amostra nas configurações). --}}
@once
<style>
    /* Variações em lista suspensa colorida */
    .pp-vd { position: relative; margin-top: 16px; z-index: 6; }
    .pp-vd.open { z-index: 40; }
    .pp-vd-btn { position: relative; z-index: 2; width: 100%; display: flex; align-items: center; gap: 12px; min-height: 64px; padding: 8px 10px 8px 8px; border: 0; border-radius: 32px; cursor: pointer; text-align: left; color: #fff;
        background: linear-gradient(115deg, var(--c2), var(--c1)); box-shadow: 0 10px 24px -12px var(--c2), inset 0 1px 0 rgba(255,255,255,.25); transition: border-radius .25s, box-shadow .25s; }
    .pp-vd.open .pp-vd-btn { border-radius: 32px 32px 0 0; box-shadow: none; }
    .pp-vd-av { flex-shrink: 0; width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,.85); display: flex; align-items: center; justify-content: center; overflow: hidden; color: var(--c2); font-size: 18px; box-shadow: 0 2px 8px rgba(0,0,0,.15); }
    .pp-vd-av img { width: 100%; height: 100%; object-fit: cover; }
    .pp-vd-txt { flex: 1; min-width: 0; display: flex; flex-direction: column; line-height: 1.2; }
    .pp-vd-txt small { font-size: 12px; font-weight: 600; opacity: .85; text-transform: uppercase; letter-spacing: .05em; }
    .pp-vd-txt b { font-size: 17px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pp-vd-n { flex-shrink: 0; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 999px; background: rgba(255,255,255,.2); }
    .pp-vd-chev { flex-shrink: 0; width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,.22); display: flex; align-items: center; justify-content: center; transition: transform .25s; }
    .pp-vd.open .pp-vd-chev { transform: rotate(180deg); }
    .pp-vd-panel { position: absolute; left: 0; right: 0; top: calc(100% - 1px); display: flex; flex-direction: column; gap: 10px; padding: 8px 12px 14px; max-height: min(60vh, 430px); overflow-y: auto; overscroll-behavior: contain;
        border-radius: 0 0 30px 30px; background: linear-gradient(180deg, color-mix(in srgb, var(--c1) 55%, var(--c2)) 0%, var(--c2) 100%); box-shadow: 0 26px 40px -18px rgba(0,0,0,.45); transform-origin: top center; scrollbar-width: thin; }
    .pp-vd-in { transition: opacity .2s ease, transform .22s cubic-bezier(.2,.9,.3,1.2); }
    .pp-vd-from { opacity: 0; transform: translateY(-8px) scaleY(.92); }
    .pp-vd-item { display: flex; align-items: center; gap: 12px; min-height: 52px; padding: 6px 16px 6px 8px; border: 0; border-radius: 999px; cursor: pointer; text-align: left;
        background: rgba(255,255,255,.72); color: color-mix(in srgb, var(--c2) 80%, #000); font-size: 15px; font-weight: 600; transition: background .15s, transform .15s; }
    .pp-vd-item:hover { background: rgba(255,255,255,.9); transform: translateX(2px); }
    .pp-vd-item.on { background: #fff; box-shadow: 0 6px 14px -8px rgba(0,0,0,.5); }
    .pp-vd-item.off { opacity: .6; }
    .pp-vd-item.off .pp-vd-name { text-decoration: line-through; }
    .pp-vd-ic { flex-shrink: 0; width: 38px; height: 38px; border-radius: 50%; background: color-mix(in srgb, var(--c1) 18%, #fff); color: var(--c1); display: flex; align-items: center; justify-content: center; overflow: hidden; font-size: 9px; }
    .pp-vd-ic img { width: 100%; height: 100%; object-fit: cover; }
    .pp-vd-name { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pp-vd-price { flex-shrink: 0; font-size: 13px; font-weight: 700; opacity: .8; }
    .pp-vd-ok { flex-shrink: 0; width: 22px; text-align: center; color: var(--c1); opacity: 0; }
    .pp-vd-item.on .pp-vd-ok { opacity: 1; }

</style>
@endonce
