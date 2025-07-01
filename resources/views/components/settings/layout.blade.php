<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('settings.profile')" wire:navigate>
                <span class="flex items-center gap-2">
                    <!-- Ícone de usuário -->
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5a8.25 8.25 0 1115 0v.75A2.25 2.25 0 0117.25 22.5h-10.5A2.25 2.25 0 014.5 20.25v-.75z" />
                    </svg>
                    {{ __('Perfil') }}
                </span>
            </flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>
                <span class="flex items-center gap-2">
                    <!-- Ícone de cadeado -->
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5A4.5 4.5 0 0 0 7.5 7.5v3m9 0A2.25 2.25 0 0 1 18.75 12.75v6A2.25 2.25 0 0 1 16.5 21H7.5A2.25 2.25 0 0 1 5.25 18.75v-6A2.25 2.25 0 0 1 7.5 10.5m9 0h-9" />
                    </svg>
                    {{ __('Senha') }}
                </span>
            </flux:navlist.item>
            <flux:navlist.item :href="route('settings.appearance')" wire:navigate>
                <span class="flex items-center gap-2">
                    <!-- Ícone de paleta de cores -->
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3C7.03 3 3 7.03 3 12c0 2.21 1.79 4 4 4h1a1 1 0 0 1 1 1v1c0 2.21 1.79 4 4 4s4-1.79 4-4c0-4.97-4.03-9-9-9z" />
                        <circle cx="8.5" cy="10.5" r="1.5" />
                        <circle cx="15.5" cy="10.5" r="1.5" />
                        <circle cx="12" cy="15.5" r="1.5" />
                    </svg>
                    {{ __('Aparência') }}
                </span>
            </flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full">
            {{ $slot }}
        </div>
    </div>
</div>