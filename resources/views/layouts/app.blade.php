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
<body class="h-full bg-slate-50 text-slate-900 antialiased">
<div class="min-h-full flex flex-col">
    <header class="bg-slate-900 text-slate-100 shadow">
        <div class="max-w-screen-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="font-semibold text-lg flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    GRC Platform
                </a>
                @auth
                <nav class="hidden md:flex gap-1 text-sm">
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('dashboard') ? 'bg-slate-800' : '' }}">Dashboard</a>
                    <a href="{{ route('risks.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('risks.*') ? 'bg-slate-800' : '' }}">Ryzyka</a>
                    <a href="{{ route('controls.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('controls.*') ? 'bg-slate-800' : '' }}">Kontrole</a>
                    <a href="{{ route('indicators.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('indicators.*') ? 'bg-slate-800' : '' }}">Wskaźniki</a>
                    <a href="{{ route('assets.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('assets.*') ? 'bg-slate-800' : '' }}">Aktywa</a>
                    <a href="{{ route('vulnerabilities.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('vulnerabilities.*') ? 'bg-slate-800' : '' }}">Podatności</a>
                    <a href="{{ route('incidents.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('incidents.*') ? 'bg-slate-800' : '' }}">Incydenty</a>
                    <a href="{{ route('nis2.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('nis2.*') ? 'bg-slate-800' : '' }}">NIS2</a>
                    <a href="{{ route('trainings.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('trainings.*') ? 'bg-slate-800' : '' }}">Szkolenia</a>
                    <a href="{{ route('exceptions.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('exceptions.*') ? 'bg-slate-800' : '' }}">Wyjątki</a>
                    <a href="{{ route('certificates.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('certificates.*') || request()->routeIs('crypto-keys.*') ? 'bg-slate-800' : '' }}">Certyfikaty</a>
                    <a href="{{ route('bcp.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('bcp.*') ? 'bg-slate-800' : '' }}">BCP/DR</a>
                    <a href="{{ route('org-metrics.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('org-metrics.*') ? 'bg-slate-800' : '' }}">Metryki</a>
                    <a href="{{ route('engagements.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('engagements.*') || request()->routeIs('findings.*') ? 'bg-slate-800' : '' }}">Audyty</a>
                    <a href="{{ route('questionnaires.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('questionnaires.*') || request()->routeIs('answer-library.*') ? 'bg-slate-800' : '' }}">Ankiety</a>
                    <a href="{{ route('vendor-assessments.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('vendor-assessments.*') || request()->routeIs('mcr.*') ? 'bg-slate-800' : '' }}">TPRM</a>
                    @canany(['rcp.view','gdpr_breach.view','dpia.view','dsar.view'])
                    <div class="relative group">
                        <button class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('rcp.*') || request()->routeIs('gdpr-breaches.*') || request()->routeIs('dpias.*') || request()->routeIs('dsar.*') ? 'bg-slate-800' : '' }}">RODO ▾</button>
                        <div class="absolute left-0 top-full mt-1 w-48 bg-slate-800 rounded shadow-lg py-1 hidden group-hover:block z-50 text-sm">
                            @can('rcp.view')<a href="{{ route('rcp.index') }}" class="block px-4 py-1.5 hover:bg-slate-700 {{ request()->routeIs('rcp.*') ? 'bg-slate-700' : '' }}">Rejestr czynności</a>@endcan
                            @can('gdpr_breach.view')<a href="{{ route('gdpr-breaches.index') }}" class="block px-4 py-1.5 hover:bg-slate-700 {{ request()->routeIs('gdpr-breaches.*') ? 'bg-slate-700' : '' }}">Naruszenia RODO</a>@endcan
                            @can('dpia.view')<a href="{{ route('dpias.index') }}" class="block px-4 py-1.5 hover:bg-slate-700 {{ request()->routeIs('dpias.*') ? 'bg-slate-700' : '' }}">DPIA</a>@endcan
                            @can('dsar.view')<a href="{{ route('dsar.index') }}" class="block px-4 py-1.5 hover:bg-slate-700 {{ request()->routeIs('dsar.*') ? 'bg-slate-700' : '' }}">Wnioski DSAR</a>@endcan
                            @can('third_party.view')<a href="{{ route('third-parties.index') }}" class="block px-4 py-1.5 hover:bg-slate-700 {{ request()->routeIs('third-parties.*') ? 'bg-slate-700' : '' }}">Strony trzecie</a>@endcan
                            @can('policy.view')<a href="{{ route('policies.index') }}" class="block px-4 py-1.5 hover:bg-slate-700 {{ request()->routeIs('policies.*') ? 'bg-slate-700' : '' }}">Polityki</a>@endcan
                        </div>
                    </div>
                    @endcanany
                    <a href="{{ route('reports.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('reports.*') ? 'bg-slate-800' : '' }}">Raporty</a>
                </nav>
                @endauth
            </div>
            @auth
            <div class="flex items-center gap-3 text-sm">
                <span class="hidden md:inline text-slate-300">{{ auth()->user()->email }}</span>
                @role('admin')
                    <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded hover:bg-slate-800">Admin</a>
                @endrole
                <a href="{{ route('mfa.setup') }}" class="px-3 py-1.5 rounded hover:bg-slate-800">
                    @if(auth()->user()->hasMfaEnabled())
                        <span class="text-emerald-400">MFA ✓</span>
                    @else
                        <span class="text-amber-400">MFA ⚠</span>
                    @endif
                </a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button class="px-3 py-1.5 rounded hover:bg-slate-800">Wyloguj</button>
                </form>
            </div>
            @endauth
        </div>
    </header>

    @if(session('status') || session('success') || session('warning') || session('error'))
        <div class="max-w-screen-2xl mx-auto px-4 mt-4 w-full">
            @if(session('status') || session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-2 rounded">{{ session('status') ?? session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-2 rounded">{{ session('warning') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-900 px-4 py-2 rounded">{{ session('error') }}</div>
            @endif
        </div>
    @endif

    <main class="flex-1 max-w-screen-2xl mx-auto px-4 py-6 w-full">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white py-3 text-xs text-slate-500">
        <div class="max-w-screen-2xl mx-auto px-4 flex items-center justify-between">
            <div>GRC Platform · Cyber Risk &amp; Audit Engine</div>
            <div>{{ now()->format('Y-m-d H:i') }}</div>
        </div>
    </footer>
</div>
@stack('scripts')
</body>
</html>
