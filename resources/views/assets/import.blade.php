@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Import aktywów z CSV</h1>
<div class="bg-white rounded shadow p-6 max-w-2xl">
    <p class="mb-3 text-sm text-slate-600">Format: nagłówki w 1 wierszu, kodowanie UTF-8.</p>
    <pre class="text-xs bg-slate-100 p-3 rounded mb-4 overflow-x-auto">code,name,type,environment,confidentiality,integrity,availability,data_classification,owner_email,business_unit_code,client_code,description
SRV-001,Production API,application,prod,3,3,4,Confidential,ciso@grc.local,SEC,,Aplikacja API klienta
DB-001,Customer DB,data_store,prod,4,4,4,Restricted,ciso@grc.local,SEC,,</pre>
    <form method="POST" action="{{ route('assets.import') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="file" name="file" accept=".csv" required class="block">
        @error('file')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        <button class="px-4 py-2 bg-emerald-600 text-white rounded">Importuj</button>
        <a href="{{ route('assets.index') }}" class="ml-2 text-slate-600 hover:underline">Anuluj</a>
    </form>
</div>
@endsection
