<?php

namespace App\Http\Controllers;

use App\Models\AccessReviewCampaign;
use App\Models\AccessReviewItem;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessReviewController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('access_review.view'), 403);

        $query = AccessReviewCampaign::with('owner');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $campaigns = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        // Stats for header row
        $activeCount  = AccessReviewCampaign::where('status', 'active')->count();
        $overdueCount = AccessReviewCampaign::where('status', 'active')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->count();
        $pendingItems = \App\Models\AccessReviewItem::where('status', 'pending')->count();

        return view('access-reviews.index', compact('campaigns', 'activeCount', 'overdueCount', 'pendingItems'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('access_review.create'), 403);

        $users = User::orderBy('name')->get();

        return view('access-reviews.form', ['campaign' => new AccessReviewCampaign, 'users' => $users]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('access_review.create'), 403);

        $data = $this->validateCampaign($request);
        $data['code'] = AccessReviewCampaign::nextCode();

        $campaign = AccessReviewCampaign::create($data);
        AuditLogger::log('access_review.created', $campaign);

        return redirect()->route('access-reviews.show', $campaign)
            ->with('status', "Kampania {$campaign->code} utworzona.");
    }

    public function show(AccessReviewCampaign $campaign): View
    {
        abort_unless(auth()->user()->can('access_review.view'), 403);

        $campaign->load('owner');
        $items = $campaign->items()->with('subjectUser', 'reviewer', 'reviewedBy')
            ->orderBy('system_name')
            ->get();
        $users = User::orderBy('name')->get();

        $tab          = request('tab', 'all');
        $filteredItems = match ($tab) {
            'pending'  => $items->where('status', 'pending'),
            'approved' => $items->where('status', 'approved'),
            'revoked'  => $items->where('status', 'revoked'),
            default    => $items,
        };

        return view('access-reviews.show', compact('campaign', 'items', 'filteredItems', 'users', 'tab'));
    }

    public function edit(AccessReviewCampaign $campaign): View
    {
        abort_unless(auth()->user()->can('access_review.update'), 403);

        $users = User::orderBy('name')->get();

        return view('access-reviews.form', compact('campaign', 'users'));
    }

    public function update(Request $request, AccessReviewCampaign $campaign): RedirectResponse
    {
        abort_unless(auth()->user()->can('access_review.update'), 403);

        $data = $this->validateCampaign($request);
        $campaign->update($data);
        AuditLogger::log('access_review.updated', $campaign);

        return redirect()->route('access-reviews.show', $campaign)->with('status', 'Zaktualizowano kampanię.');
    }

    public function launch(Request $request, AccessReviewCampaign $campaign): RedirectResponse
    {
        abort_unless(auth()->user()->can('access_review.update'), 403);

        if ($campaign->status !== 'draft') {
            return back()->with('error', 'Kampania nie jest w stanie szkicu.');
        }
        if (! $campaign->due_date) {
            return back()->with('error', 'Przed uruchomieniem ustaw datę końcową.');
        }

        $campaign->update(['status' => 'active']);
        AuditLogger::log('access_review.launched', $campaign);

        return back()->with('status', 'Kampania uruchomiona.');
    }

    public function complete(Request $request, AccessReviewCampaign $campaign): RedirectResponse
    {
        abort_unless(auth()->user()->can('access_review.update'), 403);

        if ($campaign->status !== 'active') {
            return back()->with('error', 'Kampania nie jest aktywna.');
        }

        $campaign->update(['status' => 'completed', 'completed_at' => now()]);
        AuditLogger::log('access_review.completed', $campaign);

        return back()->with('status', 'Kampania zakończona.');
    }

    public function addItem(Request $request, AccessReviewCampaign $campaign): RedirectResponse
    {
        abort_unless(auth()->user()->can('access_review.update'), 403);

        $data = $request->validate([
            'subject_user_id' => ['nullable', 'exists:users,id'],
            'subject_name'    => ['nullable', 'string', 'max:255'],
            'reviewer_id'     => ['nullable', 'exists:users,id'],
            'system_name'     => ['required', 'string', 'max:255'],
            'access_role'     => ['required', 'string', 'max:255'],
            'access_scope'    => ['nullable', 'string', 'max:255'],
            'last_used_at'    => ['nullable', 'date'],
            'justification'   => ['nullable', 'string'],
        ]);

        $data['campaign_id'] = $campaign->id;
        AccessReviewItem::create($data);
        $campaign->increment('total_items');
        AuditLogger::log('access_review.item_added', $campaign);

        return back()->with('status', 'Element dodany do kampanii.');
    }

    public function decide(Request $request, AccessReviewCampaign $campaign, AccessReviewItem $item): RedirectResponse
    {
        abort_unless(auth()->user()->can('access_review.update'), 403);
        abort_unless($item->campaign_id === $campaign->id, 404);

        $data = $request->validate([
            'status'        => ['required', 'in:approved,revoked,modified'],
            'decision_note' => ['nullable', 'string'],
        ]);

        $wasRevoked = $data['status'] === 'revoked';

        $item->update([
            'status'        => $data['status'],
            'decision_note' => $data['decision_note'] ?? null,
            'reviewed_at'   => now(),
            'reviewed_by'   => auth()->id(),
        ]);

        $campaign->increment('reviewed_items');
        if ($wasRevoked) {
            $campaign->increment('revoked_items');
        }

        AuditLogger::log('access_review.item_decided', $item);

        return back()->with('status', 'Decyzja zapisana.');
    }

    public function bulkApprove(Request $request, AccessReviewCampaign $campaign): RedirectResponse
    {
        abort_unless(auth()->user()->can('access_review.update'), 403);

        $userId = auth()->id();
        $pendingItems = $campaign->items()
            ->where('status', 'pending')
            ->where('reviewer_id', $userId)
            ->get();

        $count = 0;
        foreach ($pendingItems as $item) {
            $item->update([
                'status'      => 'approved',
                'reviewed_at' => now(),
                'reviewed_by' => $userId,
            ]);
            $campaign->increment('reviewed_items');
            $count++;
        }

        AuditLogger::log('access_review.bulk_approved', $campaign, ['count' => $count]);

        return back()->with('status', "Zatwierdzone {$count} elementów.");
    }

    private function validateCampaign(Request $request): array
    {
        return $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'scope'               => ['required', 'in:all_systems,department,system,role'],
            'scope_value'         => ['nullable', 'string', 'max:255'],
            'owner_id'            => ['nullable', 'exists:users,id'],
            'due_date'            => ['nullable', 'date'],
            'review_period_start' => ['nullable', 'date'],
            'review_period_end'   => ['nullable', 'date'],
            'notes'               => ['nullable', 'string'],
        ]);
    }
}
