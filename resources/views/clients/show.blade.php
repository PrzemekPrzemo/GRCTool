@extends('layouts.app')
@section('content')
@php
    $tierBg = match($client->tier) {
        'Enterprise' => 'bg-purple-100 text-purple-800',
        'Mid-market' => 'bg-blue-100 text-blue-800',
        default      => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('clients.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Klienci</a>
    <h1 class="text-2xl font-semibold">{{ $client->name }}</h1>
    <span class="px-2 py-0.5 rounded text-xs {{ $tierBg }}">{{ $client->tier }}</span>
    @if($client->is_active)
        <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800">Aktywny</span>
    @else
        <span class="px-2 py-0.5 rounded text-xs bg-slate-200 text-slate-600">Nieaktywny</span>
    @endif
    @can('client.update')
        <a href="{{ route('clients.edit', $client) }}" class="ml-auto px-3 py-1.5 border border-slate-300 rounded text-sm hover:bg-slate-50">Edytuj</a>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        {{-- Dane podstawowe --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Dane podstawowe</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="text-slate-500">Kod</dt>
                <dd class="font-mono">{{ $client->code }}</dd>
                <dt class="text-slate-500">Nazwa</dt>
                <dd class="font-medium">{{ $client->name }}</dd>
                <dt class="text-slate-500">Branża</dt>
                <dd>{{ $client->industry ?? '—' }}</dd>
                <dt class="text-slate-500">Tier</dt>
                <dd><span class="px-2 py-0.5 rounded text-xs {{ $tierBg }}">{{ $client->tier }}</span></dd>
                <dt class="text-slate-500">NDA podpisane</dt>
                <dd>{{ $client->nda_signed_at?->format('Y-m-d') ?? '—' }}</dd>
                <dt class="text-slate-500">Powiadomienie o podprocesorach</dt>
                <dd>{{ $client->subprocessor_notification_required ? 'Tak' : 'Nie' }}</dd>
                @if($client->subprocessor_notification_required && $client->notification_lead_time_days)
                <dt class="text-slate-500">Wyprzedzenie powiadomienia</dt>
                <dd>{{ $client->notification_lead_time_days }} dni</dd>
                @endif
            </dl>
        </div>

        {{-- Frameworki --}}
        @if(!empty($client->applicable_frameworks))
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Obowiązujące frameworki</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($client->applicable_frameworks as $fw)
                    <span class="px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-800 font-medium">{{ $fw }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Wymagania bezpieczeństwa --}}
        @if(!empty($client->contractual_security_requirements))
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Kontraktowe wymagania bezpieczeństwa</h2>
            <ul class="space-y-1.5">
                @foreach($client->contractual_security_requirements as $req)
                    <li class="flex items-start gap-2 text-sm text-slate-700">
                        <span class="mt-1 text-slate-400">•</span>
                        <span>{{ $req }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Powiązane projekty --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Powiązane projekty</h2>
            @if($client->projects->isNotEmpty())
            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Kod</th>
                        <th class="px-3 py-2 text-left">Nazwa</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Data rozpoczęcia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($client->projects as $project)
                    @php
                        $statusBg = match($project->status) {
                            'active'    => 'bg-emerald-100 text-emerald-800',
                            'on_hold'   => 'bg-amber-100 text-amber-800',
                            default     => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs">
                            <a href="{{ route('projects.show', $project) }}" class="text-emerald-700 hover:underline">{{ $project->code }}</a>
                        </td>
                        <td class="px-3 py-2">{{ $project->name }}</td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">{{ $project->status }}</span>
                        </td>
                        <td class="px-3 py-2 text-slate-600 text-xs">{{ $project->start_date?->format('Y-m-d') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-sm text-slate-500">Brak przypisanych projektów.</p>
            @endif
        </div>

    </div>

    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4 text-xs text-slate-500 space-y-1.5">
            <div>Kod: <span class="font-mono text-slate-700">{{ $client->code }}</span></div>
            <div>Dodano: {{ $client->created_at->format('Y-m-d H:i') }}</div>
            <div>Zaktualizowano: {{ $client->updated_at->format('Y-m-d H:i') }}</div>
        </div>
    </div>
</div>
@endsection
