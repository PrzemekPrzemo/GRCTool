@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Konfiguracja MFA</h1>

@if($enabled)
    <div class="bg-white rounded shadow p-6 max-w-2xl">
        <p class="mb-4 text-emerald-700 font-medium">MFA jest włączone dla Twojego konta.</p>
        <a href="{{ route('mfa.recovery_codes') }}" class="text-emerald-600 hover:underline">Pokaż kody recovery</a>
        <form method="POST" action="{{ route('mfa.disable') }}" class="mt-6">
            @csrf
            @method('DELETE')
            <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                    onclick="return confirm('Wyłączyć MFA? To zmniejsza bezpieczeństwo konta.')">
                Wyłącz MFA
            </button>
        </form>
    </div>
@else
    <div class="bg-white rounded shadow p-6 max-w-2xl">
        <p class="mb-4 text-slate-600">Zeskanuj kod QR aplikacją typu Google Authenticator, Authy lub 1Password, a następnie wprowadź kod 6-cyfrowy poniżej.</p>
        <div class="flex flex-col md:flex-row gap-6 items-center">
            <img src="data:image/png;base64,{{ $qr }}" alt="QR" class="w-48 h-48 border border-slate-200">
            <div class="text-sm">
                <p class="font-medium mb-1">Sekret manualny:</p>
                <code class="block break-all bg-slate-100 p-2 rounded text-xs">{{ $secret }}</code>
            </div>
        </div>
        <form method="POST" action="{{ route('mfa.confirm') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Kod 6-cyfrowy</label>
                <input name="code" required class="w-full md:w-48 px-3 py-2 border border-slate-300 rounded tracking-widest text-lg">
                @error('code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <button class="px-4 py-2 bg-slate-900 text-white rounded hover:bg-slate-800">Włącz MFA</button>
        </form>
    </div>
@endif
@endsection
