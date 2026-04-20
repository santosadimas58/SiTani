<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            @php
                $homeUrl = '/dashboard';
            @endphp
            <a href="{{ $homeUrl }}" class="flex items-center gap-2 px-2">
                <x-icon name="o-beaker" class="w-7 h-7 text-primary" />
                <span class="font-black text-lg">{{ config('app.name') }}</span>
            </a>
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    <x-main>
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">
            {{-- BRAND --}}
            @php
                $homeUrl = '/dashboard';
            @endphp
            <a href="{{ $homeUrl }}" class="flex items-center gap-2 px-5 pt-4 pb-2">
                <x-icon name="o-beaker" class="w-7 h-7 text-primary" />
                <span class="font-black text-lg">{{ config('app.name') }}</span>
            </a>

       <x-menu activate-by-route>
    @if($user = auth()->user())
        <x-menu-separator />
            <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                <x-slot:actions>
                <x-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff" no-wire-navigate link="/logout" />
                </x-slot:actions>
            </x-list-item>
            <x-menu-separator />
    @endif

        <x-menu-item title="Dashboard" icon="o-home" link="/dashboard" />
            <x-menu-separator />
                <x-menu-item title="Monitoring" icon="o-signal" link="/monitoring" />
                <x-menu-item title="Riwayat Sensor" icon="o-chart-bar" link="/history" />
                <x-menu-separator />
                <x-menu-item title="Kontrol Pompa" icon="o-bolt" link="/pump" />
                <x-menu-item title="Kelola Node" icon="o-server" link="/nodes" />
                <x-menu-separator />
                <x-menu-item title="Profil" icon="o-user-circle" link="/profile" />
            </x-menu>
        </x-slot:sidebar>

        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    <x-toast />
</body>
</html>
