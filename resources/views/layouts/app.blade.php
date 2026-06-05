<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} — GRC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-slate-100 text-slate-900 antialiased" x-data="{ sidebarOpen: false }">

{{-- Mobile overlay --}}
<div x-show="sidebarOpen" @click="sidebarOpen = false"
     x-transition:enter="transition-opacity ease-linear duration-200"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-200"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/50 z-20 lg:hidden" style="display:none"></div>

{{-- Sidebar --}}
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 w-64 bg-slate-900 text-slate-100 flex flex-col z-30 transform transition-transform duration-200 ease-in-out lg:translate-x-0">

    {{-- Logo --}}
    <div class="flex items-center gap-2 px-5 py-4 border-b border-slate-700">
        <span class="w-3 h-3 rounded-full bg-emerald-400 flex-shrink-0"></span>
        <span class="text-lg font-bold tracking-tight">GRC Platform</span>
    </div>

    {{-- Nav --}}
    @auth
    <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5 text-sm">

        {{-- Overview --}}
        <div x-data="{ open: {{ request()->routeIs('dashboard') || request()->routeIs('org-metrics.*') ? 'true' : 'true' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded text-slate-300 hover:bg-slate-800 hover:text-white font-semibold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="flex-1 text-left">Przegląd</span>
                <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" class="ml-2 mt-0.5 space-y-0.5">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}">Dashboard</a>
                <a href="{{ route('org-metrics.index') }}" class="nav-link {{ request()->routeIs('org-metrics.*') ? 'nav-active' : '' }}">Metryki org.</a>
            </div>
        </div>

        {{-- Ryzyka --}}
        <div x-data="{ open: {{ request()->routeIs('risks.*') || request()->routeIs('assets.*') || request()->routeIs('scenarios.*') || request()->routeIs('controls.*') || request()->routeIs('indicators.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded text-slate-300 hover:bg-slate-800 hover:text-white font-semibold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="flex-1 text-left">Ryzyka</span>
                <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" class="ml-2 mt-0.5 space-y-0.5">
                <a href="{{ route('risks.index') }}" class="nav-link {{ request()->routeIs('risks.*') ? 'nav-active' : '' }}">Rejestr ryzyk</a>
                <a href="{{ route('scenarios.index') }}" class="nav-link {{ request()->routeIs('scenarios.*') ? 'nav-active' : '' }}">Scenariusze</a>
                <a href="{{ route('assets.index') }}" class="nav-link {{ request()->routeIs('assets.*') ? 'nav-active' : '' }}">Aktywa</a>
                <a href="{{ route('controls.index') }}" class="nav-link {{ request()->routeIs('controls.*') ? 'nav-active' : '' }}">Kontrole</a>
                <a href="{{ route('controls.soa') }}" class="nav-link {{ request()->routeIs('controls.soa') ? 'nav-active' : '' }}">SoA</a>
                <a href="{{ route('indicators.index') }}" class="nav-link {{ request()->routeIs('indicators.*') ? 'nav-active' : '' }}">Wskaźniki</a>
            </div>
        </div>

        {{-- Cyberbezpieczeństwo --}}
        <div x-data="{ open: {{ request()->routeIs('vulnerabilities.*') || request()->routeIs('incidents.*') || request()->routeIs('nis2.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded text-slate-300 hover:bg-slate-800 hover:text-white font-semibold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span class="flex-1 text-left">Cyberbezpieczeństwo</span>
                <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" class="ml-2 mt-0.5 space-y-0.5">
                <a href="{{ route('vulnerabilities.index') }}" class="nav-link {{ request()->routeIs('vulnerabilities.*') ? 'nav-active' : '' }}">Podatności</a>
                <a href="{{ route('incidents.index') }}" class="nav-link {{ request()->routeIs('incidents.*') ? 'nav-active' : '' }}">Incydenty</a>
                <a href="{{ route('nis2.index') }}" class="nav-link {{ request()->routeIs('nis2.*') ? 'nav-active' : '' }}">NIS2</a>
                <a href="{{ route('certificates.index') }}" class="nav-link {{ request()->routeIs('certificates.*') || request()->routeIs('crypto-keys.*') ? 'nav-active' : '' }}">Certyfikaty / Klucze</a>
            </div>
        </div>

        {{-- Audyt i Zgodność --}}
        <div x-data="{ open: {{ request()->routeIs('engagements.*') || request()->routeIs('findings.*') || request()->routeIs('reports.*') || request()->routeIs('exceptions.*') || request()->routeIs('bcp.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded text-slate-300 hover:bg-slate-800 hover:text-white font-semibold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span class="flex-1 text-left">Audyt i Zgodność</span>
                <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" class="ml-2 mt-0.5 space-y-0.5">
                <a href="{{ route('engagements.index') }}" class="nav-link {{ request()->routeIs('engagements.*') ? 'nav-active' : '' }}">Audyty</a>
                <a href="{{ route('findings.index') }}" class="nav-link {{ request()->routeIs('findings.*') ? 'nav-active' : '' }}">Findings</a>
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'nav-active' : '' }}">Raporty</a>
                <a href="{{ route('exceptions.index') }}" class="nav-link {{ request()->routeIs('exceptions.*') ? 'nav-active' : '' }}">Wyjątki</a>
                <a href="{{ route('bcp.index') }}" class="nav-link {{ request()->routeIs('bcp.*') ? 'nav-active' : '' }}">BCP / DR</a>
            </div>
        </div>

        {{-- RODO --}}
        @canany(['rcp.view','gdpr_breach.view','dpia.view','dsar.view'])
        <div x-data="{ open: {{ request()->routeIs('rcp.*') || request()->routeIs('gdpr-breaches.*') || request()->routeIs('dpias.*') || request()->routeIs('dsar.*') || request()->routeIs('third-parties.*') || request()->routeIs('policies.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded text-slate-300 hover:bg-slate-800 hover:text-white font-semibold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                <span class="flex-1 text-left">RODO / GDPR</span>
                <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" class="ml-2 mt-0.5 space-y-0.5">
                @can('rcp.view')<a href="{{ route('rcp.index') }}" class="nav-link {{ request()->routeIs('rcp.*') ? 'nav-active' : '' }}">Rejestr czynności</a>@endcan
                @can('gdpr_breach.view')<a href="{{ route('gdpr-breaches.index') }}" class="nav-link {{ request()->routeIs('gdpr-breaches.*') ? 'nav-active' : '' }}">Naruszenia RODO</a>@endcan
                @can('dpia.view')<a href="{{ route('dpias.index') }}" class="nav-link {{ request()->routeIs('dpias.*') ? 'nav-active' : '' }}">DPIA</a>@endcan
                @can('dsar.view')<a href="{{ route('dsar.index') }}" class="nav-link {{ request()->routeIs('dsar.*') ? 'nav-active' : '' }}">Wnioski DSAR</a>@endcan
                @can('third_party.view')<a href="{{ route('third-parties.index') }}" class="nav-link {{ request()->routeIs('third-parties.*') ? 'nav-active' : '' }}">Strony trzecie</a>@endcan
                @can('policy.view')<a href="{{ route('policies.index') }}" class="nav-link {{ request()->routeIs('policies.*') ? 'nav-active' : '' }}">Polityki</a>@endcan
            </div>
        </div>
        @endcanany

        {{-- Szkolenia --}}
        <div x-data="{ open: {{ request()->routeIs('trainings.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded text-slate-300 hover:bg-slate-800 hover:text-white font-semibold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span class="flex-1 text-left">Szkolenia</span>
                <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" class="ml-2 mt-0.5 space-y-0.5">
                <a href="{{ route('trainings.index') }}" class="nav-link {{ request()->routeIs('trainings.index') || request()->routeIs('trainings.create') || request()->routeIs('trainings.edit') || request()->routeIs('trainings.show') ? 'nav-active' : '' }}">Moduły szkoleń</a>
                <a href="{{ route('trainings.report') }}" class="nav-link {{ request()->routeIs('trainings.report') ? 'nav-active' : '' }}">Raport ukończenia</a>
            </div>
        </div>

        {{-- TPRM i Ankiety --}}
        <div x-data="{ open: {{ request()->routeIs('vendor-assessments.*') || request()->routeIs('mcr.*') || request()->routeIs('questionnaires.*') || request()->routeIs('answer-library.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded text-slate-300 hover:bg-slate-800 hover:text-white font-semibold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="flex-1 text-left">TPRM i Ankiety</span>
                <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" class="ml-2 mt-0.5 space-y-0.5">
                <a href="{{ route('vendor-assessments.index') }}" class="nav-link {{ request()->routeIs('vendor-assessments.*') ? 'nav-active' : '' }}">Oceny dostawców</a>
                <a href="{{ route('mcr.index') }}" class="nav-link {{ request()->routeIs('mcr.*') ? 'nav-active' : '' }}">MCR</a>
                <a href="{{ route('questionnaires.index') }}" class="nav-link {{ request()->routeIs('questionnaires.*') ? 'nav-active' : '' }}">Ankiety klientów</a>
                <a href="{{ route('answer-library.index') }}" class="nav-link {{ request()->routeIs('answer-library.*') ? 'nav-active' : '' }}">Baza odpowiedzi</a>
            </div>
        </div>

        {{-- Admin --}}
        @role('admin')
        <div x-data="{ open: {{ request()->routeIs('admin.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded text-slate-300 hover:bg-slate-800 hover:text-white font-semibold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="flex-1 text-left">Administracja</span>
                <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-show="open" class="ml-2 mt-0.5 space-y-0.5">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-active' : '' }}">Użytkownicy</a>
            </div>
        </div>
        @endrole

    </nav>
    @endauth

    {{-- User footer --}}
    @auth
    <div class="border-t border-slate-700 px-3 py-3" x-data="{ userMenuOpen: false }">
        <button @click="userMenuOpen = !userMenuOpen"
                class="w-full flex items-center gap-2.5 px-2 py-2 rounded hover:bg-slate-800 relative">
            <span class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center text-xs font-bold flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email, 0, 2)) }}
            </span>
            <div class="flex-1 text-left min-w-0">
                <div class="text-sm font-medium truncate">{{ auth()->user()->name ?? auth()->user()->email }}</div>
                <div class="text-xs text-slate-400 truncate">{{ auth()->user()->getRoleNames()->first() ?? 'user' }}</div>
            </div>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
        </button>
        {{-- User popout --}}
        <div x-show="userMenuOpen" @click.away="userMenuOpen = false"
             class="absolute bottom-16 left-3 right-3 bg-slate-800 border border-slate-600 rounded shadow-xl py-1 z-50"
             style="display:none">
            <a href="{{ route('mfa.setup') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-700">
                @if(auth()->user()->hasMfaEnabled())
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span><span>MFA aktywne</span>
                @else
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span><span>Skonfiguruj MFA</span>
                @endif
            </a>
            <hr class="border-slate-600 my-1">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-700 text-left">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Wyloguj
                </button>
            </form>
        </div>
    </div>
    @endauth
</aside>

{{-- Main area --}}
<div class="lg:pl-64 flex flex-col min-h-screen">

    {{-- Top bar --}}
    <header class="sticky top-0 z-10 bg-white border-b border-slate-200 shadow-sm">
        <div class="px-4 py-2.5 flex items-center justify-between gap-4">
            {{-- Mobile hamburger --}}
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-1.5 rounded hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            {{-- Breadcrumb / page title --}}
            <div class="flex-1 text-sm text-slate-500 truncate hidden md:block">
                GRC Platform &rsaquo; <span class="text-slate-900">{{ $title ?? 'Dashboard' }}</span>
            </div>

            {{-- Quick actions --}}
            @auth
            <div class="flex items-center gap-2">
                <a href="{{ route('incidents.create') }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    Incydent
                </a>
                <a href="{{ route('risks.create') }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Ryzyko
                </a>
                <span class="hidden md:inline text-xs text-slate-400 pl-2">{{ now()->format('d.m.Y H:i') }}</span>
            </div>
            @endauth
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('status') || session('success') || session('warning') || session('error'))
        <div class="px-6 pt-4">
            @if(session('status') || session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-2 rounded text-sm">{{ session('status') ?? session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-2 rounded text-sm">{{ session('warning') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-900 px-4 py-2 rounded text-sm">{{ session('error') }}</div>
            @endif
        </div>
    @endif

    {{-- Page content --}}
    <main class="flex-1 px-6 py-6">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white py-2 text-xs text-slate-400 px-6 flex items-center justify-between">
        <span>GRC Platform &copy; {{ now()->year }}</span>
        <span>v1.0</span>
    </footer>
</div>

{{-- Tailwind nav utility classes --}}
<style>
    .nav-link { display: block; padding: 0.375rem 0.75rem; border-radius: 0.25rem; color: #cbd5e1; font-size: 0.8125rem; }
    .nav-link:hover { background: #1e293b; color: #f1f5f9; }
    .nav-active { background: #0f172a !important; color: #ffffff !important; font-weight: 500; }
</style>

@stack('scripts')
</body>
</html>
