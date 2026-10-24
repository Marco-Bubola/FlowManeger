@php
    $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $in = 'width:100%;border:1px solid rgba(148,163,184,.45);border-radius:.6rem;padding:.55rem .75rem;font-size:.9rem;background:transparent';
@endphp
<div class="settings-card" data-testid="shipping-zones">
    <div class="settings-card-header">
        <div class="settings-card-title-row">
            <div class="settings-card-icon" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-geo-alt" style="font-size:1rem"></i></div>
            <div>
                <p class="settings-card-title">Frete por região (CEP)</p>
                <p class="settings-card-desc">Cada bairro ou cidade com o seu frete. O cliente calcula na página do produto e paga no carrinho.</p>
            </div>
        </div>
        <button type="button" wire:click="create" class="pay-test-btn" data-testid="zone-new"><i class="bi bi-plus-lg"></i> Região</button>
    </div>

    <div style="padding:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:.75rem">
        @unless($deliveryOn)
            <p style="font-size:.8rem;color:#b45309;background:rgba(245,158,11,.1);border-radius:.6rem;padding:.6rem .75rem;margin:0"><i class="bi bi-info-circle"></i> O frete aparece para o cliente quando o pagamento pelo site e "Entregar no endereço do cliente" estão ligados acima.</p>
        @endunless

        @forelse($zones as $z)
            <div wire:key="zn-{{ $z->id }}" style="display:flex;align-items:center;gap:.75rem;border:1px solid rgba(148,163,184,.3);border-radius:.8rem;padding:.65rem .75rem;{{ $z->active ? '' : 'opacity:.6' }}" data-testid="zone-row">
                <div style="width:2.2rem;height:2.2rem;border-radius:.6rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(99,102,241,.1);color:#6366f1"><i class="bi {{ $z->kind === 'city' ? 'bi-buildings' : 'bi-signpost' }}"></i></div>
                <div style="min-width:0;flex:1">
                    <p style="margin:0;font-weight:700;font-size:.9rem">{{ $z->name }} <span style="font-weight:600;color:#059669">· {{ (float) $z->fee > 0 ? $money($z->fee) : 'Grátis' }}</span></p>
                    <p style="margin:0;font-size:.75rem;color:#64748b"><span style="white-space:nowrap">{{ $z->placeLabel() }}</span>@if($z->free_above) · <span style="white-space:nowrap">grátis acima de {{ $money($z->free_above) }}</span>@endif @if($z->eta) · {{ $z->eta }}@endif</p>
                </div>
                <button type="button" wire:click="toggle({{ $z->id }})" title="{{ $z->active ? 'Desligar' : 'Ligar' }}" style="font-size:1.25rem;color:{{ $z->active ? '#10b981' : '#94a3b8' }}"><i class="bi {{ $z->active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i></button>
                <button type="button" wire:click="edit({{ $z->id }})" title="Editar" style="color:#6366f1"><i class="bi bi-pencil"></i></button>
                <button type="button" x-data @click="fmConfirm({ title: 'Excluir a região {{ addslashes($z->name) }}?', confirmText: 'Excluir' }).then(ok => ok && $wire.delete({{ $z->id }}))" title="Excluir" style="color:#e11d48"><i class="bi bi-trash3"></i></button>
            </div>
        @empty
            <p style="margin:0;font-size:.85rem;color:#64748b;border:1px dashed rgba(148,163,184,.5);border-radius:.8rem;padding:.9rem;text-align:center">Sem regiões, todo CEP paga a taxa de entrega acima ({{ $money($defaultFee) }}).</p>
        @endforelse

        @if($showForm)
            <div style="border:1px solid rgba(99,102,241,.35);background:rgba(99,102,241,.04);border-radius:.9rem;padding:1rem;display:flex;flex-direction:column;gap:.75rem" data-testid="zone-form">
                <p style="margin:0;font-weight:700">{{ $editingId ? 'Editar região' : 'Nova região' }}</p>
                <div class="settings-form-grid">
                    <div>
                        <label class="settings-field-label" for="zn-name">Nome</label>
                        <input id="zn-name" type="text" wire:model="name" maxlength="80" placeholder="Ex.: Centro, Zona Sul, Cidade vizinha" style="{{ $in }}">
                        @error('name') <p class="pay-err">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <span class="settings-field-label">Vale para</span>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.25rem;background:rgba(148,163,184,.15);border-radius:.6rem;padding:.2rem">
                            @foreach(['cep' => 'Faixa de CEP', 'city' => 'Cidade inteira'] as $k => $n)
                                <button type="button" wire:click="$set('kind', '{{ $k }}')" style="border-radius:.45rem;padding:.45rem;font-size:.82rem;font-weight:600;{{ $kind === $k ? 'background:#fff;color:#4f46e5;box-shadow:0 1px 3px rgba(0,0,0,.12)' : 'color:#64748b' }}">{{ $n }}</button>
                            @endforeach
                        </div>
                    </div>
                    @if($kind === 'cep')
                        <div>
                            <label class="settings-field-label" for="zn-from">CEP inicial</label>
                            <input id="zn-from" type="text" inputmode="numeric" maxlength="9" wire:model="cep_from" placeholder="14000-000" style="{{ $in }}">
                            @error('cep_from') <p class="pay-err">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="settings-field-label" for="zn-to">CEP final</label>
                            <input id="zn-to" type="text" inputmode="numeric" maxlength="9" wire:model="cep_to" placeholder="14099-999" style="{{ $in }}">
                            @error('cep_to') <p class="pay-err">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div>
                            <label class="settings-field-label" for="zn-city">Cidade</label>
                            <input id="zn-city" type="text" wire:model="city" maxlength="80" placeholder="Ribeirão Preto" style="{{ $in }}">
                            @error('city') <p class="pay-err">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="settings-field-label" for="zn-uf">Estado (sigla)</label>
                            <input id="zn-uf" type="text" wire:model="uf" maxlength="2" placeholder="SP" style="{{ $in }};text-transform:uppercase">
                            @error('uf') <p class="pay-err">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div>
                        <label class="settings-field-label" for="zn-fee">Frete (R$)</label>
                        <input id="zn-fee" type="text" inputmode="decimal" wire:model="fee" placeholder="8,00 (0 = grátis)" style="{{ $in }}">
                        @error('fee') <p class="pay-err">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="settings-field-label" for="zn-free">Grátis acima de (R$, opcional)</label>
                        <input id="zn-free" type="text" inputmode="decimal" wire:model="free_above" placeholder="150,00" style="{{ $in }}">
                        @error('free_above') <p class="pay-err">{{ $message }}</p> @enderror
                    </div>
                    <div style="grid-column:1/-1">
                        <label class="settings-field-label" for="zn-eta">Prazo (opcional)</label>
                        <input id="zn-eta" type="text" wire:model="eta" maxlength="60" placeholder="Ex.: no mesmo dia, 1 a 2 dias úteis" style="{{ $in }}">
                    </div>
                </div>
                <p style="margin:0;font-size:.75rem;color:#64748b"><i class="bi bi-lightbulb"></i> Uma faixa de CEP mais estreita ganha de outra maior e de uma cidade inteira. Os 5 primeiros números do CEP costumam ser o bairro.</p>
                <div style="display:flex;gap:.5rem;justify-content:flex-end">
                    <button type="button" wire:click="$set('showForm', false)" class="pay-test-btn">Cancelar</button>
                    <button type="button" wire:click="save" class="settings-save-btn" data-testid="zone-save"><i class="bi bi-check2"></i> Salvar região</button>
                </div>
            </div>
        @endif

        @if($zones->isNotEmpty())
            <div>
                <label class="settings-field-label" for="zn-out">CEP fora das regiões</label>
                <select id="zn-out" wire:model.live="outside" style="{{ $in }}" data-testid="zone-outside">
                    <option value="fee">Cobrar a taxa padrão ({{ $money($defaultFee) }})</option>
                    <option value="none">Não entregar (o cliente só pode retirar)</option>
                </select>
            </div>
        @endif

        <form wire:submit="test" style="display:flex;gap:.5rem;align-items:flex-end;flex-wrap:wrap">
            <div style="flex:1;min-width:160px">
                <label class="settings-field-label" for="zn-test">Testar um CEP</label>
                <input id="zn-test" type="text" inputmode="numeric" maxlength="9" wire:model="testCep" placeholder="00000-000" style="{{ $in }}" data-testid="zone-test-cep">
            </div>
            <button type="submit" class="pay-test-btn" data-testid="zone-test"><i class="bi bi-calculator"></i> <span wire:loading.remove wire:target="test">Calcular</span><span wire:loading wire:target="test">...</span></button>
        </form>
        @if($testResult)
            <p style="margin:0;font-size:.85rem;padding:.6rem .75rem;border-radius:.6rem;{{ $testResult['ok'] ? 'background:rgba(16,185,129,.1);color:#047857' : 'background:rgba(244,63,94,.08);color:#be123c' }}" data-testid="zone-test-result">
                @if($testResult['ok'])
                    <b>{{ $testResult['fee'] > 0 ? $money($testResult['fee']) : 'Frete grátis' }}</b> para {{ $testResult['place'] }}
                    · {{ $testResult['zone'] ? 'região ' . $testResult['zone'] : 'taxa padrão' }}@if($testResult['freeAbove']) · grátis acima de {{ $money($testResult['freeAbove']) }}@endif
                @else
                    {{ $testResult['message'] }}
                @endif
            </p>
        @endif
    </div>
</div>
