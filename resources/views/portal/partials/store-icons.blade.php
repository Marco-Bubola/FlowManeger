{{-- Ícone da aba e da tela de início com a logo da loja (Vitrine › Informações da loja).
     Sem logo (ou sem loja), ficam os ícones do FlowManager. --}}
@php $iconOwner = (int) ($owner ?? 0); $icon32 = \App\Services\Portal\StoreIcon::url($iconOwner ?: null, 32); @endphp
@if($icon32)
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $icon32 }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ \App\Services\Portal\StoreIcon::url($iconOwner, 16) }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ \App\Services\Portal\StoreIcon::url($iconOwner, 180) }}">
    <link rel="manifest" href="{{ route('portal.manifest', ['userId' => $iconOwner]) }}">
    <meta name="apple-mobile-web-app-title" content="{{ mb_strimwidth((string) (\App\Services\Portal\StoreIdentity::for($iconOwner)['name'] ?? ''), 0, 14, '') }}">
@else
    @include('partials.favicons')
@endif
