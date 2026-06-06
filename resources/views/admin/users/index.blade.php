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
                <td class="px-3 py-2 text-xs font-mono">{{ $u->email }}</td>
                <td class="px-3 py-2"><a href="{{ route('admin.users.show', $u) }}" class="font-medium text-slate-800 hover:text-emerald-700">{{ $u->name }}</a></td>
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
