@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('exceptions.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Wyjątki</a>
</div>

@php
    $statusBg = ['draft'=>'bg-slate-100 text-slate-600','pending_approval'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700','expired'=>'bg-slate-200 text-slate-500'][$exception->status] ?? 'bg-slate-100';
    $statusLabel = \App\Models\ComplianceException::STATUS_LABELS[$exception->status] ?? $exception->status;
    $typeLabel = \App\Models\ComplianceException::TYPE_LABELS[$exception->exception_type] ?? $exception->exception_type;
@endphp

<div class="grid grid-cols-3 gap-4">
    <div class="col-span-2 space-y-4">
        <div class="bg-white rounded shadow p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="font-mono text-xs text-slate-500">{{ $exception->code }}</span>
                    <h1 class="text-xl font-semibold">{{ $exception->title }}</h1>
                </div>
                <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">{{ $statusLabel }}</span>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                <div><span class="text-slate-500">Typ:</span> {{ $typeLabel }}</div>
                <div><span class="text-slate-500">Wnioskował:</span> {{ $exception->requester?->name ?? '—' }}</div>
                <div><span class="text-slate-500">Zatwierdził:</span> {{ $exception->approver?->name ?? '—' }}</div>
                <div><span class="text-slate-500">Data zatwierdzenia:</span> {{ $exception->approved_at?->format('Y-m-d') ?? '—' }}</div>
                <div><span class="text-slate-500">Wygasa:</span>
                    @if($exception->expires_at)
                    <span class="{{ $exception->isExpired() ? 'text-red-600 font-medium' : '' }}">{{ $exception->expires_at->format('Y-m-d') }}</span>
                    @else —
                    @endif
                </div>
            </div>

            <div class="mb-3">
                <div class="text-sm font-medium text-slate-700 mb-1">Uzasadnienie</div>
                <p class="text-sm text-slate-600 bg-slate-50 rounded p-3">{{ $exception->rationale }}</p>
            </div>

            @if($exception->compensating_controls)
            <div class="mb-3">
                <div class="text-sm font-medium text-slate-700 mb-1">Kontrole kompensujące</div>
                <p class="text-sm text-slate-600 bg-slate-50 rounded p-3">{{ $exception->compensating_controls }}</p>
            </div>
            @endif

            @if(!empty($exception->affected_frameworks))
            <div>
                <div class="text-sm font-medium text-slate-700 mb-1">Powiązane frameworki</div>
                <div class="flex flex-wrap gap-1">
                    @foreach($exception->affected_frameworks as $fw)
                    <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700">{{ $fw }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            @if($exception->rejection_reason)
            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded text-sm">
                <span class="font-medium text-red-800">Powód odrzucenia:</span>
                <p class="text-red-700 mt-1">{{ $exception->rejection_reason }}</p>
            </div>
            @endif
        </div>

        {{-- Timeline --}}
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold text-sm mb-3">Historia</h2>
            <div class="space-y-2 text-sm text-slate-600">
                <div class="flex gap-3">
                    <span class="text-slate-400">{{ $exception->created_at->format('Y-m-d') }}</span>
                    <span>Wyjątek utworzony przez {{ $exception->requester?->name }}</span>
                </div>
                @if($exception->status === 'pending_approval' || $exception->status === 'approved' || $exception->status === 'rejected')
                <div class="flex gap-3">
                    <span class="text-slate-400">—</span>
                    <span>Przesłany do zatwierdzenia</span>
                </div>
                @endif
                @if($exception->approved_at)
                <div class="flex gap-3">
                    <span class="text-slate-400">{{ $exception->approved_at->format('Y-m-d') }}</span>
                    <span>Zatwierdzony przez {{ $exception->approver?->name }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold text-sm mb-3">Akcje</h2>
            <a href="{{ route('exceptions.edit', $exception) }}" class="block w-full text-center px-3 py-1.5 bg-slate-100 rounded text-sm hover:bg-slate-200 mb-2">Edytuj</a>

            @if($exception->status === 'draft')
            <form method="POST" action="{{ route('exceptions.submit', $exception) }}" class="mb-2">
                @csrf
                <button type="submit" class="w-full px-3 py-1.5 bg-amber-500 text-white rounded text-sm hover:bg-amber-600">Prześlij do zatwierdzenia</button>
            </form>
            @endif

            @if($exception->status === 'pending_approval')
            <form method="POST" action="{{ route('exceptions.approve', $exception) }}" class="mb-2">
                @csrf
                <button type="submit" class="w-full px-3 py-1.5 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700">Zatwierdź</button>
            </form>

            <form method="POST" action="{{ route('exceptions.reject', $exception) }}" class="space-y-2">
                @csrf
                <textarea name="rejection_reason" placeholder="Powód odrzucenia (wymagany)" required rows="2" class="w-full border border-slate-300 rounded px-2 py-1 text-sm"></textarea>
                <button type="submit" class="w-full px-3 py-1.5 bg-red-600 text-white rounded text-sm hover:bg-red-700">Odrzuć</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
