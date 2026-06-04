@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('trainings.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Szkolenia</a>
</div>

<div class="grid grid-cols-3 gap-4">
    <div class="col-span-2 space-y-4">
        <div class="bg-white rounded shadow p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="font-mono text-xs text-slate-500">{{ $training->code }}</span>
                    <h1 class="text-xl font-semibold">{{ $training->title }}</h1>
                </div>
                <div class="flex gap-2">
                    @if($training->is_mandatory)
                    <span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-700">Obowiązkowe</span>
                    @endif
                    @if($training->is_active)
                    <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">Aktywne</span>
                    @endif
                </div>
            </div>
            @if($training->description)
            <p class="text-sm text-slate-600">{{ $training->description }}</p>
            @endif
            <div class="grid grid-cols-3 gap-3 mt-3 text-sm">
                <div><span class="text-slate-500">Typ:</span> {{ \App\Models\Training::TYPE_LABELS[$training->type] ?? $training->type }}</div>
                <div><span class="text-slate-500">Częstotliwość:</span> {{ \App\Models\Training::FREQUENCY_LABELS[$training->frequency] ?? $training->frequency }}</div>
                <div><span class="text-slate-500">Ważność:</span> {{ $training->expiry_days }} dni</div>
                <div><span class="text-slate-500">Właściciel:</span> {{ $training->owner?->name ?? '—' }}</div>
                <div><span class="text-slate-500">Ukończenie:</span> {{ $training->completionRate() }}%</div>
            </div>
        </div>

        {{-- Completion Matrix --}}
        <div class="bg-white rounded shadow overflow-hidden">
            <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-sm">Macierz ukończeń</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 text-left">
                    <tr>
                        <th class="px-3 py-2">Użytkownik</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Ukończono</th>
                        <th class="px-3 py-2">Wygasa</th>
                        <th class="px-3 py-2">Wynik</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completions as $c)
                    @php
                        $statusBg = ['pending'=>'bg-slate-100 text-slate-600','completed'=>'bg-emerald-100 text-emerald-700','expired'=>'bg-red-100 text-red-700','waived'=>'bg-amber-100 text-amber-700'][$c->status] ?? 'bg-slate-100';
                        $statusLabel = ['pending'=>'Oczekuje','completed'=>'Ukończone','expired'=>'Wygasłe','waived'=>'Zwolnione'][$c->status] ?? $c->status;
                    @endphp
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2">{{ $c->user->name }}</td>
                        <td class="px-3 py-2"><span class="px-1.5 py-0.5 rounded text-xs {{ $statusBg }}">{{ $statusLabel }}</span></td>
                        <td class="px-3 py-2 text-xs text-slate-600">{{ $c->completed_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-3 py-2 text-xs {{ $c->isExpired() ? 'text-red-600 font-medium' : 'text-slate-600' }}">{{ $c->expires_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-3 py-2 text-xs">{{ $c->score !== null ? $c->score.'%' : '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-3 py-4 text-center text-sm text-slate-400">Brak ukończeń</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold text-sm mb-3">Akcje</h2>
            <a href="{{ route('trainings.edit', $training) }}" class="block w-full text-center px-3 py-1.5 bg-slate-100 rounded text-sm hover:bg-slate-200 mb-2">Edytuj</a>
        </div>

        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold text-sm mb-3">Zarejestruj ukończenie</h2>
            <form method="POST" action="{{ route('trainings.complete', $training) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs mb-1">Użytkownik *</label>
                    <select name="user_id" required class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                        <option value="">— wybierz —</option>
                        @foreach($allUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs mb-1">Data ukończenia *</label>
                    <input type="date" name="completed_at" value="{{ date('Y-m-d') }}" required class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                </div>
                <div>
                    <label class="block text-xs mb-1">Wynik (%)</label>
                    <input type="number" name="score" min="0" max="100" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                </div>
                <div>
                    <label class="block text-xs mb-1">Status</label>
                    <select name="status" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                        <option value="completed">Ukończone</option>
                        <option value="pending">Oczekuje</option>
                        <option value="waived">Zwolnione</option>
                    </select>
                </div>
                <button type="submit" class="w-full px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Zapisz</button>
            </form>
        </div>
    </div>
</div>
@endsection
