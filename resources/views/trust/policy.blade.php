<!DOCTYPE html>
<html lang="pl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $policy->title }} · Trust Center</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50">
<main class="max-w-3xl mx-auto p-6">
    <a href="{{ route('trust.public') }}" class="text-emerald-700 text-sm hover:underline">← Trust Center</a>
    <div class="text-xs font-mono text-slate-500 mt-4">{{ $policy->code }} · v{{ $policy->current_version }} · effective {{ $policy->effective_from?->format('Y-m-d') }}</div>
    <h1 class="text-3xl font-bold mt-1">{{ $policy->title }}</h1>
    @if($policy->public_summary)<p class="text-slate-700 mt-3">{{ $policy->public_summary }}</p>@endif
    <div class="mt-6 bg-white rounded shadow p-6 prose">
        <p class="text-slate-500">{{ $policy->description ?? 'Pełna treść polityki dostępna w panelu klienta po podpisaniu NDA.' }}</p>
    </div>
</main>
</body>
</html>
