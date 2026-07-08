@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Import polityk i procedur z CSV</h1>
<div class="bg-white rounded shadow p-6 max-w-2xl">
    <p class="mb-3 text-sm text-slate-600">Format: nagłówki w 1 wierszu, kodowanie UTF-8. Istniejące polityki (dopasowane po <code class="bg-slate-100 px-1 rounded">code</code>) zostaną zaktualizowane, brakujące — utworzone.</p>
    <pre class="text-xs bg-slate-100 p-3 rounded mb-4 overflow-x-auto">code,title,category,current_version,status,owner_email,effective_from,next_review_due,description,drive_url
POL-2026-0001,Polityka bezpieczeństwa informacji,ISMS,2.0,Active,ciso@grc.local,2026-01-01,2027-01-01,,https://drive.google.com/drive/folders/xxx
POL-2026-0002,Procedura zarządzania incydentami,IR,1.1,Draft,ciso@grc.local,,,,</pre>
    <p class="mb-4 text-xs text-slate-500">Kolumna <code class="bg-slate-100 px-1 rounded">drive_url</code> jest opcjonalna — jeśli podana, dokument zostaje podpięty do polityki jako link do Google Drive (tryb ręcznych linków, działa bez konfiguracji API).</p>
    <form method="POST" action="{{ route('policies.import') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="file" name="file" accept=".csv" required class="block">
        @error('file')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        <button class="px-4 py-2 bg-emerald-600 text-white rounded">Importuj</button>
        <a href="{{ route('policies.index') }}" class="ml-2 text-slate-600 hover:underline">Anuluj</a>
    </form>
</div>
@endsection
