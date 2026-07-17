<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $users = User::with('roles', 'businessUnit')->orderBy('name')->paginate(50);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        return view('admin.users.form', $this->formData(new User));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'is_external' => ['nullable', 'boolean'],
            'external_org' => ['nullable', 'string'],
            'auth_provider' => ['nullable', 'in:local,google,microsoft'],
            'roles' => ['array'],
        ]);

        $authProvider = $data['auth_provider'] ?? 'local';
        $tempPassword = Str::password(64);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($tempPassword),
            'business_unit_id' => $data['business_unit_id'] ?? null,
            'is_external' => $data['is_external'] ?? false,
            'external_org' => $data['external_org'] ?? null,
        ]);
        $user->forceFill([
            'auth_provider' => $authProvider,
            'is_active' => true,
            'locale' => 'pl',
            'email_verified_at' => now(),
        ])->save();
        $user->syncRoles($data['roles'] ?? []);

        if ($authProvider === 'google') {
            return redirect()->route('admin.users.index')
                ->with('status', "Utworzono użytkownika {$user->email}. Użytkownik będzie logował się przez Google Workspace.");
        }

        if ($authProvider === 'microsoft') {
            return redirect()->route('admin.users.index')
                ->with('status', "Utworzono użytkownika {$user->email}. Użytkownik będzie logował się przez Microsoft Entra ID — konto zostanie powiązane przy pierwszym logowaniu.");
        }

        $shortPassword = Str::password(16);
        $user->forceFill(['password' => Hash::make($shortPassword)])->save();

        return redirect()->route('admin.users.index')
            ->with('status', "Utworzono usera {$user->email}. Hasło tymczasowe: {$shortPassword} — przekaż bezpiecznym kanałem i wymuś zmianę.");
    }

    public function show(User $user): View
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $sessions = $user->sessions()
            ->orderByDesc('logged_in_at')
            ->limit(50)
            ->get();

        $activityLog = AuditLog::with('user')
            ->where('user_id', $user->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return view('admin.users.show', compact('user', 'sessions', 'activityLog'));
    }

    public function edit(User $user): View
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        return view('admin.users.form', $this->formData($user));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'is_external' => ['nullable', 'boolean'],
            'external_org' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'auth_provider' => ['nullable', 'in:local,google,microsoft'],
            'roles' => ['array'],
        ]);

        $user->update([
            'name' => $data['name'],
            'business_unit_id' => $data['business_unit_id'] ?? null,
            'is_external' => $data['is_external'] ?? false,
            'external_org' => $data['external_org'] ?? null,
        ]);
        $user->forceFill([
            'is_active' => $data['is_active'] ?? true,
            'auth_provider' => $data['auth_provider'] ?? $user->auth_provider,
        ])->save();
        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('status', "Zaktualizowano {$user->email}.");
    }

    public function deactivate(User $user): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $user->forceFill(['is_active' => false, 'disabled_at' => now()])->save();

        return back()->with('status', "Konto {$user->email} dezaktywowane.");
    }

    public function resetPassword(User $user): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $tempPassword = Str::password(16);
        $user->forceFill([
            'password' => Hash::make($tempPassword),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

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
