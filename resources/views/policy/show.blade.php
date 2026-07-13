@extends('layouts.app')
@section('content')
@php
    $statusBg = match($policy->status) {
        'Draft'    => 'bg-yellow-100 text-yellow-800',
        'Approved' => 'bg-blue-100 text-blue-800',
        'Active'   => 'bg-emerald-100 text-emerald-800',
        'Retired'  => 'bg-slate-200 text-slate-600',
        default    => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('policies.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Polityki</a>
    <h1 class="text-2xl font-semibold">{{ $policy->title }}</h1>
    <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">{{ $policy->status }}</span>
    <span class="text-xs font-mono text-slate-500">v{{ $policy->current_version }}</span>
    @can('policy.update')
    <div class="ml-auto flex gap-2">
        <a href="{{ route('policies.edit', $policy) }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm">Edytuj</a>
        @if($policy->status === 'Approved' || $policy->status === 'Draft')
        <form method="POST" action="{{ route('policies.approve', $policy) }}" onsubmit="return confirm('Zatwierdzić i aktywować politykę?')">
            @csrf
            <button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Zatwierdź</button>
        </form>
        @endif
    </div>
    @endcan
</div>

@if($policy->next_review_due?->isPast())
<div class="bg-amber-50 border-l-4 border-amber-400 px-4 py-2 rounded-r mb-4 text-sm text-amber-800">
    Termin przeglądu polityki minął: <strong>{{ $policy->next_review_due->format('Y-m-d') }}</strong>
</div>
@endif

{{-- Attestation CTA --}}
@if($policy->attestation_required && $policy->status === 'Active')
<div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4 flex items-center justify-between">
    <div class="text-sm text-blue-800">
        @if($userAttestation)
            ✓ Potwierdziłeś zapoznanie się z tą polityką ({{ $userAttestation->attested_at->format('Y-m-d') }})
        @else
            Wymagane jest potwierdzenie zapoznania się z polityką v{{ $policy->current_version }}
        @endif
    </div>
    @if(!$userAttestation)
    <form method="POST" action="{{ route('policies.attest', $policy) }}">
        @csrf
        <button class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm">Potwierdzam zapoznanie</button>
    </form>
    @endif
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Szczegóły polityki</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="text-slate-500">Kod</dt>
                <dd class="font-mono">{{ $policy->code }}</dd>
                <dt class="text-slate-500">Kategoria</dt>
                <dd>{{ $policy->category ?? '—' }}</dd>
                <dt class="text-slate-500">Klasyfikacja</dt>
                <dd>{{ $policy->classification ?? '—' }}</dd>
                <dt class="text-slate-500">Właściciel</dt>
                <dd>{{ $policy->owner?->name ?? $policy->owner_role ?? '—' }}</dd>
                <dt class="text-slate-500">Odbiorcy</dt>
                <dd>{{ $policy->audience ?? '—' }}</dd>
                <dt class="text-slate-500">Obowiązuje od</dt>
                <dd>{{ $policy->effective_from?->format('Y-m-d') ?? '—' }}</dd>
                <dt class="text-slate-500">Cykl przeglądu</dt>
                <dd>{{ $policy->review_cycle_months ? $policy->review_cycle_months.' mies.' : '—' }}</dd>
                <dt class="text-slate-500">Termin przeglądu</dt>
                <dd class="{{ $policy->next_review_due?->isPast() ? 'text-red-700 font-semibold' : '' }}">
                    {{ $policy->next_review_due?->format('Y-m-d') ?? '—' }}
                </dd>
                <dt class="text-slate-500">Polityka nadrzędna</dt>
                <dd>
                    @if($policy->parentPolicy)
                        <a href="{{ route('policies.show', $policy->parentPolicy) }}" class="text-blue-600 hover:underline">{{ $policy->parentPolicy->code }}</a>
                    @else
                        —
                    @endif
                </dd>
                <dt class="text-slate-500">Zastępuje</dt>
                <dd>
                    @if($policy->supersedes)
                        <a href="{{ route('policies.show', $policy->supersedes) }}" class="text-blue-600 hover:underline">{{ $policy->supersedes->code }}</a>
                    @else
                        —
                    @endif
                </dd>
                <dt class="text-slate-500">Zatwierdził</dt>
                <dd>{{ $policy->approver?->name ?? '—' }}</dd>
                <dt class="text-slate-500">Data zatwierdzenia</dt>
                <dd>{{ $policy->approved_at?->format('Y-m-d') ?? '—' }}</dd>
            </dl>
            @if($policy->supersededBy->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-amber-100 bg-amber-50 -mx-5 px-5 py-3">
                <p class="text-sm text-amber-800">
                    Zastąpiona pełną treścią:
                    @foreach($policy->supersededBy as $newer)
                        <a href="{{ route('policies.show', $newer) }}" class="font-medium text-amber-900 hover:underline">{{ $newer->code }}</a>@if(!$loop->last), @endif
                    @endforeach
                </p>
            </div>
            @endif
            @if($policy->scope_description)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <h3 class="text-xs font-semibold text-slate-500 uppercase mb-1">Zakres</h3>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $policy->scope_description }}</p>
            </div>
            @endif
            @if($policy->description)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $policy->description }}</p>
            </div>
            @endif
        </div>

        @if($policy->childPolicies->isNotEmpty())
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-slate-700 text-sm mb-2">Polityki podrzędne ({{ $policy->childPolicies->count() }})</h3>
            <div class="flex flex-wrap gap-1.5">
                @foreach($policy->childPolicies as $child)
                    <a href="{{ route('policies.show', $child) }}" class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700 hover:bg-slate-200 font-mono">{{ $child->code }}</a>
                @endforeach
            </div>
        </div>
        @endif

        @if($policy->controls->isNotEmpty())
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Kontrole ({{ $policy->controls->count() }})</h2>
            <div class="space-y-2">
                @foreach($policy->controls as $control)
                @php
                    $ctlStatusBg = match($control->status) {
                        'implemented'    => 'bg-emerald-100 text-emerald-800',
                        'partial'        => 'bg-amber-100 text-amber-800',
                        'planned'        => 'bg-slate-200 text-slate-600',
                        'not_applicable' => 'bg-slate-100 text-slate-500',
                        'exception'      => 'bg-purple-100 text-purple-800',
                        default          => 'bg-slate-100 text-slate-600',
                    };
                @endphp
                <div class="border border-slate-100 rounded p-2.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono text-xs text-slate-500">{{ $control->control_code }}</span>
                        <span class="text-sm font-medium">{{ $control->title }}</span>
                        <span class="px-1.5 py-0.5 rounded text-xs {{ $ctlStatusBg }}">{{ $control->status }}</span>
                        <span class="text-xs text-slate-400">{{ $control->control_type }} / {{ $control->implementation_type }}</span>
                    </div>
                    @if($control->frameworkMappings->isNotEmpty())
                    <div class="mt-1.5 flex flex-wrap gap-1">
                        @foreach($control->frameworkMappings as $m)
                            <span class="px-1.5 py-0.5 rounded text-[11px] bg-blue-50 text-blue-700" title="{{ $m->control_ref }} ({{ $m->mapping_type }})">{{ $m->framework_code }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($policy->framework_mappings))
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-slate-700 text-sm mb-2">Mapowania standardów</h3>
            <div class="flex flex-wrap gap-1">
                @foreach($policy->framework_mappings as $m)
                    <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-800">{{ $m }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if($policy->attestations->isNotEmpty())
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Potwierdzenia zapoznania ({{ $policy->attestations->count() }})</h2>
            <div class="space-y-1.5 max-h-60 overflow-y-auto">
                @foreach($policy->attestations->sortByDesc('attested_at') as $att)
                <div class="flex items-center justify-between text-xs p-1.5 rounded hover:bg-slate-50">
                    <span>{{ $att->user?->name ?? '—' }}</span>
                    <span class="text-slate-500">v{{ $att->policy_version }} · {{ $att->attested_at->format('Y-m-d H:i') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Documents (uploaded files + Google Drive links) --}}
        <div class="bg-white rounded shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-700">Dokumenty</h2>
                @if(!$driveApiEnabled)
                <span class="text-xs text-slate-400">Synchronizacja Drive: API wyłączone</span>
                @endif
            </div>

            @forelse($policy->documentLinks as $link)
            @php $doc = $link->evidence; @endphp
            @if($doc)
            <div class="flex items-center justify-between p-2 rounded hover:bg-slate-50 text-sm border-b border-slate-50 last:border-0">
                @if($doc->source === 'upload')
                <a href="{{ route('policies.documents.download', [$policy, $link]) }}" class="text-emerald-700 hover:underline truncate max-w-xs">
                    📎 {{ $doc->title ?? $doc->original_filename }}
                </a>
                @else
                <a href="{{ $doc->external_url }}" target="_blank" rel="noopener" class="text-emerald-700 hover:underline truncate max-w-xs">
                    {{ $doc->title ?? $doc->original_filename }}
                </a>
                @endif
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    @if($doc->external_synced_at)
                        <span title="Ostatnia synchronizacja">↻ {{ $doc->external_synced_at->format('Y-m-d H:i') }}</span>
                    @endif
                    @can('policy.update')
                        @if($driveApiEnabled && $doc->external_file_id)
                        <form method="POST" action="{{ route('policies.documents.sync', [$policy, $link]) }}">
                            @csrf
                            <button class="px-2 py-1 border border-slate-300 rounded hover:bg-slate-100">Synchronizuj</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('policies.documents.destroy', [$policy, $link]) }}" onsubmit="return confirm('Odpiąć dokument?')">
                            @csrf
                            @method('DELETE')
                            <button class="px-2 py-1 text-red-600 hover:bg-red-50 rounded">Odepnij</button>
                        </form>
                    @endcan
                </div>
            </div>
            @endif
            @empty
            <p class="text-sm text-slate-400">Brak podpiętych dokumentów.</p>
            @endforelse

            @can('policy.update')
            <div class="mt-3 pt-3 border-t border-slate-100 space-y-3">
                <form method="POST" action="{{ route('policies.documents.store', $policy) }}" enctype="multipart/form-data" class="flex gap-2">
                    @csrf
                    <input type="file" name="file" required class="flex-1 text-xs">
                    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-xs whitespace-nowrap">Wgraj plik</button>
                </form>
                @error('file')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror

                <form method="POST" action="{{ route('policies.documents.store', $policy) }}" class="space-y-2">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="title" placeholder="Nazwa dokumentu (opcjonalnie)" class="px-2 py-1.5 border border-slate-300 rounded text-xs">
                        <input type="text" name="drive_file_id" placeholder="ID pliku Drive (opcjonalnie, do synchronizacji API)" class="px-2 py-1.5 border border-slate-300 rounded text-xs">
                    </div>
                    <div class="flex gap-2">
                        <input type="url" name="drive_url" required placeholder="lub link: https://drive.google.com/…" class="flex-1 px-2 py-1.5 border border-slate-300 rounded text-xs">
                        <button class="px-3 py-1.5 border border-slate-300 rounded text-xs whitespace-nowrap">Podepnij link</button>
                    </div>
                    @error('drive_url')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
                </form>
            </div>
            @endcan
        </div>

        {{-- Version history --}}
        @if($policy->versions->isNotEmpty())
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Historia wersji ({{ $policy->versions->count() }})</h2>
            <div class="space-y-1.5 max-h-72 overflow-y-auto">
                @foreach($policy->versions as $v)
                <div class="text-xs p-1.5 rounded hover:bg-slate-50 border-b border-slate-50 last:border-0">
                    <div class="flex items-center justify-between">
                        <span class="font-mono">v{{ $v->version_number }}</span>
                        <span class="text-slate-500">{{ $v->changed_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="text-slate-500">{{ $v->author?->name ?? 'System' }}@if($v->change_reason) — {{ $v->change_reason }}@endif</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4 text-xs text-slate-500 space-y-1.5">
            <div>Kod: <span class="font-mono text-slate-700">{{ $policy->code }}</span></div>
            <div>Wersja: <span class="font-mono text-slate-700">v{{ $policy->current_version }}</span></div>
            <div>Właściciel: <span class="text-slate-700">{{ $policy->owner?->name ?? '—' }}</span></div>
            <div>Attestation wymagane: <span class="text-slate-700">{{ $policy->attestation_required ? 'Tak' : 'Nie' }}</span></div>
            <div>Utworzono: {{ $policy->created_at->format('Y-m-d H:i') }}</div>
        </div>
    </div>
</div>
@endsection
