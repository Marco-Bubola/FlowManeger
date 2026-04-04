<div {{ $attributes->class('flex items-center gap-2.5') }}>
    <img src="/favicon.svg" alt="{{ config('app.name', 'FlowManager') }}" class="h-8 w-8 shrink-0 rounded-none object-contain" />
    <div class="grid min-w-0 flex-1 text-start text-sm leading-tight">
        <span class="truncate font-black tracking-[-0.03em] text-slate-900 dark:text-white">{{ config('app.name', 'FlowManager') }}</span>
        <span class="truncate text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">CRM e operacao</span>
    </div>
</div>
