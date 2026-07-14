@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Naruszenia danych osobowych</h1>
        <p class="text-sm text-slate-500 mt-1">Art. 33–34 RODO — rejestr naruszeń ochrony danych, zgłoszenia do UODO</p>
    </div>
    @can('gdpr_breach.create')
        <a href="{{ route('gdpr-breaches.create') }}" class="px-3 py-1.5 bg-red-600 text-white rounded text-sm">+ Nowe naruszenie</a>
    @endcan
</div>

@php
    $overdue = $breaches->filter(fn($b) => $b->isOverdue());
@endphp
@if($overdue->isNotEmpty())
<div class="bg-red-50 border border-red-300 rounded p-3 mb-4 text-sm text-red-800">
    <strong>{{ $overdue->count() }} naruszenie(ń)</strong> przekroczyło termin zgłoszenia do UODO (72 godziny od wykrycia).
</div>
@endif

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex flex-wrap gap-2">
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status —</option>
        <option value="open" @selected(request('status')==='open')>Otwarte</option>
        <option value="contained" @selected(request('status')==='contained')>Opanowane</option>
        <option value="reported" @selected(request('status')==='reported')>Zgłoszone do UODO</option>
        <option value="closed" @selected(request('status')==='closed')>Zamknięte</option>
    </select>
    <select name="risk_level" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Ryzyko —</option>
        <option value="low" @selected(request('risk_level')==='low')>Niskie</option>
        <option value="medium" @selected(request('risk_level')==='medium')>Średnie</option>
        <option value="high" @selected(request('risk_level')==='high')>Wysokie</option>
        <option value="very_high" @selected(request('risk_level')==='very_high')>Bardzo wysokie</option>
    </select>
    <label class="flex items-center gap-2 text-sm px-2">
        <input type="checkbox" name="notification" value="1" @checked(request('notification'))>
        Tylko wymagające zgłoszenia
    </label>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
    @if(request()->anyFilled(['status','risk_level','notification']))
        <a href="{{ route('gdpr-breaches.index') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600">Wyczyść</a>
    @endif
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Tytuł</th>
                <th class="px-3 py-2">Rodzaj</th>
                <th class="px-3 py-2">Ryzyko</th>
                <th class="px-3 py-2">Zgłoszenie UODO</th>
                <th class="px-3 py-2">Deadline 72h</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Wykryto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($breaches as $b)
            @php
                $riskBg = match($b->risk_level) {
                    'very_high' => 'bg-red-600 text-white',
                    'high'      => 'bg-orange-500 text-white',
                    'medium'    => 'bg-amber-300 text-amber-900',
                    'low'       => 'bg-slate-200 text-slate-700',
                    default     => 'bg-slate-100 text-slate-600',
                };
                $statusBg = match($b->status) {
                    'open'      => 'bg-red-100 text-red-800',
                    'contained' => 'bg-amber-100 text-amber-800',
                    'reported'  => 'bg-blue-100 text-blue-800',
                    'closed'    => 'bg-emerald-100 text-emerald-800',
                    default     => 'bg-slate-100 text-slate-600',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 {{ $b->isOverdue() ? 'bg-red-50' : '' }}">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('gdpr-breaches.show', $b) }}" class="text-emerald-700 hover:underline">{{ $b->code }}</a>
                </td>
                <td class="px-3 py-2">{{ $b->title }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">
                    {{ match($b->breach_type) { 'confidentiality' => 'Poufność', 'integrity' => 'Integralność', 'availability' => 'Dostępność', default => '—' } }}
                </td>
                <td class="px-3 py-2">
                    @if($b->risk_level)
                        <span class="px-2 py-0.5 rounded text-xs {{ $riskBg }}">{{ $b->riskLevelLabel() }}</span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-3 py-2 text-center">
                    @if($b->notification_required)
                        @if($b->uodo_notified_at)
                            <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800">Zgłoszono</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-700 font-semibold">Wymagane</span>
                        @endif
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-3 py-2 text-xs {{ $b->isOverdue() ? 'text-red-700 font-semibold' : 'text-slate-600' }}">
                    @if($b->uodo_notification_deadline && !$b->uodo_notified_at)
                        {{ $b->uodo_notification_deadline->format('Y-m-d H:i') }}
                        @if($b->isOverdue()) <span class="text-red-600">⚠ PRZEKROCZONY</span> @endif
                    @else
                        —
                    @endif
                </td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">
                        {{ match($b->status) { 'open' => 'Otwarte', 'contained' => 'Opanowane', 'reported' => 'Zgłoszone', 'closed' => 'Zamknięte', default => $b->status } }}
                    </span>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $b->discovered_at?->format('Y-m-d H:i') ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-3 py-8 text-center text-slate-500">
                    Brak zarejestrowanych naruszeń danych osobowych.
                    @can('gdpr_breach.create')
                        <a href="{{ route('gdpr-breaches.create') }}" class="text-emerald-600 hover:underline ml-1">Zarejestruj naruszenie</a>.
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $breaches->links() }}</div>
@endsection
