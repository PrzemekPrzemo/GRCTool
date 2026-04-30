@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <div class="text-xs font-mono text-slate-500">{{ $scenario->code }}</div>
        <h1 class="text-2xl font-semibold">{{ $scenario->name }}</h1>
        <p class="text-slate-500 text-sm">{{ $scenario->category_l1 }} / {{ $scenario->category_l2 }}</p>
    </div>
    @can('create', App\Models\Risk::class)
    <form method="POST" action="{{ route('scenarios.adopt', $scenario) }}">@csrf
        <button class="px-4 py-2 bg-emerald-600 text-white rounded">Adopt jako ryzyko →</button>
    </form>
    @endcan
</div>
<div class="bg-white rounded shadow p-6 max-w-3xl">
    <p class="whitespace-pre-line text-sm">{{ $scenario->description }}</p>
    <div class="grid grid-cols-2 gap-4 mt-6 text-sm">
        <div>
            <h3 class="font-medium mb-1">Threat actors</h3>
            <ul class="text-xs text-slate-600 space-y-0.5">
                @foreach($scenario->default_threat_actors ?? [] as $ta)<li>· {{ $ta }}</li>@endforeach
            </ul>
        </div>
        <div>
            <h3 class="font-medium mb-1">MITRE ATT&CK</h3>
            <ul class="text-xs font-mono text-slate-600 space-y-0.5">
                @foreach($scenario->default_mitre_techniques ?? [] as $m)<li>· {{ $m }}</li>@endforeach
            </ul>
        </div>
        <div>
            <h3 class="font-medium mb-1">Typowe assets</h3>
            <ul class="text-xs text-slate-600 space-y-0.5">
                @foreach($scenario->typical_assets_affected ?? [] as $a)<li>· {{ $a }}</li>@endforeach
            </ul>
        </div>
        <div>
            <h3 class="font-medium mb-1">Recommended controls</h3>
            <ul class="text-xs text-slate-600 font-mono space-y-0.5">
                @foreach($scenario->recommended_controls ?? [] as $c)<li>· {{ $c }}</li>@endforeach
            </ul>
        </div>
    </div>
    <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500">
        Default likelihood PERT: min={{ $scenario->default_likelihood_pert['min'] ?? '?' }}, mode={{ $scenario->default_likelihood_pert['mode'] ?? '?' }}, max={{ $scenario->default_likelihood_pert['max'] ?? '?' }}
        | Default impact PERT: min={{ $scenario->default_impact_pert['min'] ?? '?' }}, mode={{ $scenario->default_impact_pert['mode'] ?? '?' }}, max={{ $scenario->default_impact_pert['max'] ?? '?' }}
    </div>
</div>
@endsection
