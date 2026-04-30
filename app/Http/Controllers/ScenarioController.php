<?php

namespace App\Http\Controllers;

use App\Models\ScenarioTemplate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScenarioController extends Controller
{
    public function index(Request $request): View
    {
        $query = ScenarioTemplate::query()->where('is_active', true);
        if ($cat = $request->string('category')->toString()) {
            $query->where('category_l2', $cat);
        }
        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%$search%")->orWhere('description', 'like', "%$search%");
            });
        }
        $scenarios = $query->orderBy('category_l2')->orderBy('name')->get();
        $byCategory = $scenarios->groupBy('category_l2');

        return view('scenarios.index', compact('scenarios', 'byCategory'));
    }

    public function show(ScenarioTemplate $scenario): View
    {
        return view('scenarios.show', compact('scenario'));
    }
}
