@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-4">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" class="w-14 h-14 rounded-full border-2 border-slate-200">
            @else
                <div class="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-xl">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h1 class="text-xl font-bold text-slate-900">{{ $user->name }}</h1>
                <p class="text-slate-500 text-sm">{{ $user->email }}</p>
                <div class="flex items-center gap-2 mt-1">
                    @foreach($user->roles as $role)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700">{{ $role->name }}</span>
                    @endforeach
                    @if($user->auth_provider === 'google')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">Google</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">Lokalne</span>
                    @endif
                    @if(!$user->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Dezaktywowane</span>
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm">Edytuj</a>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <div class="text-xs text-slate-500 mb-1">Ostatnie logowanie</div>
            <div class="text-sm font-semibold text-slate-800">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '—' }}</div>
            <div class="text-xs text-slate-400 font-mono">{{ $user->last_login_ip ?? '' }}</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <div class="text-xs text-slate-500 mb-1">Sesje łącznie</div>
            <div class="text-2xl font-bold text-slate-800">{{ $sessions->count() }}</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <div class="text-xs text-slate-500 mb-1">Działania w systemie</div>
            <div class="text-2xl font-bold text-slate-800">{{ $activityLog->count() }}</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <div class="text-xs text-slate-500 mb-1">Konto aktywne od</div>
            <div class="text-sm font-semibold text-slate-800">{{ $user->created_at->format('d.m.Y') }}</div>
        </div>
    </div>

    {{-- Sessions history --}}
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800 text-sm">Historia sesji (ostatnie 50)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500">Logowanie</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500">Wylogowanie</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500">Czas trwania</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500">IP</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500">Metoda</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500">Przeglądarka</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($sessions as $session)
                    <tr class="{{ $session->isActive() ? 'bg-emerald-50' : '' }}">
                        <td class="px-4 py-2.5 font-mono text-xs text-slate-600">{{ $session->logged_in_at->format('Y-m-d H:i:s') }}</td>
                        <td class="px-4 py-2.5 font-mono text-xs text-slate-400">
                            {{ $session->logged_out_at ? $session->logged_out_at->format('Y-m-d H:i:s') : '—' }}
                            @if($session->isActive()) <span class="ml-1 text-emerald-600 text-xs font-medium">aktywna</span> @endif
                        </td>
                        <td class="px-4 py-2.5 text-xs text-slate-500">{{ $session->duration() }}</td>
                        <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $session->ip_address ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            @if($session->auth_provider === 'google')
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-blue-100 text-blue-700">Google</span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-slate-100 text-slate-600">Hasło</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-xs text-slate-400 max-w-xs truncate">{{ Str::limit($session->user_agent ?? '', 60) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">Brak historii sesji</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Activity log --}}
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-5 py-3 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800 text-sm">Aktywność w systemie (ostatnie 100 zdarzeń)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500">Czas</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500">Akcja</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500">Obiekt</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($activityLog as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 font-mono text-xs text-slate-500">{{ $log->occurred_at ? $log->occurred_at->format('Y-m-d H:i:s') : '—' }}</td>
                        <td class="px-4 py-2">
                            @php
                                $c = match(true) {
                                    str_contains($log->action,'failed')||str_contains($log->action,'rejected') => 'bg-red-100 text-red-700',
                                    str_contains($log->action,'login') => 'bg-emerald-100 text-emerald-700',
                                    str_contains($log->action,'deleted') => 'bg-orange-100 text-orange-700',
                                    default => 'bg-blue-100 text-blue-700',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $c }}">{{ $log->action }}</span>
                        </td>
                        <td class="px-4 py-2 text-xs text-slate-500">
                            @if($log->subject_type)
                                {{ class_basename($log->subject_type) }}@if($log->subject_code) — {{ $log->subject_code }}@elseif($log->subject_id) #{{ $log->subject_id }}@endif
                            @else —
                            @endif
                        </td>
                        <td class="px-4 py-2 font-mono text-xs text-slate-400">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Brak wpisów</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
