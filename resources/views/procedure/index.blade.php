@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Procedury</h1>
        <p class="text-sm text-slate-500 mt-0.5">Operacyjne procedury i playbooki wdrażające polityki bezpieczeństwa</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('policies.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
            ← Polityki
        </a>
        @can('policy.create')
        <a href="{{ route('procedures.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
            + Nowa procedura
        </a>
        @endcan
    </div>
</div>

@if($procedures->isEmpty())
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
    <p class="text-slate-400">Brak procedur w bazie</p>
</div>
@else
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-2.5">Kod</th>
                <th class="text-left px-4 py-2.5">Tytuł</th>
                <th class="text-left px-4 py-2.5">Powiązana polityka</th>
                <th class="text-left px-4 py-2.5">Encja GRC</th>
                <th class="text-left px-4 py-2.5">Kroki</th>
                <th class="text-left px-4 py-2.5">Wersja</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($procedures as $procedure)
            <tr class="hover:bg-slate-50 cursor-pointer" onclick="window.location='{{ route('procedures.show', $procedure) }}'">
                <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $procedure->code }}</td>
                <td class="px-4 py-3 font-medium text-slate-900">
                    <a href="{{ route('procedures.show', $procedure) }}" class="hover:underline">{{ $procedure->title }}</a>
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $procedure->policy_ref ?? '—' }}</td>
                <td class="px-4 py-3">
                    @if($procedure->related_model)
                        <span class="px-2 py-0.5 rounded text-xs bg-blue-50 text-blue-700">{{ $procedure->related_model }}</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-xs bg-amber-50 text-amber-700">brak encji</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $procedure->steps_count }}</td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $procedure->version }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif
@endsection
