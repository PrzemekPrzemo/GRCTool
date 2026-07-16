<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->check(), 403);

        $data = $request->validate([
            'commentable_type' => ['required', 'string', 'in:App\\Models\\Risk,App\\Models\\Incident,App\\Models\\CorrectiveActionPlan,App\\Models\\Finding'],
            'commentable_id' => ['required', 'integer'],
            'body' => ['required', 'string', 'max:4000'],
            'is_internal' => ['boolean'],
        ]);

        $data['user_id'] = auth()->id();
        $data['is_internal'] = $request->boolean('is_internal');

        Comment::create($data);

        return back()->with('status', 'Komentarz dodany.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        abort_unless(
            auth()->id() === $comment->user_id || auth()->user()->hasRole(['admin', 'ciso']),
            403
        );

        $comment->delete();

        return back()->with('status', 'Komentarz usunięty.');
    }
}
