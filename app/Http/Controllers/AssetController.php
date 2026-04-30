<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\BusinessUnit;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $query = Asset::query()->with(['owner', 'businessUnit', 'client']);

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }
        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }
        if ($criticality = $request->string('criticality')->toString()) {
            $query->where('criticality', $criticality);
        }

        $assets = $query->orderByDesc('id')->paginate(25)->withQueryString();

        return view('assets.index', compact('assets'));
    }

    public function create(): View
    {
        $this->authorize('create', Asset::class);

        return view('assets.form', $this->formData(new Asset));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Asset::class);
        $data = $this->validateAsset($request);
        $asset = Asset::create($data);

        return redirect()->route('assets.show', $asset)->with('status', "Aktywo {$asset->code} utworzone.");
    }

    public function show(Asset $asset): View
    {
        $asset->load(['owner', 'custodian', 'businessUnit', 'client', 'project', 'dependencies', 'dependents', 'vulnerabilities']);

        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset): View
    {
        $this->authorize('update', $asset);

        return view('assets.form', $this->formData($asset));
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $this->authorize('update', $asset);
        $data = $this->validateAsset($request);
        $asset->update($data);

        return redirect()->route('assets.show', $asset)->with('status', 'Aktywo zaktualizowane.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $this->authorize('delete', $asset);
        $asset->delete();

        return redirect()->route('assets.index')->with('status', 'Aktywo zarchiwizowane (soft delete).');
    }

    public function showImport(): View
    {
        $this->authorize('create', Asset::class);

        return view('assets.import');
    }

    /**
     * Format CSV: code,name,type,environment,confidentiality,integrity,availability,
     * data_classification,owner_email,business_unit_code,client_code,description
     */
    public function import(Request $request): RedirectResponse
    {
        $this->authorize('create', Asset::class);
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:10240']]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $headers = array_map('strtolower', fgetcsv($handle));

        $count = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $r = array_combine($headers, $row);
            try {
                Asset::updateOrCreate(
                    ['code' => $r['code'] ?? null],
                    [
                        'name' => $r['name'] ?? '',
                        'type' => $r['type'] ?? 'application',
                        'environment' => $r['environment'] ?? 'prod',
                        'confidentiality_impact' => max(1, min(4, (int) ($r['confidentiality'] ?? 2))),
                        'integrity_impact' => max(1, min(4, (int) ($r['integrity'] ?? 2))),
                        'availability_impact' => max(1, min(4, (int) ($r['availability'] ?? 2))),
                        'data_classification' => $r['data_classification'] ?? 'Internal',
                        'description' => $r['description'] ?? null,
                        'owner_id' => isset($r['owner_email']) ? optional(User::where('email', $r['owner_email'])->first())->id : null,
                        'business_unit_id' => isset($r['business_unit_code']) ? optional(BusinessUnit::where('code', $r['business_unit_code'])->first())->id : null,
                        'client_id' => isset($r['client_code']) ? optional(Client::where('code', $r['client_code'])->first())->id : null,
                    ],
                );
                $count++;
            } catch (\Throwable $e) {
                $errors[] = ($r['code'] ?? 'row?').' — '.$e->getMessage();
            }
        }
        fclose($handle);

        $msg = "Zaimportowano $count aktywów.";
        if ($errors) {
            $msg .= ' Błędy: '.implode('; ', array_slice($errors, 0, 5));
        }

        return redirect()->route('assets.index')->with('status', $msg);
    }

    private function validateAsset(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'max:32'],
            'environment' => ['required', 'string', 'max:16'],
            'confidentiality_impact' => ['required', 'integer', 'min:1', 'max:4'],
            'integrity_impact' => ['required', 'integer', 'min:1', 'max:4'],
            'availability_impact' => ['required', 'integer', 'min:1', 'max:4'],
            'data_classification' => ['required', 'string', 'max:32'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'custodian_id' => ['nullable', 'exists:users,id'],
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'lifecycle_status' => ['required', 'string', 'max:32'],
        ]);
    }

    private function formData(Asset $asset): array
    {
        return [
            'asset' => $asset,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'businessUnits' => BusinessUnit::orderBy('name')->get(['id', 'code', 'name']),
            'clients' => Client::orderBy('name')->get(['id', 'code', 'name']),
            'types' => ['server', 'application', 'repository', 'saas', 'data_store', 'person', 'vendor', 'network_device', 'ai_model'],
            'classifications' => ['Public', 'Internal', 'Confidential', 'Restricted'],
            'environments' => ['prod', 'staging', 'dev', 'test'],
            'lifecycles' => ['active', 'planned', 'deprecated', 'retired'],
        ];
    }
}
