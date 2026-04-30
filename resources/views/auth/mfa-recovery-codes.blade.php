@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Kody recovery</h1>
<div class="bg-white rounded shadow p-6 max-w-xl">
    <p class="text-amber-700 mb-4">Zapisz te kody w bezpiecznym miejscu (password manager). Każdy działa jednorazowo.</p>
    <ul class="grid grid-cols-2 gap-2 font-mono text-sm">
        @foreach($codes as $code)
            <li class="bg-slate-100 px-3 py-2 rounded">{{ $code }}</li>
        @endforeach
    </ul>
    <a href="{{ route('mfa.setup') }}" class="inline-block mt-6 text-emerald-600 hover:underline">← Wróć</a>
</div>
@endsection
