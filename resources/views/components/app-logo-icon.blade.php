{{-- Logo do app (o "F" nas cores rosa, roxo e azul). --}}
<img src="{{ Vite::asset('resources/images/icons/logo-128.png') }}" alt="{{ config('app.name', 'FlowManager') }}" {{ $attributes->merge(['class' => 'object-contain']) }} />
