@extends('layouts.app')
@section('content')

<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('procedures.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Procedury</a>
    <h1 class="text-2xl font-semibold">{{ $procedure->title }}</h1>
    <span class="text-xs font-mono text-slate-500">{{ $procedure->code }} · v{{ $procedure->version }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        @if($procedure->steps->isNotEmpty())
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Kroki ({{ $procedure->steps->count() }})</h2>
            <ol class="space-y-2">
                @foreach($procedure->steps as $step)
                <li class="border border-slate-100 rounded p-2.5 flex items-start gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-100 text-slate-600 text-xs flex items-center justify-center font-medium">{{ $step->step_no }}</span>
                    <div class="flex-1">
                        <p class="text-sm text-slate-800">{{ $step->description }}</p>
                        <div class="flex flex-wrap gap-1.5 mt-1.5">
                            @if($step->owner_role)
                                <span class="px-1.5 py-0.5 rounded text-[11px] bg-slate-100 text-slate-600">{{ $step->owner_role }}</span>
                            @endif
                            @if($step->sla)
                                <span class="px-1.5 py-0.5 rounded text-[11px] bg-amber-50 text-amber-700">SLA: {{ $step->sla }}</span>
                            @endif
                            @if($step->tool)
                                <span class="px-1.5 py-0.5 rounded text-[11px] bg-blue-50 text-blue-700">{{ $step->tool }}</span>
                            @endif
                        </div>
                    </div>
                </li>
                @endforeach
            </ol>
        </div>
        @endif

        @if($procedure->description)
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Pełna treść</h2>
            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $procedure->description }}</p>
        </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4 text-xs text-slate-500 space-y-1.5">
            <div>Kod: <span class="font-mono text-slate-700">{{ $procedure->code }}</span></div>
            <div>Wersja: <span class="text-slate-700">v{{ $procedure->version }}</span></div>
            <div>Właściciel: <span class="text-slate-700">{{ $procedure->owner_role ?? '—' }}</span></div>
            <div>Obowiązuje od: <span class="text-slate-700">{{ $procedure->effective_from?->format('Y-m-d') ?? '—' }}</span></div>
            <div>Powiązana polityka: <span class="text-slate-700">{{ $procedure->policy_ref ?? '—' }}</span></div>
            <div>
                Encja GRC:
                @if($procedure->related_model)
                    <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700">{{ $procedure->related_model }}</span>
                @else
                    <span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">brak — tylko procedura</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
