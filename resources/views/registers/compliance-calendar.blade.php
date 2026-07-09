@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Kalendarz compliance</h1>
        <p class="text-sm text-slate-500 mt-0.5">Cykliczne zadania compliance, audytowe i risk (REG-015)</p>
    </div>
</div>

@foreach($tasks as $frequency => $items)
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-600 uppercase">{{ $frequency }}</div>
    <table class="w-full text-sm">
        <tbody class="divide-y divide-slate-100">
            @foreach($items as $task)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-2 font-mono text-xs text-slate-500 w-16">{{ $task->ref }}</td>
                <td class="px-4 py-2 text-slate-800">{{ $task->task }}</td>
                <td class="px-4 py-2 text-xs text-slate-500 w-40">{{ $task->months }}</td>
                <td class="px-4 py-2 text-xs text-slate-600 w-32">{{ $task->owner_role }}</td>
                <td class="px-4 py-2 text-xs text-blue-700 w-40">{{ $task->related_document }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach
@endsection
