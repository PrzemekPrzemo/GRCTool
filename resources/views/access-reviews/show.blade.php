@extends('layouts.app')
@section('content')

@php
    $statusBadge = [
        'draft'     => 'bg-slate-100 text-slate-600',
        'active'    => 'bg-blue-100 text-blue-700',
        'completed' => 'bg-emerald-100 text-emerald-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ][$campaign->status] ?? 'bg-slate-100';
    $statusLabel = \App\Models\AccessReviewCampaign::STATUS_LABELS[$campaign->status] ?? $campaign->status;
    $scopeLabel  = \App\Models\AccessReviewCampaign::SCOPE_LABELS[$campaign->scope] ?? $campaign->scope;
    $isOverdue   = $campaign->isOverdue();
    $pct         = $campaign->progressPct();
@endphp

<div class="mb-4">
    <a href="{{ route('access-reviews.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Przeglądy dostępów</a>
</div>

{{-- Header --}}
<div class="flex items-start justify-between mb-4">
    <div>
        <span class="font-mono text-xs text-slate-500">{{ $campaign->code }}</span>
        <h1 class="text-2xl font-semibold">{{ $campaign->title }}</h1>
        <div class="mt-1">
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">{{ $statusLabel }}</span>
        </div>
    </div>
    <div class="flex gap-2">
        @can('access_review.update')
        <a href="{{ route('access-reviews.edit', $campaign) }}" class="px-3 py-1.5 bg-slate-100 rounded-lg text-sm hover:bg-slate-200 transition-colors">Edytuj</a>
        @if($campaign->status === 'draft')
        <form method="POST" action="{{ route('access-reviews.launch', $campaign) }}">
            @csrf
            <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors">Uruchom kampanię</button>
        </form>
        @endif
        @if($campaign->status === 'active')
        <form method="POST" action="{{ route('access-reviews.complete', $campaign) }}">
            @csrf
            <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 transition-colors">Zakończ kampanię</button>
        </form>
        @endif
        @endcan
    </div>
</div>

<div class="grid grid-cols-3 gap-4">
    <div class="col-span-2 space-y-4">

        {{-- Campaign info card --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                <div><span class="text-slate-500">Zakres:</span> {{ $scopeLabel }}{{ $campaign->scope_value ? ' — '.$campaign->scope_value : '' }}</div>
                <div><span class="text-slate-500">Właściciel:</span> {{ $campaign->owner?->name ?? '—' }}</div>
                <div class="{{ $isOverdue ? 'text-red-600 font-medium' : '' }}">
                    <span class="text-slate-500">Termin:</span>
                    {{ $campaign->due_date?->format('Y-m-d') ?? '—' }}
                    @if($isOverdue)<span class="ml-1 text-red-500 text-xs">PRZEKROCZONY</span>@endif
                </div>
                <div><span class="text-slate-500">Odwołane:</span> <span class="font-medium text-red-600">{{ $campaign->revoked_items }}</span></div>
                @if($campaign->review_period_start)
                <div><span class="text-slate-500">Okres przeglądu:</span> {{ $campaign->review_period_start->format('Y-m-d') }} — {{ $campaign->review_period_end?->format('Y-m-d') ?? '?' }}</div>
                @endif
                @if($campaign->completed_at)
                <div><span class="text-slate-500">Zakończono:</span> {{ $campaign->completed_at->format('Y-m-d H:i') }}</div>
                @endif
            </div>
            {{-- Progress bar --}}
            <div>
                <div class="flex justify-between text-xs text-slate-500 mb-1">
                    <span>Postęp przeglądu</span>
                    <span>{{ $campaign->reviewed_items }} / {{ $campaign->total_items }} ({{ $pct }}%)</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2">
                    <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @if($campaign->description)
            <p class="mt-3 text-sm text-slate-600">{{ $campaign->description }}</p>
            @endif
        </div>

        {{-- Filter tabs + Items table --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                <div class="flex gap-1">
                    @foreach(['all' => 'Wszystkie', 'pending' => 'Oczekujące', 'approved' => 'Zatwierdzone', 'revoked' => 'Odwołane'] as $tabKey => $tabLabel)
                    <a href="{{ request()->fullUrlWithQuery(['tab' => $tabKey]) }}"
                       class="px-3 py-1 rounded text-xs font-medium transition-colors {{ $tab === $tabKey ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                        {{ $tabLabel }}
                        <span class="ml-1 text-xs opacity-70">
                            ({{ $items->where('status', $tabKey === 'all' ? '!!' : $tabKey)->count() ?: $items->count() }})
                        </span>
                    </a>
                    @endforeach
                </div>
                @can('access_review.update')
                @if($campaign->status === 'active' && $items->where('status','pending')->where('reviewer_id', auth()->id())->count() > 0)
                <form method="POST" action="{{ route('access-reviews.bulk_approve', $campaign) }}">
                    @csrf
                    <button type="submit" class="px-3 py-1 bg-emerald-600 text-white rounded text-xs font-medium hover:bg-emerald-700 transition-colors">
                        Zatwierdź wszystkie moje ({{ $items->where('status','pending')->where('reviewer_id', auth()->id())->count() }})
                    </button>
                </form>
                @endif
                @endcan
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 text-left">
                    <tr>
                        <th class="px-4 py-2">Użytkownik</th>
                        <th class="px-4 py-2">System</th>
                        <th class="px-4 py-2">Rola dostępu</th>
                        <th class="px-4 py-2">Zakres</th>
                        <th class="px-4 py-2">Ostatnie użycie</th>
                        <th class="px-4 py-2">Reviewer</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Akcja</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($filteredItems as $item)
                    @php
                        $itemBadge = [
                            'pending'      => 'bg-amber-100 text-amber-700',
                            'approved'     => 'bg-emerald-100 text-emerald-700',
                            'revoked'      => 'bg-red-100 text-red-700',
                            'modified'     => 'bg-blue-100 text-blue-700',
                            'not_reviewed' => 'bg-slate-100 text-slate-600',
                        ][$item->status] ?? 'bg-slate-100';
                        $itemLabel = \App\Models\AccessReviewItem::STATUS_LABELS[$item->status] ?? $item->status;
                        $subjectName = $item->subjectUser?->name ?? $item->subject_name ?? '—';
                        $canDecide = $campaign->status === 'active' && $item->status === 'pending'
                            && (auth()->id() === $item->reviewer_id || auth()->id() === $campaign->owner_id);
                    @endphp
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-2 font-medium text-xs">{{ $subjectName }}</td>
                        <td class="px-4 py-2 text-xs">{{ $item->system_name }}</td>
                        <td class="px-4 py-2 text-xs">
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">{{ $item->access_role }}</span>
                        </td>
                        <td class="px-4 py-2 text-xs text-slate-500">{{ $item->access_scope ?? '—' }}</td>
                        <td class="px-4 py-2 text-xs text-slate-500">{{ $item->last_used_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-4 py-2 text-xs text-slate-600">{{ $item->reviewer?->name ?? '—' }}</td>
                        <td class="px-4 py-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $itemBadge }}">{{ $itemLabel }}</span>
                        </td>
                        <td class="px-4 py-2">
                            @if($canDecide)
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" type="button"
                                        class="text-xs px-2 py-0.5 bg-slate-100 rounded hover:bg-slate-200 transition-colors">Decyzja</button>
                                <div x-show="open" @click.outside="open = false"
                                     class="absolute right-0 z-10 mt-1 w-72 bg-white rounded-xl shadow-lg border border-slate-200 p-3">
                                    <form method="POST" action="{{ route('access-reviews.item.decide', [$campaign, $item]) }}" class="space-y-2">
                                        @csrf
                                        <div class="flex gap-2">
                                            <button type="submit" name="status" value="approved"
                                                    class="flex-1 py-1 bg-emerald-600 text-white rounded text-xs hover:bg-emerald-700">Zatwierdź</button>
                                            <button type="submit" name="status" value="revoked"
                                                    class="flex-1 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700">Odwołaj</button>
                                            <button type="submit" name="status" value="modified"
                                                    class="flex-1 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">Zmodyfikuj</button>
                                        </div>
                                        <textarea name="decision_note" rows="2" placeholder="Notatka (wymagana dla odwołania/modyfikacji)"
                                                  class="w-full border border-slate-300 rounded px-2 py-1 text-xs"></textarea>
                                    </form>
                                </div>
                            </div>
                            @elseif($item->decision_note)
                            <span class="text-xs text-slate-400" title="{{ $item->decision_note }}">✓ Decyzja</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400 text-sm">Brak elementów</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add Item form (collapsible) --}}
        @can('access_review.update')
        @if($campaign->status !== 'completed' && $campaign->status !== 'cancelled')
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5" x-data="{ open: false }">
            <button @click="open = !open" type="button"
                    class="w-full flex items-center justify-between text-sm font-semibold text-slate-700 hover:text-slate-900">
                <span>+ Dodaj element do przeglądu</span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-transition class="mt-4">
                <form method="POST" action="{{ route('access-reviews.item.add', $campaign) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1">Użytkownik (z systemu)</label>
                            <select name="subject_user_id" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                <option value="">— wybierz lub wpisz ręcznie —</option>
                                @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Użytkownik (nazwa ręczna)</label>
                            <input type="text" name="subject_name" placeholder="np. john.doe@external.com"
                                   class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1">System *</label>
                            <input type="text" name="system_name" required placeholder="np. AWS, GitHub"
                                   class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Rola dostępu *</label>
                            <input type="text" name="access_role" required placeholder="np. Admin, Read-only"
                                   class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Zakres dostępu</label>
                            <input type="text" name="access_scope" placeholder="np. prod-aws, github/org"
                                   class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1">Reviewer</label>
                            <select name="reviewer_id" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                <option value="">— brak —</option>
                                @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Ostatnie użycie</label>
                            <input type="date" name="last_used_at" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                        <div></div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Uzasadnienie dostępu</label>
                        <textarea name="justification" rows="2" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm"></textarea>
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm font-medium hover:bg-emerald-700 transition-colors">Dodaj element</button>
                </form>
            </div>
        </div>
        @endif
        @endcan

    </div>

    {{-- Right sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h2 class="font-semibold text-sm text-slate-700 mb-3">Podsumowanie</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Wszystkich</span>
                    <span class="font-medium">{{ $items->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Oczekujących</span>
                    <span class="font-medium text-amber-600">{{ $items->where('status','pending')->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Zatwierdzonych</span>
                    <span class="font-medium text-emerald-600">{{ $items->where('status','approved')->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Odwołanych</span>
                    <span class="font-medium text-red-600">{{ $items->where('status','revoked')->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Zmodyfikowanych</span>
                    <span class="font-medium text-blue-600">{{ $items->where('status','modified')->count() }}</span>
                </div>
            </div>
        </div>

        @if($campaign->notes)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h2 class="font-semibold text-sm text-slate-700 mb-2">Notatki</h2>
            <p class="text-sm text-slate-600">{{ $campaign->notes }}</p>
        </div>
        @endif
    </div>
</div>

@endsection
