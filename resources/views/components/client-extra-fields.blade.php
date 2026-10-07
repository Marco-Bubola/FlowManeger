{{--
  Documento, aniversário e endereço por partes no cadastro interno do cliente.
  Máscaras de CPF/CNPJ e CEP em Alpine; o CEP busca o endereço no ViaCEP.
  Usa wire:model direto: precisa estar dentro de um componente com o trait
  App\Livewire\Clients\Concerns\HasClientExtraFields.
--}}
@php
    $input = 'w-full px-4 py-3 rounded-xl border-2 border-slate-200/50 dark:border-slate-600/50 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all';
    $label = 'block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2';
@endphp
<div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-6 gap-4 pt-2"
    x-data="{
        loadingCep: false,
        cepError: '',
        maskDoc(v) {
            const d = v.replace(/\D/g, '').slice(0, 14);
            if (d.length <= 11) {
                return d.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            return d.replace(/^(\d{2})(\d)/, '$1.$2').replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3').replace(/\.(\d{3})(\d)/, '.$1/$2').replace(/(\d{4})(\d)/, '$1-$2');
        },
        maskCep(v) {
            const d = v.replace(/\D/g, '').slice(0, 8);
            return d.length > 5 ? d.slice(0, 5) + '-' + d.slice(5) : d;
        },
        async lookupCep(v) {
            const d = v.replace(/\D/g, '');
            this.cepError = '';
            if (d.length !== 8) return;
            this.loadingCep = true;
            try {
                const r = await fetch('https://viacep.com.br/ws/' + d + '/json/');
                const j = await r.json();
                if (j.erro) { this.cepError = 'CEP não encontrado.'; return; }
                $wire.set('street', j.logradouro || '', false);
                $wire.set('neighborhood', j.bairro || '', false);
                $wire.set('city', j.localidade || '', false);
                $wire.set('state', j.uf || '');
                this.$nextTick(() => this.$refs.number?.focus());
            } catch (e) {
                this.cepError = 'Não foi possível buscar o CEP agora. Preencha à mão.';
            } finally {
                this.loadingCep = false;
            }
        }
    }">
    <div class="md:col-span-6 flex items-center gap-2 text-sm font-bold text-slate-600 dark:text-slate-300 border-t border-slate-200 dark:border-slate-700 pt-4">
        <i class="bi bi-person-vcard text-indigo-500"></i> Dados complementares <span class="font-normal text-slate-400">(opcionais)</span>
    </div>

    <div class="md:col-span-2">
        <label for="cpf_cnpj" class="{{ $label }}">CPF ou CNPJ</label>
        <input id="cpf_cnpj" type="text" inputmode="numeric" wire:model.blur="cpf_cnpj" placeholder="000.000.000-00"
            x-on:input="$event.target.value = maskDoc($event.target.value)" class="{{ $input }}">
        @error('cpf_cnpj') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="birth_date" class="{{ $label }}">Data de nascimento</label>
        <input id="birth_date" type="date" wire:model="birth_date" class="{{ $input }}">
        @error('birth_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="company" class="{{ $label }}">Empresa</label>
        <input id="company" type="text" wire:model="company" class="{{ $input }}">
    </div>

    <div class="md:col-span-2">
        <label for="cep" class="{{ $label }}">CEP</label>
        <div class="relative">
            <input id="cep" type="text" inputmode="numeric" wire:model="cep" placeholder="00000-000"
                x-on:input="$event.target.value = maskCep($event.target.value); if ($event.target.value.length === 9) lookupCep($event.target.value)"
                class="{{ $input }}">
            <i x-show="loadingCep" x-cloak class="bi bi-arrow-repeat animate-spin absolute right-3 top-1/2 -translate-y-1/2 text-indigo-500"></i>
        </div>
        <p x-show="cepError" x-cloak x-text="cepError" class="mt-1 text-xs text-amber-600"></p>
        @error('cep') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-4">
        <label for="street" class="{{ $label }}">Rua</label>
        <input id="street" type="text" wire:model="street" class="{{ $input }}">
    </div>
    <div class="md:col-span-1">
        <label for="number" class="{{ $label }}">Número</label>
        <input id="number" x-ref="number" type="text" wire:model="number" class="{{ $input }}">
    </div>
    <div class="md:col-span-2">
        <label for="complement" class="{{ $label }}">Complemento</label>
        <input id="complement" type="text" wire:model="complement" class="{{ $input }}">
    </div>
    <div class="md:col-span-3">
        <label for="neighborhood" class="{{ $label }}">Bairro</label>
        <input id="neighborhood" type="text" wire:model="neighborhood" class="{{ $input }}">
    </div>
    <div class="md:col-span-4">
        <label for="city" class="{{ $label }}">Cidade</label>
        <input id="city" type="text" wire:model="city" class="{{ $input }}">
    </div>
    <div class="md:col-span-2">
        <label for="state" class="{{ $label }}">UF</label>
        <input id="state" type="text" maxlength="2" wire:model="state" class="{{ $input }} uppercase">
    </div>
</div>
