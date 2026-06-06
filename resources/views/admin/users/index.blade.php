@extends('layouts.app')
@section('content')
<div class="flex justify-between mb-4">
    <h1 class="text-2xl font-semibold">Użytkownicy</h1>
    <a href="{{ route('admin.users.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowy user</a>
</div>
<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr><th class="px-3 py-2">Email</th><th class="px-3 py-2">Imię</th><th class="px-3 py-2">Role</th><th class="px-3 py-2">BU</th><th class="px-3 py-2">External</th><th class="px-3 py-2">MFA</th><th class="px-3 py-2">Aktywny</th><th class="px-3 py-2">Last login</th><th></th></tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            <tr class="border-t border-slate-100">
                <td class="px-3 py-2 text-xs font-mono">
                    <div class="flex items-center gap-1.5">
                        @if($u->avatar_url) <img src="{{ $u->avatar_url }}" class="w-7 h-7 rounded-full inline-block flex-shrink-0" alt=""> @endif
                        {{ $u->email }}
                    </div>
                </td>
                <td class="px-3 py-2">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <a href="{{ route('admin.users.show', $u) }}" class="font-medium text-slate-800 hover:text-emerald-700">{{ $u->name }}</a>
                        @if($u->auth_provider === 'google')
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                <svg class="w-3 h-3" viewBox="0 0 24 24"><path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/></svg>
                                Google
                            </span>
                        @else
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">Lokalne</span>
                        @endif
                    </div>
                </td>
                <td class="px-3 py-2 text-xs">@foreach($u->roles as $r)<span class="px-1.5 py-0.5 bg-slate-100 rounded mr-1">{{ $r->name }}</span>@endforeach</td>
                <td class="px-3 py-2 text-xs">{{ $u->businessUnit?->code ?? '—' }}</td>
                <td class="px-3 py-2 text-xs">{{ $u->is_external ? $u->external_org : '—' }}</td>
                <td class="px-3 py-2 text-xs">{{ $u->hasMfaEnabled() ? '✓' : '⚠' }}</td>
                <td class="px-3 py-2 text-xs">{{ $u->is_active ? 'Tak' : 'Nie' }}</td>
                <td class="px-3 py-2 text-xs">{{ $u->last_login_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td class="px-3 py-2 text-xs">
                    <a href="{{ route('admin.users.show', $u) }}" class="text-blue-700 hover:underline">Aktywność</a>
                    <a href="{{ route('admin.users.edit', $u) }}" class="text-emerald-700 hover:underline ml-2">edit</a>
                    <form method="POST" action="{{ route('admin.users.reset', $u) }}" class="inline" onsubmit="return confirm('Reset hasła i wyłączyć MFA?')">@csrf<button class="text-amber-700 hover:underline ml-2">reset pwd</button></form>
                    @if($u->is_active)
                        <form method="POST" action="{{ route('admin.users.deactivate', $u) }}" class="inline" onsubmit="return confirm('Dezaktywować?')">@csrf<button class="text-red-700 hover:underline ml-2">deactivate</button></form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
