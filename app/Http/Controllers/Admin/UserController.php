<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessUnit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles', 'businessUnit')->orderBy('name')->paginate(50);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', $this->formData(new User));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'is_external' => ['nullable', 'boolean'],
            'external_org' => ['nullable', 'string'],
            'roles' => ['array'],
        ]);

        $tempPassword = Str::password(16);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($tempPassword),
            'business_unit_id' => $data['business_unit_id'] ?? null,
            'is_external' => $data['is_external'] ?? false,
            'external_org' => $data['external_org'] ?? null,
            'is_active' => true,
            'locale' => 'pl',
            'email_verified_at' => now(),
        ]);
        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('admin.users.index')
            ->with('status', "Utworzono usera {$user->email}. Hasło tymczasowe: {$tempPassword} — przekaż bezpiecznym kanałem i wymuś zmianę.");
    }

    public function show(User $user): View
    {
        $sessions = $user->sessions()
            ->orderByDesc('logged_in_at')
            ->limit(50)
            ->get();

        $activityLog = \App\Models\AuditLog::where('user_id', $user->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return view('admin.users.show', compact('user', 'sessions', 'activityLog'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', $this->formData($user));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'is_external' => ['nullable', 'boolean'],
            'external_org' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['array'],
        ]);

        $user->update([
            'name' => $data['name'],
            'business_unit_id' => $data['business_unit_id'] ?? null,
            'is_external' => $data['is_external'] ?? false,
            'external_org' => $data['external_org'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('status', "Zaktualizowano {$user->email}.");
    }

    public function deactivate(User $user): RedirectResponse
    {
        $user->update(['is_active' => false, 'disabled_at' => now()]);

        return back()->with('status', "Konto {$user->email} dezaktywowane.");
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $tempPassword = Str::password(16);
        $user->update([
            'password' => Hash::make($tempPassword),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return back()->with('status', "Hasło zresetowane: {$tempPassword} — wymuś zmianę przy pierwszym logowaniu.");
    }

    private function formData(User $user): array
    {
        return [
            'user' => $user,
            'businessUnits' => BusinessUnit::orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get(),
            'currentRoles' => $user->roles->pluck('name')->all(),
        ];
    }
}
