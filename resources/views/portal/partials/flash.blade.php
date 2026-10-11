{{-- Avisos da última ação (sucesso, erro, campos inválidos), no visual do catálogo. --}}
@if(session('success'))
    <div class="po-flash"><i class="fas fa-circle-check" style="margin-top:2px"></i><span>{{ session('success') }}</span></div>
@endif
@if(session('error') || session('info'))
    <div class="po-flash err"><i class="fas fa-circle-exclamation" style="margin-top:2px"></i><span>{{ session('error') ?: session('info') }}</span></div>
@endif
@if($errors->any())
    <div class="po-flash err"><i class="fas fa-circle-exclamation" style="margin-top:2px"></i>
        <span>@if($errors->count() === 1){{ $errors->first() }}@else Confira os campos marcados: {{ implode(' ', array_slice($errors->all(), 0, 3)) }}@endif</span>
    </div>
@endif
