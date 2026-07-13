@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Import polityk z CSV</h1>
<div class="bg-white rounded shadow p-6 max-w-2xl">
    <p class="mb-3 text-sm text-slate-600">Zbiorczy import metadanych wielu polityk naraz. Format: nagłówki w 1 wierszu, kodowanie UTF-8. Istniejące polityki (dopasowane po <code class="bg-slate-100 px-1 rounded">code</code>) zostaną zaktualizowane, brakujące — utworzone.</p>
    <pre class="text-xs bg-slate-100 p-3 rounded mb-4 overflow-x-auto">code,title,category,current_version,status,owner_email,effective_from,next_review_due,description,drive_url
POL-2026-0001,Polityka bezpieczeństwa informacji,ISMS,2.0,Active,ciso@grc.local,2026-01-01,2027-01-01,,https://drive.google.com/drive/folders/xxx</pre>
    <p class="mb-4 text-xs text-slate-500">Kolumna <code class="bg-slate-100 px-1 rounded">drive_url</code> jest opcjonalna — jeśli podana, dokument zostaje podpięty do polityki jako link do Google Drive (tryb ręcznych linków, działa bez konfiguracji API).</p>
    <p class="mb-4 text-xs text-slate-500">Masz pojedyncze dokumenty w Wordzie zamiast arkusza CSV? Skorzystaj z importu przy <a href="{{ route('policies.create') }}" class="text-emerald-700 hover:underline">tworzeniu polityki</a> lub <a href="{{ route('procedures.create') }}" class="text-emerald-700 hover:underline">procedury</a> — wgrany plik .docx automatycznie wypełni treść i zostanie zapisany jako załącznik.</p>
    <form method="POST" action="{{ route('policies.import') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="file" name="file" accept=".csv" required class="block">
        @error('file')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        <button class="px-4 py-2 bg-emerald-600 text-white rounded">Importuj</button>
        <a href="{{ route('policies.index') }}" class="ml-2 text-slate-600 hover:underline">Anuluj</a>
    </form>
</div>
@endsection
