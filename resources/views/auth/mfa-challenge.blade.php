@extends('layouts.auth')
@section('content')
<h2 class="text-xl font-semibold mb-2">Weryfikacja MFA</h2>
<p class="text-slate-600 text-sm mb-6">Wpisz 6-cyfrowy kod z aplikacji uwierzytelniającej (Google Authenticator, Authy, 1Password) lub kod recovery.</p>
<form method="POST" action="{{ route('mfa.verify') }}" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium mb-1">Kod</label>
        <input name="code" autocomplete="one-time-code" autofocus required
               class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 tracking-widest text-lg">
        @error('code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <button class="w-full bg-slate-900 text-white py-2 rounded font-medium hover:bg-slate-800">Zweryfikuj</button>
</form>
@endsection
