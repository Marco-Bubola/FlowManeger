{{--
  Origem (como conheceu) e etiquetas do cliente no cadastro interno.
  Precisa do trait App\Livewire\Clients\Concerns\HasClientCrmFields.
--}}
@php
    $input = 'w-full px-4 py-3 rounded-xl border-2 border-slate-200/50 dark:border-slate-600/50 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all';
    $label = 'block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2';
    $sources = \App\Services\Clients\ClientStatus::SOURCES;
    $tags = \App\Models\ClientTag::where('user_id', auth()->id())->orderBy('name')->get();
    $sourceNow = $source;
    $colorNow = $newTagColor;
@endphp
<div id="crm" class="crm-fields-card scroll-mt-24 mt-6 rounded-3xl border border-slate-200/70 dark:border-slate-700/60 bg-white/90 dark:bg-slate-900/80 shadow-sm p-4 sm:p-6 grid grid-cols-1 gap-4">
    <div class="flex items-center gap-2 text-sm font-bold text-slate-600 dark:text-slate-300 ">
        <i class="bi bi-tags text-indigo-500"></i> Relacionamento <span class="font-normal text-slate-400">(opcional)</span>
    </div>

    <div>
        <span class="{{ $label }}">Como conheceu a loja</span>
        <div class="flex flex-wrap gap-2">
            @foreach($sources as $key => $s)
                <button type="button" wire:click="$set('source', '{{ $sourceNow === $key ? '' : $key }}')"
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold border transition
                               {{ $sourceNow === $key ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:border-indigo-300' }}">
                    <i class="bi {{ $s['icon'] }}"></i>{{ $s['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    <div>
        <span class="{{ $label }}">Etiquetas <span class="font-normal text-slate-400">toque para marcar</span></span>
        <div class="flex flex-wrap gap-2">
            @forelse($tags as $tag)
                @php $on = in_array($tag->id, $tagIds, true); @endphp
                <button type="button" wire:click="toggleTag({{ $tag->id }})" wire:key="ctag-{{ $tag->id }}"
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold border-2 transition {{ $on ? 'border-current shadow-sm' : 'border-transparent opacity-60 hover:opacity-100' }}"
                        style="{{ $tag->style($on ? 0.2 : 0.1) }}">
                    <i class="bi {{ $on ? 'bi-check-circle-fill' : 'bi-tag' }}"></i>{{ $tag->name }}
                </button>
            @empty
                <p class="text-xs text-slate-500 dark:text-slate-400">Nenhuma etiqueta ainda. Crie a primeira abaixo (ex.: VIP, Atacado, Revendedora).</p>
            @endforelse
        </div>
        <div class="mt-3 flex flex-col sm:flex-row sm:items-center gap-2">
            <input type="text" wire:model="newTagName" wire:keydown.enter.prevent="addTag" maxlength="40" placeholder="Nova etiqueta" class="{{ $input }} sm:max-w-xs !py-2">
            <div class="flex items-center gap-1.5">
                @foreach(\App\Models\ClientTag::COLORS as $c)
                    <button type="button" wire:click="$set('newTagColor', '{{ $c }}')" title="{{ $c }}"
                            class="w-6 h-6 rounded-full ring-offset-2 dark:ring-offset-slate-900 {{ $colorNow === $c ? 'ring-2 ring-slate-500' : '' }}"
                            style="background: {{ \App\Models\ConsortiumNotification::TONES[$c] }}"></button>
                @endforeach
            </div>
            <button type="button" wire:click="addTag" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 text-sm font-bold hover:bg-indigo-100">
                <i class="bi bi-plus-lg"></i>Criar
            </button>
        </div>
    </div>
</div>
