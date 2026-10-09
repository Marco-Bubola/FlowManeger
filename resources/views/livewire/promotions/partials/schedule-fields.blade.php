{{-- "Começa: agora / em uma data" + atalhos + "Termina". Horários de São Paulo. --}}
@php $schedKey = $schedKey ?? 's'; @endphp
<div class="promo-sched space-y-2" wire:key="sched-{{ $schedKey }}">
    <div class="flex items-center justify-between gap-2">
        <span class="promo-sched-label"><i class="bi bi-calendar-event"></i> Começa</span>
        <div class="promo-sched-seg">
            <button type="button" wire:click="setStartMode('now')" class="{{ $startMode === 'now' ? 'on' : '' }}"><i class="bi bi-lightning-charge"></i> Agora</button>
            <button type="button" wire:click="setStartMode('date')" class="{{ $startMode === 'date' ? 'on' : '' }}"><i class="bi bi-clock"></i> Em uma data</button>
        </div>
    </div>

    <div class="flex flex-wrap gap-1.5">
        @foreach($this->schedulePresets() as $key => $label)
            <button type="button" wire:click="applyPreset('{{ $key }}')" class="promo-chip promo-sched-chip">{{ $label }}</button>
        @endforeach
    </div>

    <div class="grid {{ $startMode === 'date' ? 'grid-cols-[minmax(0,1fr)_minmax(0,0.8fr)]' : 'grid-cols-1' }} gap-2">
        @if($startMode === 'date')
            <label class="promo-field"><span>Dia</span><input type="date" wire:model.live="startDate" min="{{ now(\App\Models\Promotion::tz())->format('Y-m-d') }}"></label>
            <label class="promo-field promo-sched-time"><span>Hora</span><input type="time" wire:model.live="startTime" step="900"></label>
        @endif
        <label class="promo-field {{ $startMode === 'date' ? 'col-span-2' : '' }}"><span>Termina</span><input type="date" wire:model.live="endsAt" min="{{ now(\App\Models\Promotion::tz())->format('Y-m-d') }}"></label>
    </div>

    <p class="promo-sched-summary {{ $startMode === 'date' ? 'is-scheduled' : '' }}">
        <i class="bi {{ $startMode === 'date' ? 'bi-alarm' : 'bi-info-circle' }}"></i>
        <span>{{ $this->scheduleSummary }}. <span class="opacity-70">Horário de Brasília.</span>@if($startMode === 'date') Até começar, os preços não mudam. @endif</span>
    </p>
</div>

@once
<style>
    .promo-sched-label { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: rgb(100 116 139); }
    .dark .promo-sched-label { color: rgb(148 163 184); }
    .promo-sched-seg { display: inline-flex; gap: 2px; padding: 3px; border-radius: .8rem; background: rgba(148,163,184,.18); }
    .promo-sched-seg button { padding: .3rem .6rem; border-radius: .6rem; font-size: .72rem; font-weight: 700; color: rgb(71 85 105); white-space: nowrap; }
    .dark .promo-sched-seg button { color: rgb(203 213 225); }
    .promo-sched-seg button.on { color: #fff; background: linear-gradient(135deg, #ec4899, #a855f7 55%, #3b82f6); box-shadow: 0 2px 8px rgba(168,85,247,.35); }
    .promo-sched-chip { font-size: .7rem !important; }
    .promo-sched input[type=date], .promo-sched input[type=time] { width: 100%; min-width: 0; }
    .promo-sched-summary { display: flex; gap: .35rem; align-items: flex-start; font-size: .7rem; line-height: 1.35; color: rgb(100 116 139); }
    .promo-sched-summary.is-scheduled { padding: .45rem .6rem; border-radius: .7rem; color: rgb(109 40 217); background: rgba(168,85,247,.10); border: 1px solid rgba(168,85,247,.25); }
    .dark .promo-sched-summary.is-scheduled { color: rgb(216 180 254); background: rgba(168,85,247,.15); }
</style>
@endonce
