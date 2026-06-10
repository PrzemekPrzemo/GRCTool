@props(['model'])

@php
    $comments = $model->comments()->with('user')->get();
    $modelType = get_class($model);
@endphp

<div class="bg-white rounded-lg shadow mt-6">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-semibold text-slate-800 text-sm">Komentarze ({{ $comments->count() }})</h3>
    </div>

    @if($comments->isEmpty())
        <p class="px-4 py-6 text-sm text-slate-400 text-center">Brak komentarzy.</p>
    @else
        <ul class="divide-y divide-slate-100">
            @foreach($comments as $c)
            <li class="px-4 py-3 {{ $c->is_internal ? 'bg-amber-50' : '' }}">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-7 h-7 rounded-full bg-emerald-600 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($c->user?->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 text-xs text-slate-500 mb-0.5">
                            <span class="font-medium text-slate-700">{{ $c->user?->name ?? '—' }}</span>
                            <span>{{ $c->created_at->diffForHumans() }}</span>
                            @if($c->is_internal)
                                <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-xs">wewnętrzny</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $c->body }}</p>
                    </div>
                    @if(auth()->id() === $c->user_id || auth()->user()->hasRole(['admin', 'ciso']))
                    <form method="POST" action="{{ route('comments.destroy', $c) }}" class="flex-shrink-0"
                          onsubmit="return confirm('Usunąć komentarz?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-slate-300 hover:text-red-500 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    @endif

    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">
        <form method="POST" action="{{ route('comments.store') }}">
            @csrf
            <input type="hidden" name="commentable_type" value="{{ $modelType }}">
            <input type="hidden" name="commentable_id" value="{{ $model->id }}">
            <textarea name="body" rows="2" required maxlength="4000"
                placeholder="Dodaj komentarz..."
                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg resize-none focus:outline-none focus:ring-1 focus:ring-emerald-500"></textarea>
            <div class="flex items-center justify-between mt-2">
                <label class="flex items-center gap-1.5 text-xs text-slate-500 cursor-pointer">
                    <input type="checkbox" name="is_internal" value="1" class="rounded border-slate-300">
                    Wewnętrzny (tylko staff)
                </label>
                <button type="submit"
                    class="px-3 py-1.5 bg-emerald-600 text-white rounded text-xs font-medium hover:bg-emerald-700 transition-colors">
                    Dodaj
                </button>
            </div>
        </form>
    </div>
</div>
