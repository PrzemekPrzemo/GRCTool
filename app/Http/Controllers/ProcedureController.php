<?php

namespace App\Http\Controllers;

use App\Models\Procedure;
use Illuminate\View\View;

class ProcedureController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('policy.view'), 403);

        $procedures = Procedure::withCount('steps')->orderBy('code')->get();

        return view('procedure.index', compact('procedures'));
    }

    public function show(Procedure $procedure): View
    {
        abort_unless(auth()->user()->can('policy.view'), 403);

        $procedure->load('steps');

        return view('procedure.show', compact('procedure'));
    }
}
