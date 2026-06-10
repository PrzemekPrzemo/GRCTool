@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('evidence.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Rejestr dowodów</a>
</div>

@php
    $classificationBadge = match($evidence->classification) {
        'Public'       => 'bg-slate-100 text-slate-700',
        'Internal'     => 'bg-blue-100 text-blue-700',
        'Confidential' => 'bg-amber-100 text-amber-700',
        'Restricted'   => 'bg-red-100 text-red-700',
        default        => 'bg-slate-100 text-slate-600',
    };
    $expiryStatus = $evidence->expiryStatus();
    $expiryBadge = match($expiryStatus) {
        'valid'         => 'bg-emerald-100 text-emerald-700',
        'expiring_soon' => 'bg-amber-100 text-amber-700',
        'expired'       => 'bg-red-100 text-red-700',
        'no_expiry'     => 'bg-slate-100 text-slate-500',
        default         => 'bg-slate-100 text-slate-500',
    };
    $expiryLabel = match($expiryStatus) {
        'valid'         => 'Ważny',
        'expiring_soon' => 'Wygasa wkrótce',
        'expired'       => 'Przeterminowany',
        'no_expiry'     => 'Bez daty ważności',
        default         => '—',
    };
@endphp

{{-- Header --}}
<div class="flex items-start justify-between mb-4">
    <div class="flex items-center gap-2 flex-wrap">
        <h1 class="text-2xl font-semibold text-slate-800">{{ $evidence->title }}</h1>
        <span class="px-2 py-0.5 rounded text-xs {{ $classificationBadge }}">{{ $evidence->classification }}</span>
        <span class="px-2 py-0.5 rounded text-xs {{ $expiryBadge }}">{{ $expiryLabel }}</span>
    </div>
    @can('evidence.update')
    @unless($evidence->is_immutable)
    <a href="{{ route('evidence.edit', $evidence) }}" class="px-3 py-1.5 bg-slate-100 rounded text-sm hover:bg-slate-200 shrink-0">Edytuj</a>
    @endunless
    @endcan
</div>

{{-- Status flash --}}
@if(session('status'))
<div class="mb-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded px-3 py-2 text-sm">{{ session('status') }}</div>
@endif
@if(session('error'))
<div class="mb-3 bg-red-50 border border-red-200 text-red-800 rounded px-3 py-2 text-sm">{{ session('error') }}</div>
@endif

{{-- Immutable banner --}}
@if($evidence->is_immutable)
<div class="mb-4 flex items-center gap-2 bg-slate-50 border border-slate-300 rounded-lg px-4 py-3 text-sm text-slate-700">
    <span class="text-lg">&#128274;</span>
    <span><strong>Ten dowód jest niezmienialny</strong> — edycja i usuwanie są zablokowane.</span>
</div>
@endif

{{-- Expired banner --}}
@if($evidence->isStale())
<div class="mb-4 flex items-center gap-2 bg-red-50 border border-red-300 rounded-lg px-4 py-3 text-sm text-red-700">
    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    <span><strong>PRZETERMINOWANY</strong> od {{ $evidence->valid_until->format('Y-m-d') }}</span>
</div>
@elseif($evidence->isExpiring())
@php $daysLeft = (int) now()->diffInDays($evidence->valid_until, false); @endphp
<div class="mb-4 flex items-center gap-2 bg-amber-50 border border-amber-300 rounded-lg px-4 py-3 text-sm text-amber-700">
    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>Wygasa za <strong>{{ $daysLeft }} {{ $daysLeft === 1 ? 'dzień' : 'dni' }}</strong> ({{ $evidence->valid_until->format('Y-m-d') }})</span>
</div>
@endif

<div class="grid grid-cols-3 gap-4">
    {{-- Main metadata --}}
    <div class="col-span-2 space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4 uppercase tracking-wide">Metadane</h2>

            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <span class="block text-xs text-slate-500 mb-0.5">Tytuł</span>
                    <span class="font-medium">{{ $evidence->title }}</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-500 mb-0.5">Klasyfikacja</span>
                    <span class="px-1.5 py-0.5 rounded text-xs {{ $classificationBadge }}">{{ $evidence->classification }}</span>
                </div>

                @if($evidence->description)
                <div class="col-span-2">
                    <span class="block text-xs text-slate-500 mb-0.5">Opis</span>
                    <p class="text-slate-700 whitespace-pre-line">{{ $evidence->description }}</p>
                </div>
                @endif

                <div>
                    <span class="block text-xs text-slate-500 mb-0.5">Ważne od</span>
                    <span>{{ $evidence->valid_from ? $evidence->valid_from->format('Y-m-d') : '—' }}</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-500 mb-0.5">Ważne do</span>
                    <span>{{ $evidence->valid_until ? $evidence->valid_until->format('Y-m-d') : '—' }}</span>
                </div>

                <div>
                    <span class="block text-xs text-slate-500 mb-0.5">Retencja do</span>
                    <span>{{ $evidence->retention_until ? $evidence->retention_until->format('Y-m-d') : '—' }}</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-500 mb-0.5">Plik</span>
                    <span class="text-slate-700">{{ $evidence->original_filename ?? '—' }}</span>
                </div>

                <div>
                    <span class="block text-xs text-slate-500 mb-0.5">Przesłał</span>
                    <span>{{ $evidence->uploader?->name ?? '—' }}</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-500 mb-0.5">Klient</span>
                    <span>{{ $evidence->client?->name ?? '—' }}</span>
                </div>

                @if($evidence->sha256)
                <div class="col-span-2">
                    <span class="block text-xs text-slate-500 mb-0.5">SHA-256</span>
                    <span class="font-mono text-xs text-slate-600 bg-slate-50 rounded px-2 py-1 block truncate">{{ $evidence->sha256 }}</span>
                </div>
                @endif

                @if(!empty($evidence->tags))
                <div class="col-span-2">
                    <span class="block text-xs text-slate-500 mb-1">Tagi</span>
                    <div class="flex flex-wrap gap-1">
                        @foreach($evidence->tags as $tag)
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-xs">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Links section --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4 uppercase tracking-wide">Powiązania</h2>
            @if($evidence->links->isEmpty())
            <p class="text-sm text-slate-400">Brak powiązań z innymi obiektami.</p>
            @else
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Typ obiektu</th>
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Rola</th>
                        <th class="px-3 py-2">Dodano</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evidence->links as $link)
                    @php
                        $shortType = class_basename($link->linkable_type);
                    @endphp
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-3 py-2 text-slate-700">{{ $shortType }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $link->linkable_id }}</td>
                        <td class="px-3 py-2 text-xs text-slate-600">{{ $link->relation_role ?? '—' }}</td>
                        <td class="px-3 py-2 text-xs text-slate-500">{{ $link->created_at->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h2 class="font-semibold text-sm mb-3 text-slate-700">Akcje</h2>
            @can('evidence.update')
            @unless($evidence->is_immutable)
            <a href="{{ route('evidence.edit', $evidence) }}" class="block w-full text-center px-3 py-1.5 bg-slate-100 rounded text-sm hover:bg-slate-200 mb-2">Edytuj</a>
            @endunless
            @endcan
            @can('evidence.delete')
            @unless($evidence->is_immutable)
            <form method="POST" action="{{ route('evidence.destroy', $evidence) }}">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Usunąć ten dowód?')" class="w-full px-3 py-1.5 bg-red-600 text-white rounded text-sm hover:bg-red-700">Usuń</button>
            </form>
            @endunless
            @endcan
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h2 class="font-semibold text-sm mb-3 text-slate-700">Informacje</h2>
            <dl class="text-xs space-y-2 text-slate-600">
                <div>
                    <dt class="text-slate-400">UUID</dt>
                    <dd class="font-mono truncate">{{ $evidence->uuid }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">Dodano</dt>
                    <dd>{{ $evidence->created_at->format('Y-m-d H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">Ostatnia zmiana</dt>
                    <dd>{{ $evidence->updated_at->format('Y-m-d H:i') }}</dd>
                </div>
                @if($evidence->mime_type)
                <div>
                    <dt class="text-slate-400">MIME</dt>
                    <dd>{{ $evidence->mime_type }}</dd>
                </div>
                @endif
                @if($evidence->size_bytes)
                <div>
                    <dt class="text-slate-400">Rozmiar</dt>
                    <dd>{{ number_format($evidence->size_bytes / 1024, 1) }} KB</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>
</div>

@endsection
