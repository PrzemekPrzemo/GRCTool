@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-4">
    <div>
        <a href="{{ route('trainings.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Szkolenia</a>
        <h1 class="text-2xl font-semibold mt-1">Raport ukończeń szkoleń</h1>
        <p class="text-sm text-slate-500">Macierz: wiersze = użytkownicy, kolumny = szkolenia</p>
    </div>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="text-xs">
        <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="px-3 py-2 text-left sticky left-0 bg-slate-50 border-r border-slate-200 min-w-[160px]">Użytkownik</th>
                @foreach($trainings as $t)
                <th class="px-2 py-2 text-center font-medium max-w-[80px]" title="{{ $t->title }}">
                    <div class="truncate max-w-[80px]">{{ $t->code }}</div>
                    <div class="text-slate-400 font-normal truncate max-w-[80px]">{{ \App\Models\Training::TYPE_LABELS[$t->type] ?? $t->type }}</div>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 sticky left-0 bg-white border-r border-slate-200 font-medium text-slate-700">{{ $u->name }}</td>
                @foreach($trainings as $t)
                @php
                    $completion = $completions[$u->id][$t->id] ?? null;
                    if (!$completion) {
                        $bg = 'bg-slate-100 text-slate-400';
                        $label = '—';
                    } else {
                        [$bg, $label] = match($completion->status) {
                            'completed' => ['bg-emerald-100 text-emerald-700', 'OK'],
                            'expired'   => ['bg-red-100 text-red-700', 'Wygasło'],
                            'waived'    => ['bg-amber-100 text-amber-700', 'Zwol.'],
                            default     => ['bg-slate-100 text-slate-500', 'Oczek.'],
                        };
                        if ($completion->status === 'completed' && $completion->isExpired()) {
                            $bg = 'bg-red-100 text-red-700';
                            $label = 'Wygasło';
                        }
                    }
                @endphp
                <td class="px-2 py-2 text-center">
                    <span class="px-1.5 py-0.5 rounded {{ $bg }}">{{ $label }}</span>
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
