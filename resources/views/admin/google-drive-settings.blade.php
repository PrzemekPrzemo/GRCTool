@extends('layouts.app')
@section('title', 'Konfiguracja Google Drive')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Konfiguracja Google Drive</h1>
        <p class="text-slate-500 mt-1 text-sm">Podepnij polityki i procedury do plików na współdzielonym dysku Google. Tryb linków działa zawsze bez konfiguracji poniżej — ta strona włącza dodatkowo automatyczną synchronizację przez Drive API.</p>
    </div>

    @if(session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded text-sm">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-900 rounded text-sm">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-900 rounded text-sm">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    {{-- Current status --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-700">Synchronizacja przez Google Drive API</span>
            @if($settings['google_drive_enabled'] === '1')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Włączone
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    Wyłączone (działa tylko tryb ręcznych linków)
                </span>
            @endif
        </div>
        <div class="mt-3 grid grid-cols-2 gap-3 text-sm text-slate-600">
            <div>
                <span class="font-medium">Service account:</span>
                <span class="ml-1 font-mono text-xs">{{ $settings['google_drive_client_email'] ?: '—' }}</span>
            </div>
            <div>
                <span class="font-medium">Folder na dysku:</span>
                <span class="ml-1 font-mono text-xs">{{ $settings['google_drive_folder_id'] ?: '—' }}</span>
            </div>
            <div class="col-span-2">
                <span class="font-medium">Klucz JSON:</span>
                <span class="ml-1">{{ $settings['credentials_saved'] ? '●●●●●● (zapisany)' : '— (nie ustawiony)' }}</span>
            </div>
        </div>
        @if($settings['credentials_saved'])
        <form method="POST" action="{{ route('admin.google-drive.test') }}" class="mt-4">
            @csrf
            <button class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm hover:bg-slate-50">Testuj połączenie</button>
        </form>
        @endif
    </div>

    {{-- Configuration form --}}
    <form method="POST" action="{{ route('admin.google-drive.update') }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">ID folderu na Google Drive (opcjonalnie)</label>
            <input type="text" name="google_drive_folder_id"
                   value="{{ old('google_drive_folder_id', $settings['google_drive_folder_id']) }}"
                   placeholder="np. 1AbCdEfGhIjKlMnOpQrStUvWxYz"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400">
            <p class="text-xs text-slate-400 mt-1">Fragment adresu URL folderu współdzielonego dysku, gdzie trzymacie polityki/procedury (informacyjnie, do podpowiedzi w formularzu podpinania dokumentu).</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Klucz JSON service accounta
                @if($settings['credentials_saved'])
                    <span class="text-xs font-normal text-slate-400 ml-1">(zostaw puste, by zachować aktualny)</span>
                @endif
            </label>
            <textarea name="google_drive_credentials" rows="5" autocomplete="off"
                      placeholder='{{ $settings['credentials_saved'] ? '●●●●●●●●●● (nie zmieniaj)' : '{"type": "service_account", "client_email": "...", ...}' }}'
                      class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400"></textarea>
            @error('google_drive_credentials')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            <p class="text-xs text-slate-400 mt-1">Wklej całą zawartość pliku JSON klucza service accounta z Google Cloud Console. Jest szyfrowana przed zapisem w bazie.</p>
        </div>

        <div class="pt-2 border-t border-slate-100">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="google_drive_enabled" value="1"
                       {{ $settings['google_drive_enabled'] === '1' ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-400">
                <div>
                    <p class="text-sm font-medium text-slate-700">Włącz komunikację z Google Drive API</p>
                    <p class="text-xs text-slate-400">Gdy wyłączone, polityki nadal można podpinać przez ręczne linki do Drive — nie jest wymagana żadna konfiguracja powyżej.</p>
                </div>
            </label>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('policies.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Anuluj</a>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition-colors">
                Zapisz konfigurację
            </button>
        </div>
    </form>

    {{-- Alert Center sync --}}
    <div class="mt-6 bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="text-sm font-semibold text-slate-900 mb-1">Synchronizacja Google Workspace Alert Center</h3>
        <p class="text-xs text-slate-500 mb-4">Ciągnie alerty bezpieczeństwa poczty i Drive (phishing, malware, podejrzane logowania, DLP) do rejestru incydentów. Korzysta z tego samego service accounta co wyżej, ale wymaga domain-wide delegation ze scope'em <code class="bg-slate-100 px-1 rounded">apps.alerts.readonly</code> i adresu administratora Workspace do impersonacji.</p>

        <form method="PUT" action="{{ route('admin.google-drive.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="google_drive_folder_id" value="{{ $settings['google_drive_folder_id'] }}">
            <input type="hidden" name="google_drive_enabled" value="{{ $settings['google_drive_enabled'] }}">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">E-mail administratora do impersonacji</label>
                <input type="email" name="google_workspace_admin_email"
                       value="{{ old('google_workspace_admin_email', $settings['google_workspace_admin_email']) }}"
                       placeholder="admin@yourdomain.com"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400">
                @error('google_workspace_admin_email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="google_workspace_alerts_enabled" value="1"
                       {{ $settings['google_workspace_alerts_enabled'] === '1' ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-400">
                <div>
                    <p class="text-sm font-medium text-slate-700">Włącz synchronizację Alert Center</p>
                    <p class="text-xs text-slate-400">Godzinowa komenda tworzy/aktualizuje incydenty na podstawie alertów Workspace.</p>
                </div>
            </label>

            <div class="flex items-center justify-end pt-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800">Zapisz</button>
            </div>
        </form>

        @if($settings['google_workspace_alerts_enabled'] === '1')
        <form method="POST" action="{{ route('admin.google-drive.test-alert-center') }}" class="mt-3">
            @csrf
            <button class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm hover:bg-slate-50">Testuj połączenie</button>
        </form>
        @endif
    </div>

    {{-- Setup instructions --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-blue-900 mb-3">Jak założyć service account dla Google Drive API</h3>
        <ol class="text-xs text-blue-800 space-y-1.5 list-decimal list-inside">
            <li>Przejdź do <strong>console.cloud.google.com</strong> → wybierz/utwórz projekt organizacji</li>
            <li>APIs &amp; Services → Library → włącz <strong>Google Drive API</strong></li>
            <li>APIs &amp; Services → Credentials → Create credentials → <strong>Service account</strong></li>
            <li>Po utworzeniu: Keys → Add key → Create new key → typ <strong>JSON</strong> → pobierz plik</li>
            <li>Skopiuj adres e-mail service accounta (pole <code class="bg-blue-100 px-1 rounded">client_email</code> w pobranym pliku)</li>
            <li>Na Google Drive: udostępnij folder z politykami temu adresowi e-mail (wystarczy uprawnienie "Czytelnik")</li>
            <li>Wklej całą zawartość pobranego pliku JSON w pole powyżej i zapisz</li>
            <li>Dla synchronizacji Alert Center: w Google Admin Console → Security → API controls → Domain-wide delegation → dodaj Client ID service accounta ze scope'em <code class="bg-blue-100 px-1 rounded">https://www.googleapis.com/auth/apps.alerts.readonly</code></li>
        </ol>
    </div>

</div>
@endsection
