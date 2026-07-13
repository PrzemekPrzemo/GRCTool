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
<body class="h-full bg-slate-100 text-slate-900 antialiased"
      x-data="{
          sidebarOpen: false,
          deskOpen: localStorage.getItem('grc-sidebar-open') !== '0',
          toggleDesk() {
              this.deskOpen = !this.deskOpen;
              localStorage.setItem('grc-sidebar-open', this.deskOpen ? '1' : '0');
          },
      }">

{{-- Mobile overlay --}}
<div x-show="sidebarOpen" @click="sidebarOpen = false"
     class="fixed inset-0 bg-black/50 z-20 lg:hidden"
     x-transition:enter="transition-opacity duration-200"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity duration-200"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

{{-- SIDEBAR --}}
<aside class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 text-slate-100 flex flex-col transition-transform duration-200"
       :class="[
           sidebarOpen ? 'translate-x-0' : '-translate-x-full',
           deskOpen ? 'lg:translate-x-0' : 'lg:-translate-x-full',
       ]">

    {{-- Logo --}}
    <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-700/60">
        <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.955 11.955 0 003 12c0 6.627 5.373 12 12 12s12-5.373 12-12c0-2.06-.52-3.999-1.436-5.685"/>
            </svg>
        </div>
        <div>
            <div class="font-bold text-sm leading-tight">GRC Platform</div>
            <div class="text-xs text-slate-400 leading-tight">Cyber Risk & Audit</div>
        </div>
    </div>

    {{-- Navigation --}}
    @auth
    <nav class="flex-1 overflow-y-auto py-3 text-sm scrollbar-thin">

        {{-- Overview --}}
        <div class="px-3 mb-1">
            <p class="px-2 py-1 text-xs font-semibold text-slate-500 uppercase tracking-widest">Przegląd</p>
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
            @can('compliance.view')
            <a href="{{ route('dashboard.executive') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('dashboard.executive') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h12M3.75 3h-1.5M3.75 3H15m6 13.5V6a2.25 2.25 0 00-2.25-2.25H15M21 16.5H3m18 0v.75A2.25 2.25 0 0118.75 19.5H5.25A2.25 2.25 0 013 17.25v-.75M15 3.75v3.75m0 0h3.75M15 7.5l5.25-5.25"/></svg>
                <span>Dashboard CSO/Zarządu</span>
            </a>
            @endcan
            @can('indicator.view')
            <a href="{{ route('org-metrics.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('org-metrics.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Metryki org.</span>
            </a>
            @endcan
        </div>

        {{-- Risk & Controls --}}
        <div class="px-3 mb-1" x-data="{ open: {{ request()->routeIs('risks.*','controls.*','indicators.*','scenarios.*','risk-treatment-plans.*','risk-acceptances.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-2 py-1 text-xs font-semibold text-slate-500 uppercase tracking-widest hover:text-slate-300 transition-colors">
                <span>Ryzyko & Kontrole</span>
                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition class="space-y-0.5">
                <a href="{{ route('risks.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('risks.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Ryzyka</span>
                </a>
                @can('risk.view')
                <a href="{{ route('risk-treatment-plans.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('risk-treatment-plans.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Plany leczenia</span>
                </a>
                <a href="{{ route('risk-acceptances.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('risk-acceptances.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Akceptacje ryzyk</span>
                </a>
                @endcan
                <a href="{{ route('scenarios.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('scenarios.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Scenariusze</span>
                </a>
                <a href="{{ route('controls.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('controls.index','controls.show','controls.create','controls.edit','controls.soa') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Kontrole</span>
                </a>
                @can('control.view')
                <a href="{{ route('controls.cross-mapping') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('controls.cross-mapping') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>Macierz pokrycia</span>
                </a>
                @endcan
                <a href="{{ route('indicators.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('indicators.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    <span>Wskaźniki KPI/KRI</span>
                </a>
            </div>
        </div>

        {{-- Organizacja --}}
        @canany(['client.view','business_unit.view','project.view'])
        <div class="px-3 mb-1" x-data="{ open: {{ request()->routeIs('clients.*','business-units.*','projects.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-2 py-1 text-xs font-semibold text-slate-500 uppercase tracking-widest hover:text-slate-300 transition-colors">
                <span>Organizacja</span>
                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition class="space-y-0.5">
                @can('client.view')
                <a href="{{ route('clients.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('clients.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Klienci</span>
                </a>
                @endcan
                @can('business_unit.view')
                <a href="{{ route('business-units.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('business-units.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <span>Jednostki bizn.</span>
                </a>
                @endcan
                @can('project.view')
                <a href="{{ route('projects.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('projects.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Projekty</span>
                </a>
                @endcan
            </div>
        </div>
        @endcanany

        {{-- Assets & Vulnerabilities --}}
        <div class="px-3 mb-1" x-data="{ open: {{ request()->routeIs('assets.*','vulnerabilities.*','certificates.*','crypto-keys.*','evidence.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-2 py-1 text-xs font-semibold text-slate-500 uppercase tracking-widest hover:text-slate-300 transition-colors">
                <span>Aktywa & Luki</span>
                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition class="space-y-0.5">
                <a href="{{ route('assets.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('assets.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
                    <span>Aktywa</span>
                </a>
                @can('vulnerability.view')
                <a href="{{ route('vulnerabilities.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('vulnerabilities.index') || request()->routeIs('vulnerabilities.show') || request()->routeIs('vulnerabilities.create') || request()->routeIs('vulnerabilities.edit') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    <span>Podatności</span>
                </a>
                <a href="{{ route('vulnerabilities.sla') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('vulnerabilities.sla') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>SLA podatności</span>
                </a>
                @endcan
                @can('certificate.view')
                <a href="{{ route('certificates.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('certificates.*','crypto-keys.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span>Certyfikaty & Klucze</span>
                </a>
                @endcan
                @can('evidence.view')
                <a href="{{ route('evidence.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('evidence.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Dowody</span>
                </a>
                @endcan
            </div>
        </div>

        {{-- Incidents & Continuity --}}
        @canany(['incident.view','bcp.view','nis2.view'])
        <div class="px-3 mb-1" x-data="{ open: {{ request()->routeIs('incidents.*','bcp.*','nis2.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-2 py-1 text-xs font-semibold text-slate-500 uppercase tracking-widest hover:text-slate-300 transition-colors">
                <span>Incydenty & Ciągłość</span>
                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition class="space-y-0.5">
                @can('incident.view')
                <a href="{{ route('incidents.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('incidents.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span>Incydenty</span>
                </a>
                <a href="{{ route('security-overview.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('security-overview.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 4v5c0 4.418-2.865 8.418-7 9.5-4.135-1.082-7-5.082-7-9.5V7l7-4z"/></svg>
                    <span>Security Overview</span>
                </a>
                @endcan
                @can('bcp.view')
                <a href="{{ route('bcp.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('bcp.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>BCP / DR</span>
                </a>
                @endcan
                @can('nis2.view')
                <a href="{{ route('nis2.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('nis2.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>NIS2</span>
                </a>
                @endcan
            </div>
        </div>
        @endcanany

        {{-- RODO / GDPR --}}
        @canany(['rcp.view','gdpr_breach.view','dpia.view','dsar.view','subprocessor.view','third_party.view','policy.view'])
        <div class="px-3 mb-1" x-data="{ open: {{ request()->routeIs('rcp.*','gdpr-breaches.*','dpias.*','dsar.*','third-parties.*','policies.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-2 py-1 text-xs font-semibold text-slate-500 uppercase tracking-widest hover:text-slate-300 transition-colors">
                <span>RODO / GDPR</span>
                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition class="space-y-0.5">
                @can('rcp.view')
                <a href="{{ route('rcp.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('rcp.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Rejestr czynności</span>
                </a>
                @endcan
                @can('gdpr_breach.view')
                <a href="{{ route('gdpr-breaches.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('gdpr-breaches.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <span>Naruszenia RODO</span>
                </a>
                @endcan
                @can('dpia.view')
                <a href="{{ route('dpias.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('dpias.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>DPIA</span>
                </a>
                @endcan
                @can('dsar.view')
                <a href="{{ route('dsar.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('dsar.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Wnioski DSAR</span>
                </a>
                @endcan
                @can('subprocessor.view')
                <a href="{{ route('subprocessors.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('subprocessors.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    <span>Subprocesory</span>
                </a>
                @endcan
                @can('third_party.view')
                <a href="{{ route('third-parties.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('third-parties.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Strony trzecie</span>
                </a>
                <a href="{{ route('vendor-risk.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('vendor-risk.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M12 3a9 9 0 100 18 9 9 0 000-18z"/></svg>
                    <span>Ryzyko dostawców</span>
                </a>
                @endcan
                @can('policy.view')
                <a href="{{ route('policies.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('policies.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Polityki</span>
                </a>
                <a href="{{ route('procedures.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('procedures.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Procedury</span>
                </a>
                @endcan
            </div>
        </div>
        @endcanany

        {{-- Compliance --}}
        @canany(['compliance.view','compliance.create','training.view','exception.view'])
        <div class="px-3 mb-1" x-data="{ open: {{ request()->routeIs('trainings.*','exceptions.*','compliance.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-2 py-1 text-xs font-semibold text-slate-500 uppercase tracking-widest hover:text-slate-300 transition-colors">
                <span>Compliance</span>
                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition class="space-y-0.5">
                @can('compliance.view')
                <a href="{{ route('compliance.frameworks') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('compliance.frameworks') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Frameworki</span>
                </a>
                <a href="{{ route('compliance.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('compliance.index') || request()->routeIs('compliance.show') || request()->routeIs('compliance.create') || request()->routeIs('compliance.respond') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Oceny</span>
                </a>
                <a href="{{ route('compliance.overview') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('compliance.overview') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                    <span>Poziom zgodności</span>
                </a>
                @endcan
                @can('compliance.create')
                <a href="{{ route('compliance.admin.frameworks') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('compliance.admin.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Zarządzanie</span>
                </a>
                @endcan
                @can('training.view')
                <a href="{{ route('trainings.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('trainings.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Szkolenia</span>
                </a>
                @endcan
                @can('exception.view')
                <a href="{{ route('exceptions.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('exceptions.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    <span>Wyjątki</span>
                </a>
                @endcan
                @can('compliance.view')
                <a href="{{ route('registers.raci') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('registers.raci') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Macierz RACI</span>
                </a>
                <a href="{{ route('registers.ai-tools') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('registers.ai-tools') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    <span>Narzędzia AI</span>
                </a>
                <a href="{{ route('registers.change-requests') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('registers.change-requests') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Rejestr zmian</span>
                </a>
                <a href="{{ route('registers.disposals') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('registers.disposals') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Rejestr utylizacji</span>
                </a>
                <a href="{{ route('registers.compliance-calendar') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('registers.compliance-calendar') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Kalendarz compliance</span>
                </a>
                <a href="{{ route('registers.compliance-obligations') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('registers.compliance-obligations') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Obowiązki compliance</span>
                </a>
                @endcan
            </div>
        </div>
        @endcanany

        {{-- Audyty --}}
        <div class="px-3 mb-1" x-data="{ open: {{ request()->routeIs('engagements.*','findings.*','cap.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-2 py-1 text-xs font-semibold text-slate-500 uppercase tracking-widest hover:text-slate-300 transition-colors">
                <span>Audyty</span>
                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition class="space-y-0.5">
                <a href="{{ route('engagements.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('engagements.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Engagementy</span>
                </a>
                <a href="{{ route('findings.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('findings.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"/></svg>
                    <span>Wnioski</span>
                </a>
                <a href="{{ route('cap.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('cap.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Plany naprawcze</span>
                </a>
            </div>
        </div>

        {{-- Klienci & Dostawcy --}}
        <div class="px-3 mb-1" x-data="{ open: {{ request()->routeIs('questionnaires.*','answer-library.*','vendor-assessments.*','mcr.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-2 py-1 text-xs font-semibold text-slate-500 uppercase tracking-widest hover:text-slate-300 transition-colors">
                <span>Klienci & Dostawcy</span>
                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition class="space-y-0.5">
                @can('rfp.view')
                <a href="{{ route('questionnaires.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('questionnaires.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Ankiety klientów (RFP)</span>
                </a>
                <a href="{{ route('answer-library.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('answer-library.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    <span>Baza odpowiedzi</span>
                </a>
                @endcan
                <a href="{{ route('vendor-assessments.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('vendor-assessments.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>TPRM (dostawcy)</span>
                </a>
                <a href="{{ route('mcr.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('mcr.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>MCR</span>
                </a>
            </div>
        </div>

        {{-- AppSec & IAM --}}
        @canany(['sdlc.view','access_review.view'])
        <div class="px-3 mb-1" x-data="{ open: {{ request()->routeIs('sdlc.*','access-reviews.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-2 py-1 text-xs font-semibold text-slate-500 uppercase tracking-widest hover:text-slate-300 transition-colors">
                <span>AppSec & IAM</span>
                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition class="space-y-0.5">
                @can('sdlc.view')
                <a href="{{ route('sdlc.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('sdlc.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    <span>SDLC Projekty</span>
                </a>
                @endcan
                @can('access_review.view')
                <a href="{{ route('access-reviews.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('access-reviews.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Przeglądy dostępów</span>
                </a>
                @endcan
            </div>
        </div>
        @endcanany

        {{-- Raporty --}}
        <div class="px-3 mb-1">
            <p class="px-2 py-1 text-xs font-semibold text-slate-500 uppercase tracking-widest">Raporty</p>
            <a href="{{ route('reports.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('reports.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Raporty</span>
            </a>
        </div>

    </nav>

    {{-- User footer --}}
    <div class="border-t border-slate-700/60 px-4 py-3" x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex items-center gap-3 hover:bg-slate-800 rounded-lg px-2 py-1.5 transition-colors">
            <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center flex-shrink-0 text-xs font-bold">
                {{ substr(auth()->user()->name ?? auth()->user()->email, 0, 2) }}
            </div>
            <div class="flex-1 text-left min-w-0">
                <div class="text-sm font-medium truncate">{{ auth()->user()->name ?? auth()->user()->email }}</div>
                <div class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</div>
            </div>
            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" @click.outside="open=false" class="mt-2 bg-slate-800 rounded-lg py-1 text-sm">
            <a href="{{ route('mfa.setup') }}" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-700 rounded transition-colors">
                @if(auth()->user()->hasMfaEnabled())
                    <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-slate-300">MFA aktywne</span>
                @else
                    <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
                    <span class="text-amber-300">Włącz MFA</span>
                @endif
            </a>
            @role('admin')
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-700 rounded transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-slate-700 text-white' : 'text-slate-300' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Użytkownicy</span>
            </a>
            <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-700 rounded transition-colors {{ request()->routeIs('admin.roles.*') ? 'bg-slate-700 text-white' : 'text-slate-300' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Role</span>
            </a>
            <a href="{{ route('admin.audit-log') }}" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-700 rounded transition-colors {{ request()->routeIs('admin.audit-log') ? 'bg-slate-700 text-white' : 'text-slate-300' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Dziennik audytu</span>
            </a>
            @endrole
            @if(auth()->user()->hasAnyRole(['ciso', 'admin']))
            <a href="{{ route('admin.entra.show') }}" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-700 rounded transition-colors {{ request()->routeIs('admin.entra.*') ? 'bg-slate-700 text-white' : 'text-slate-300' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Entra ID / SSO</span>
            </a>
            <a href="{{ route('admin.google-drive.show') }}" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-700 rounded transition-colors {{ request()->routeIs('admin.google-drive.*') ? 'bg-slate-700 text-white' : 'text-slate-300' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14l-4 4m0 0l4 4m-4-4h16m-4-14l4 4m0 0l-4 4m4-4H4"/></svg>
                <span>Google Drive</span>
            </a>
            <a href="{{ route('admin.aws-security-hub.show') }}" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-700 rounded transition-colors {{ request()->routeIs('admin.aws-security-hub.*') ? 'bg-slate-700 text-white' : 'text-slate-300' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>AWS Security Hub</span>
            </a>
            @endif
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-slate-700 rounded transition-colors text-slate-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Wyloguj</span>
                </button>
            </form>
        </div>
    </div>
    @endauth
</aside>

{{-- MAIN AREA --}}
<div class="flex flex-col min-h-screen transition-[padding] duration-200" :class="deskOpen ? 'lg:pl-64' : 'lg:pl-0'">

    {{-- Top bar --}}
    <header class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b border-slate-200 px-4 lg:px-6 py-3 flex items-center gap-4">
        {{-- Mobile hamburger --}}
        <button @click="sidebarOpen = true" class="lg:hidden p-1.5 rounded hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        {{-- Desktop sidebar collapse/expand --}}
        <button @click="toggleDesk()" class="hidden lg:flex p-1.5 rounded hover:bg-slate-100" title="Zwiń/rozwiń menu">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        {{-- Global search --}}
        <form method="GET" action="{{ route('search') }}" class="flex-1 max-w-sm mx-4">
            <div class="relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Szukaj..."
                    class="w-full pl-8 pr-3 py-1.5 text-sm bg-slate-800 border border-slate-700 rounded-lg text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                <svg class="absolute left-2.5 top-2 w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </form>

        {{-- Right side: quick links --}}
        <div class="flex items-center gap-2 text-sm">
            @can('incident.create')
            <a href="{{ route('incidents.create') }}" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-medium hover:bg-red-700 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Incydent
            </a>
            @endcan
            @can('risk.create')
            <a href="{{ route('risks.create') }}" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 text-white rounded-lg text-xs font-medium hover:bg-amber-600 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Ryzyko
            </a>
            @endcan
            <a href="{{ route('help.index') }}" title="Pomoc / instrukcja"
               class="flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors {{ request()->routeIs('help.*') ? 'bg-slate-100 text-slate-700' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </a>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('status') || session('success') || session('warning') || session('error'))
    <div class="px-4 lg:px-6 pt-4">
        @if(session('status') || session('success'))
            <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-2.5 rounded-lg text-sm">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('status') ?? session('success') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-900 px-4 py-2.5 rounded-lg text-sm">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                {{ session('warning') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-900 px-4 py-2.5 rounded-lg text-sm">
                <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif
    </div>
    @endif

    {{-- Page content --}}
    <main class="flex-1 px-4 lg:px-6 py-6">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white px-4 lg:px-6 py-3 text-xs text-slate-400 flex items-center justify-between">
        <span>GRC Platform · Cyber Risk & Audit Engine</span>
        <span>{{ now()->format('Y-m-d H:i') }}</span>
    </footer>
</div>

@stack('scripts')
</body>
</html>
