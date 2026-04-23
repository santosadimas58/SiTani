<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="{
        sidebarCollapsed: JSON.parse(localStorage.getItem('hydrowatch-sidebar-collapsed') ?? 'false'),
        setSidebarCollapsed(value) {
            this.sidebarCollapsed = value;
            localStorage.setItem('hydrowatch-sidebar-collapsed', JSON.stringify(value));
        },
        toggleSidebar() {
            this.setSidebarCollapsed(!this.sidebarCollapsed);
        }
    }"
    class="hydrowatch-app min-h-screen font-sans antialiased"
>
    <x-nav sticky class="hydrowatch-topbar lg:hidden">
        <x-slot:brand>
            @php
                $homeUrl = '/dashboard';
            @endphp
            <a href="{{ $homeUrl }}" class="hydrowatch-brand px-2">
                <span class="hydrowatch-brand-mark">
                    <svg width="22" height="22" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 55 Q20 35 40 28 Q60 35 60 55" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                        <line x1="40" y1="55" x2="40" y2="65" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                        <path d="M20 62 Q30 57 40 65 Q50 57 60 62" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>
                    <span class="hydrowatch-brand-title">{{ config('app.name') }}</span>
                    <span class="hydrowatch-brand-subtitle">Smart irrigation command</span>
                </span>
            </a>
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer text-green-900" />
            </label>
        </x-slot:actions>
    </x-nav>

    <main class="hydrowatch-main">
        <div class="drawer lg:drawer-open hydrowatch-drawer">
            <input id="main-drawer" type="checkbox" class="drawer-toggle" />

            <div class="drawer-content hydrowatch-main-content" :class="{ 'hydrowatch-main-content-collapsed': sidebarCollapsed }">
                <div class="hydrowatch-content-shell">
                    <div class="hydrowatch-content-panel">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <div class="drawer-side hydrowatch-drawer-side">
                <label for="main-drawer" aria-label="close sidebar" class="drawer-overlay"></label>

                <aside class="hydrowatch-sidebar" :class="{ 'hydrowatch-sidebar-collapsed': sidebarCollapsed }">
                    @php
                        $homeUrl = '/dashboard';
                    @endphp
                    <a href="{{ $homeUrl }}" class="hydrowatch-brand px-1 pt-1 pb-5">
                        <span class="hydrowatch-brand-mark">
                            <svg width="22" height="22" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 55 Q20 35 40 28 Q60 35 60 55" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                <line x1="40" y1="55" x2="40" y2="65" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                                <path d="M20 62 Q30 57 40 65 Q50 57 60 62" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="hydrowatch-brand-title">{{ config('app.name') }}</span>
                            <span class="hydrowatch-brand-subtitle">Smart irrigation command</span>
                        </span>
                    </a>

                    <x-menu activate-by-route class="hydrowatch-menu">
                        @if($user = auth()->user())
                            <x-menu-separator />
                            <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="hydrowatch-user-card">
                                <x-slot:actions>
                                    <x-button icon="o-power" class="btn-circle btn-ghost btn-xs hydrowatch-user-logout" tooltip-left="logoff" no-wire-navigate link="/logout" />
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

                    <button
                        type="button"
                        class="hydrowatch-sidebar-toggle"
                        @click="toggleSidebar()"
                        x-bind:aria-label="sidebarCollapsed ? 'Perbesar sidebar' : 'Kecilkan sidebar'"
                    >
                        <x-icon name="o-bars-3-bottom-left" class="w-5 h-5" />
                        <span class="mary-hideable" x-text="sidebarCollapsed ? 'Perbesar' : 'Kecilkan'"></span>
                    </button>
                </aside>
            </div>
        </div>
    </main>

    <x-toast />
</body>
</html>
