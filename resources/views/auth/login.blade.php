@extends('layouts.auth')
@section('content')
<h2 class="text-xl font-semibold mb-6">Logowanie</h2>
@if(session('warning'))
    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-900 rounded text-sm">{{ session('warning') }}</div>
@endif
<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input name="email" type="email" autofocus required value="{{ old('email') }}"
               class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400">
        @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Hasło</label>
        <input name="password" type="password" required
               class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400">
        @error('password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="remember" value="1" class="rounded">
        Zapamiętaj urządzenie (zaufane)
    </label>
    <button class="w-full bg-slate-900 text-white py-2 rounded font-medium hover:bg-slate-800">Zaloguj</button>
</form>
@endsection
