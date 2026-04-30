<!DOCTYPE html>
<html lang="pl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trust Center · {{ config('app.name') }}</title>
    <meta name="description" content="Security & Compliance program — {{ config('app.name') }} Trust Center.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-b from-slate-900 to-slate-800 text-slate-100">

<header class="py-12 text-center">
    <div class="max-w-4xl mx-auto px-4">
        <span class="inline-block w-3 h-3 rounded-full bg-emerald-400 mr-2 align-middle"></span>
        <span class="text-emerald-400 text-sm font-mono uppercase tracking-wider">Security · Compliance · Trust</span>
        <h1 class="text-4xl md:text-5xl font-bold mt-3">Trust Center</h1>
        <p class="text-slate-300 mt-3 max-w-2xl mx-auto">Otwarta strona statusu naszego programu bezpieczeństwa. Aktywne certyfikaty, polityki, lista subprocesorów i wybrane wskaźniki w czasie rzeczywistym.</p>
    </div>
</header>

<main class="max-w-5xl mx-auto px-4 pb-16 space-y-10">

    {{-- Certyfikaty --}}
    <section class="bg-white text-slate-900 rounded-lg shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">Aktywne certyfikaty</h2>
        @if($certifications->isEmpty())
            <p class="text-slate-500 text-sm">Brak publicznie wystawionych certyfikatów. W trakcie procesu certyfikacji ISO 27001:2022.</p>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($certifications as $cert)
            <div class="border border-slate-200 rounded p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xs font-mono text-slate-500">{{ $cert->framework }}</div>
                        <h3 class="font-semibold">{{ $cert->name }}</h3>
                    </div>
                    <span class="text-emerald-600 text-2xl">✓</span>
                </div>
                <dl class="mt-3 text-sm grid grid-cols-2 gap-y-1">
                    <dt class="text-slate-500">Wystawca</dt><dd>{{ $cert->issuer }}</dd>
                    <dt class="text-slate-500">Numer</dt><dd class="font-mono text-xs">{{ $cert->certificate_number ?? '—' }}</dd>
                    <dt class="text-slate-500">Wystawiony</dt><dd>{{ $cert->issued_at?->format('Y-m-d') }}</dd>
                    <dt class="text-slate-500">Ważny do</dt><dd>{{ $cert->valid_until?->format('Y-m-d') }}</dd>
                </dl>
                @if($cert->scope)<p class="text-xs text-slate-600 mt-2">{{ $cert->scope }}</p>@endif
            </div>
            @endforeach
        </div>
        @endif
    </section>

    {{-- KCI publiczne --}}
    @if($publicIndicators->isNotEmpty())
    <section class="bg-white text-slate-900 rounded-lg shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">Wskaźniki bezpieczeństwa</h2>
        <p class="text-slate-500 text-sm mb-4">Wybrane KCI z naszego programu, aktualizowane automatycznie.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($publicIndicators as $i)
            @php $latest = $i->latestMeasurement; $bg = ['green'=>'bg-emerald-500','amber'=>'bg-amber-500','red'=>'bg-red-500'][$latest?->status] ?? 'bg-slate-300'; @endphp
            <div class="bg-slate-50 rounded p-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-slate-500 font-mono">{{ $i->code }}</span>
                    <span class="inline-block w-3 h-3 rounded-full {{ $bg }}"></span>
                </div>
                <div class="font-medium text-sm">{{ $i->public_label ?: $i->name }}</div>
                <div class="text-2xl font-bold mt-1">{{ $latest ? rtrim(rtrim(number_format((float)$latest->value, 2), '0'), '.').' '.$i->unit : '—' }}</div>
                @if($latest)<div class="text-xs text-slate-500">stan na {{ $latest->measured_at->format('Y-m-d') }}</div>@endif
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Polityki --}}
    <section class="bg-white text-slate-900 rounded-lg shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">Polityki bezpieczeństwa</h2>
        @if($policies->isEmpty())
            <p class="text-slate-500 text-sm">Pełna lista polityk dostępna po podpisaniu NDA.</p>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($policies as $p)
            <div class="border border-slate-200 rounded p-3">
                <div class="text-xs font-mono text-slate-500">{{ $p->code }} · v{{ $p->current_version }}</div>
                <h3 class="font-semibold">{{ $p->title }}</h3>
                @if($p->public_summary)<p class="text-sm text-slate-700 mt-1">{{ $p->public_summary }}</p>@endif
                @if($p->public_download)
                    <a href="{{ route('trust.policy', $p) }}" class="text-emerald-700 text-sm hover:underline">Pełna treść →</a>
                @else
                    <p class="text-xs text-slate-500 mt-1">Pełna treść dostępna po NDA</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </section>

    {{-- Subprocesorzy --}}
    @if($subprocessors->isNotEmpty())
    <section class="bg-white text-slate-900 rounded-lg shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">Subprocessorzy</h2>
        <p class="text-slate-500 text-sm mb-4">Lista podmiotów przetwarzających, z którymi współpracujemy. Notyfikacja zmian zgodnie z DPA min. 30 dni przed.</p>
        <table class="w-full text-sm">
            <thead class="text-left text-xs uppercase text-slate-500">
                <tr><th class="py-2">Nazwa</th><th>Usługa</th><th>Kraj</th><th>Tier</th><th>Certyfikaty</th></tr>
            </thead>
            <tbody>
            @foreach($subprocessors as $s)
            <tr class="border-t border-slate-100">
                <td class="py-2 font-medium">{{ $s->name }}</td>
                <td class="py-2">{{ $s->service_provided }}</td>
                <td class="py-2 text-xs">{{ $s->country_of_processing }}</td>
                <td class="py-2 text-xs">{{ $s->tier }}</td>
                <td class="py-2 text-xs font-mono">{{ implode(', ', $s->certifications ?? []) }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </section>
    @endif

    {{-- Kontakt + NDA portal CTA --}}
    <section class="bg-emerald-900/30 border border-emerald-700 rounded-lg p-6 text-center">
        <h2 class="text-xl font-semibold text-white">Potrzebujesz pełnej dokumentacji?</h2>
        <p class="text-slate-300 text-sm mt-2 max-w-xl mx-auto">Audyt packs, raporty pentest, pełna lista subprocessorów i polityk dostępne po podpisaniu NDA.</p>
        <a href="mailto:security@example.com" class="inline-block mt-4 px-6 py-2 bg-emerald-500 hover:bg-emerald-400 text-white rounded font-medium">Zażądaj dostępu NDA</a>
    </section>

</main>

<footer class="text-center text-xs text-slate-400 py-6 border-t border-slate-700">
    {{ config('app.name') }} · powered by GRC Platform · stan na {{ now()->format('Y-m-d H:i') }}
</footer>
</body>
</html>
