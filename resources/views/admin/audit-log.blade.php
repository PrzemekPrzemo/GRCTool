@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-slate-900">Dziennik audytu</h1>
        <span class="text-sm text-slate-500">{{ $logs->total() }} wpisów</span>
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-4 bg-white rounded-xl border border-slate-200 p-4">
        <select name="action" class="px-3 py-1.5 border border-slate-200 rounded-lg text-sm">
            <option value="">Wszystkie akcje</option>
            <option value="login" {{ ($filters['action'] ?? '') === 'login' ? 'selected' : '' }}>login</option>
            <option value="login_google" {{ ($filters['action'] ?? '') === 'login_google' ? 'selected' : '' }}>login_google</option>
            <option value="login_failed" {{ ($filters['action'] ?? '') === 'login_failed' ? 'selected' : '' }}>login_failed</option>
            <option value="logout" {{ ($filters['action'] ?? '') === 'logout' ? 'selected' : '' }}>logout</option>
            <option value="created" {{ ($filters['action'] ?? '') === 'created' ? 'selected' : '' }}>created</option>
            <option value="updated" {{ ($filters['action'] ?? '') === 'updated' ? 'selected' : '' }}>updated</option>
            <option value="deleted" {{ ($filters['action'] ?? '') === 'deleted' ? 'selected' : '' }}>deleted</option>
        </select>
        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
               class="px-3 py-1.5 border border-slate-200 rounded-lg text-sm" placeholder="Od">
        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
               class="px-3 py-1.5 border border-slate-200 rounded-lg text-sm" placeholder="Do">
        <button type="submit" class="px-4 py-1.5 bg-slate-800 text-white rounded-lg text-sm hover:bg-slate-700">Filtruj</button>
        <a href="{{ route('admin.audit-log') }}" class="px-4 py-1.5 border border-slate-200 text-slate-600 rounded-lg text-sm hover:bg-slate-50">Reset</a>
    </form>

    {{-- Log table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Czas</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Użytkownik</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Akcja</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Obiekt</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2.5 text-slate-500 text-xs font-mono whitespace-nowrap">
                        {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '—' }}
                    </td>
                    <td class="px-4 py-2.5">
                        @if($log->user)
                            <div class="flex items-center gap-1.5">
                                @if($log->user->avatar_url)
                                    <img src="{{ $log->user->avatar_url }}" class="w-5 h-5 rounded-full" alt="">
                                @endif
                                <span class="font-medium text-slate-700">{{ $log->user->name }}</span>
                                <span class="text-slate-400 text-xs">{{ $log->user->email }}</span>
                            </div>
                        @else
                            <span class="text-slate-400 text-xs">system / anonimowy</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5">
                        @php
                            $actionColor = match(true) {
                                str_contains($log->action, 'failed') || str_contains($log->action, 'rejected') => 'bg-red-100 text-red-700',
                                str_contains($log->action, 'login') => 'bg-emerald-100 text-emerald-700',
                                str_contains($log->action, 'logout') => 'bg-slate-100 text-slate-600',
                                str_contains($log->action, 'deleted') => 'bg-orange-100 text-orange-700',
                                default => 'bg-blue-100 text-blue-700',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $actionColor }}">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-xs text-slate-500">
                        @if($log->subject_type)
                            {{ class_basename($log->subject_type) }}
                            @if($log->subject_id) #{{ $log->subject_id }} @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-xs font-mono text-slate-400">
                        {{ data_get($log->context, 'ip', $log->context['ip'] ?? '—') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-400">Brak wpisów dla wybranych filtrów</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">{{ $logs->withQueryString()->links() }}</div>
</div>
@endsection
