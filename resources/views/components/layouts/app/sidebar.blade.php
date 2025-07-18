<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard.index') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Dashboards')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard.index')" :current="request()->routeIs('dashboard.index')" wire:navigate>{{ __('Dashboard Geral') }}</flux:navlist.item>
                    <flux:navlist.item icon="chart-bar" :href="route('dashboard.cashbook')" :current="request()->routeIs('dashboard.cashbook')" wire:navigate>{{ __('Dashboard Financeiro') }}</flux:navlist.item>
                    <flux:navlist.item icon="shopping-bag" :href="route('dashboard.products')" :current="request()->routeIs('dashboard.products')" wire:navigate>{{ __('Dashboard Produtos') }}</flux:navlist.item>
                    <flux:navlist.item icon="currency-dollar" :href="route('dashboard.sales')" :current="request()->routeIs('dashboard.sales')" wire:navigate>{{ __('Dashboard Vendas') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Financeiro')">
                    <flux:navlist.item icon="credit-card" :href="url('banks')" :current="Request::is('banks')" wire:navigate>Bancos</flux:navlist.item>
                    <flux:navlist.item icon="document-text" :href="url('invoices')" :current="Request::is('invoices*')" wire:navigate>Transações</flux:navlist.item>
                    <flux:navlist.item icon="wallet" :href="url('cashbook')" :current="Request::is('cashbook')" wire:navigate>Livro Caixa</flux:navlist.item>
                    <flux:navlist.item icon="banknotes" :href="url('cofrinho')" :current="Request::is('cofrinho')" wire:navigate>Cofrinhos</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Vendas e Produtos')">
                    <flux:navlist.item icon="cube" :href="url('products')" :current="Request::is('products')" wire:navigate>Produtos</flux:navlist.item>
                    <flux:navlist.item icon="users" :href="url('clients')" :current="Request::is('clients')" wire:navigate>Clientes</flux:navlist.item>
                    <flux:navlist.item icon="shopping-cart" :href="url('sales')" :current="Request::is('sales')" wire:navigate>Vendas</flux:navlist.item>
                    <flux:navlist.item icon="tag" :href="url('categories')" :current="Request::is('categories')" wire:navigate>Categorias</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repositório') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentação') }}
                </flux:navlist.item>
            </flux:navlist>

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Configurações') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Sair') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Configurações') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Sair') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
