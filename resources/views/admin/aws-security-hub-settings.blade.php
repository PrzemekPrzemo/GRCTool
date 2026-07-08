@extends('layouts.app')
@section('title', 'Konfiguracja AWS Security Hub')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Konfiguracja AWS Security Hub</h1>
        <p class="text-slate-500 mt-1 text-sm">Ciągnie aktywne findings z AWS Security Hub (agreguje GuardDuty, Inspector, Config) — u nas AWS hostuje aplikacje wewnętrzne, więc to główne źródło podatności/misconfigów dla tej infrastruktury.</p>
    </div>

    @if(session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded text-sm">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-900 rounded text-sm">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-900 rounded text-sm">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-700">Synchronizacja AWS Security Hub</span>
            @if($settings['aws_security_hub_enabled'] === '1')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Włączone
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    Wyłączone
                </span>
            @endif
        </div>
        <div class="mt-3 grid grid-cols-2 gap-3 text-sm text-slate-600">
            <div>
                <span class="font-medium">Region:</span>
                <span class="ml-1 font-mono text-xs">{{ $settings['aws_region'] ?: '—' }}</span>
            </div>
            <div>
                <span class="font-medium">Access Key ID:</span>
                <span class="ml-1 font-mono text-xs">{{ $settings['aws_access_key_id'] ? substr($settings['aws_access_key_id'], 0, 6).'…' : '—' }}</span>
            </div>
            <div class="col-span-2">
                <span class="font-medium">Secret Access Key:</span>
                <span class="ml-1">{{ $settings['secret_saved'] ? '●●●●●● (zapisany)' : '— (nie ustawiony)' }}</span>
            </div>
        </div>
        @if($settings['secret_saved'])
        <form method="POST" action="{{ route('admin.aws-security-hub.test') }}" class="mt-4">
            @csrf
            <button class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm hover:bg-slate-50">Testuj połączenie</button>
        </form>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.aws-security-hub.update') }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Region AWS <span class="text-red-500">*</span></label>
            <input type="text" name="aws_region" required
                   value="{{ old('aws_region', $settings['aws_region']) }}"
                   placeholder="eu-west-1"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400">
            @error('aws_region')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Access Key ID <span class="text-red-500">*</span></label>
            <input type="text" name="aws_access_key_id" required
                   value="{{ old('aws_access_key_id', $settings['aws_access_key_id']) }}"
                   placeholder="AKIA…"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400">
            @error('aws_access_key_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Secret Access Key
                @if($settings['secret_saved'])
                    <span class="text-xs font-normal text-slate-400 ml-1">(zostaw puste, by zachować aktualny)</span>
                @else
                    <span class="text-red-500">*</span>
                @endif
            </label>
            <input type="password" name="aws_secret_access_key"
                   placeholder="{{ $settings['secret_saved'] ? '●●●●●●●●●● (nie zmieniaj)' : 'Wklej Secret Access Key' }}"
                   autocomplete="off"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400">
            @error('aws_secret_access_key')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="pt-2 border-t border-slate-100">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="aws_security_hub_enabled" value="1"
                       {{ $settings['aws_security_hub_enabled'] === '1' ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-400">
                <div>
                    <p class="text-sm font-medium text-slate-700">Włącz synchronizację Security Hub</p>
                    <p class="text-xs text-slate-400">Godzinowa komenda ciągnie aktywne findings i tworzy/aktualizuje podatności w rejestrze.</p>
                </div>
            </label>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('vulnerabilities.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Anuluj</a>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition-colors">
                Zapisz konfigurację
            </button>
        </div>
    </form>

    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-blue-900 mb-3">Jak przygotować dostęp IAM</h3>
        <ol class="text-xs text-blue-800 space-y-1.5 list-decimal list-inside">
            <li>Włącz <strong>AWS Security Hub</strong> w regionie z Waszymi aplikacjami (i opcjonalnie GuardDuty/Inspector/Config jako źródła)</li>
            <li>IAM → Users → Create user (np. <code class="bg-blue-100 px-1 rounded">grc-tool-readonly</code>), Programmatic access</li>
            <li>Przypnij politykę <strong>AWSSecurityHubReadOnlyAccess</strong> (managed policy AWS)</li>
            <li>Security credentials → Create access key → skopiuj Access Key ID i Secret Access Key</li>
            <li>Wklej dane powyżej i zapisz — klucz jest szyfrowany przed zapisem w bazie</li>
        </ol>
    </div>

</div>
@endsection
