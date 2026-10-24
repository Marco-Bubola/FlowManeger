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

    <details class="promo-season" @if($season) open @endif>
        <summary><i class="bi bi-stars"></i> Datas comemorativas <span>Natal, Black Friday, Dia das Mães...</span><i class="bi bi-chevron-down ms-auto"></i></summary>
        <div class="promo-season-row" data-testid="season-row">
            @foreach($this->seasonPresets() as $d)
                <button type="button" wire:click="applyPreset('data:{{ $d['key'] }}')" class="promo-season-chip {{ $season === $d['key'] ? 'on' : '' }}" data-testid="season-{{ $d['key'] }}">
                    <span class="e">{{ $d['emoji'] }}</span>
                    <span class="t"><b>{{ $d['name'] }}</b><small>{{ $d['now'] ? 'Já começou · até ' . $d['end']->format('d/m') : \App\Services\Promotions\CommercialCalendar::label($d) }}</small></span>
                </button>
            @endforeach
        </div>
    </details>

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
    .promo-season { border-radius: .8rem; border: 1px solid rgba(168,85,247,.25); background: rgba(168,85,247,.06); }
    .promo-season summary { list-style: none; display: flex; align-items: center; gap: .4rem; padding: .45rem .6rem; cursor: pointer; font-size: .74rem; font-weight: 700; color: rgb(109 40 217); }
    .promo-season summary::-webkit-details-marker { display: none; }
    .promo-season summary span { font-weight: 500; color: rgb(100 116 139); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0; }
    .promo-season[open] summary .bi-chevron-down { transform: rotate(180deg); }
    .dark .promo-season summary { color: rgb(216 180 254); }
    .promo-season-row { display: flex; gap: .4rem; overflow-x: auto; padding: 0 .5rem .55rem; scrollbar-width: thin; }
    .promo-season-chip { flex-shrink: 0; display: flex; align-items: center; gap: .4rem; padding: .35rem .6rem .35rem .4rem; border-radius: .7rem; background: #fff; border: 1px solid rgba(148,163,184,.35); text-align: left; }
    .dark .promo-season-chip { background: rgb(30 41 59); border-color: rgb(51 65 85); }
    .promo-season-chip .e { font-size: 1.15rem; line-height: 1; }
    .promo-season-chip b { display: block; font-size: .72rem; color: rgb(30 41 59); white-space: nowrap; }
    .dark .promo-season-chip b { color: rgb(241 245 249); }
    .promo-season-chip small { display: block; font-size: .64rem; color: rgb(100 116 139); white-space: nowrap; }
    .promo-season-chip.on { border-color: #a855f7; box-shadow: 0 0 0 2px rgba(168,85,247,.3); background: rgba(168,85,247,.08); }
    .promo-sched input[type=date], .promo-sched input[type=time] { width: 100%; min-width: 0; }
    .promo-sched-summary { display: flex; gap: .35rem; align-items: flex-start; font-size: .7rem; line-height: 1.35; color: rgb(100 116 139); }
    .promo-sched-summary.is-scheduled { padding: .45rem .6rem; border-radius: .7rem; color: rgb(109 40 217); background: rgba(168,85,247,.10); border: 1px solid rgba(168,85,247,.25); }
    .dark .promo-sched-summary.is-scheduled { color: rgb(216 180 254); background: rgba(168,85,247,.15); }
</style>
@endonce
