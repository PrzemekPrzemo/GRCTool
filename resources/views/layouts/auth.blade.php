<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Login' }} — GRC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-900">
<div class="min-h-full flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="text-emerald-400 text-3xl font-bold">GRC Platform</div>
            <div class="text-slate-400 text-sm mt-1">Cyber Risk · Compliance · Audit</div>
        </div>
        <div class="bg-white rounded-lg shadow-2xl p-8">
            @yield('content')
        </div>
        <div class="text-slate-500 text-xs text-center mt-4">
            Wszystkie próby logowania są rejestrowane. Stosujemy MFA i polityki retencji ≥7 lat.
        </div>
    </div>
</div>
</body>
</html>
