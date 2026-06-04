@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Szkolenia i świadomość</h1>
    <div class="flex gap-2">
        <a href="{{ route('trainings.report') }}" class="px-3 py-1.5 bg-slate-700 text-white rounded text-sm">Raport macierzowy</a>
        <a href="{{ route('trainings.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowe szkolenie</a>
    </div>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Tytuł</th>
                <th class="px-3 py-2">Typ</th>
                <th class="px-3 py-2">Częstotliwość</th>
                <th class="px-3 py-2">Ukończenie</th>
                <th class="px-3 py-2">Obowiązkowe</th>
                <th class="px-3 py-2">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trainings as $t)
            @php
                $rate = $t->completionRate();
                $typeLabel = \App\Models\Training::TYPE_LABELS[$t->type] ?? $t->type;
                $freqLabel = \App\Models\Training::FREQUENCY_LABELS[$t->frequency] ?? $t->frequency;
                $typeBg = ['security_awareness'=>'bg-blue-100 text-blue-800','gdpr'=>'bg-purple-100 text-purple-800','role_specific'=>'bg-orange-100 text-orange-800','technical'=>'bg-teal-100 text-teal-800','onboarding'=>'bg-green-100 text-green-800'][$t->type] ?? 'bg-slate-100 text-slate-700';
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('trainings.show', $t) }}" class="text-emerald-700 hover:underline">{{ $t->code }}</a>
                </td>
                <td class="px-3 py-2">
                    <a href="{{ route('trainings.show', $t) }}" class="hover:underline font-medium">{{ $t->title }}</a>
                </td>
                <td class="px-3 py-2">
                    <span class="px-1.5 py-0.5 rounded text-xs {{ $typeBg }}">{{ $typeLabel }}</span>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $freqLabel }}</td>
                <td class="px-3 py-2">
                    @if($t->completions_count > 0)
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-slate-200 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $rate >= 80 ? 'bg-emerald-500' : ($rate >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $rate }}%"></div>
                        </div>
                        <span class="text-xs text-slate-600">{{ $rate }}%</span>
                    </div>
                    @else
                    <span class="text-xs text-slate-400">Brak danych</span>
                    @endif
                </td>
                <td class="px-3 py-2">
                    @if($t->is_mandatory)
                    <span class="px-1.5 py-0.5 rounded text-xs bg-red-100 text-red-700">Tak</span>
                    @else
                    <span class="px-1.5 py-0.5 rounded text-xs bg-slate-100 text-slate-500">Nie</span>
                    @endif
                </td>
                <td class="px-3 py-2">
                    <a href="{{ route('trainings.edit', $t) }}" class="text-xs text-slate-600 hover:underline">Edytuj</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $trainings->links() }}</div>
@endsection
