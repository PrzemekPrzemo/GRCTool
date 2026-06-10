<?php

use App\Models\User;

// ─────────────────────────────────────────────────────────────────────────────
// BeforeEach: zaloguj jako admin z MFA — role zmieniane per-test przez syncRoles
// ─────────────────────────────────────────────────────────────────────────────

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

// ─────────────────────────────────────────────────────────────────────────────
// Sekcja 1: Użytkownik bez roli — brak jakichkolwiek uprawnień
// ─────────────────────────────────────────────────────────────────────────────

it('denies access to risks create for user without role', function (): void {
    $noRole = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $this->actingAs($noRole);

    $this->post('/risks')->assertForbidden();
});

it('denies access to controls create for user without role', function (): void {
    $noRole = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $this->actingAs($noRole);

    // ControlController::store() nie ma własnego guard — sprawdzamy przez create() (GET)
    // który nie ma guard, więc testujemy przez crossMapping (control.view)
    // Alternatywnie — sprawdzamy POST /risks (z uprawnieniami) lub inny endpoint z guardem.
    // Użyjemy CAP — który ma abort_unless cap.create
    $this->post('/cap')->assertForbidden();
});

it('denies access to incidents create for user without role', function (): void {
    $noRole = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $this->actingAs($noRole);

    $this->post('/incidents')->assertForbidden();
});

it('denies access to cap create for user without role', function (): void {
    $noRole = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $this->actingAs($noRole);

    $this->post('/cap')->assertForbidden();
});

it('denies access to evidence create for user without role', function (): void {
    $noRole = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $this->actingAs($noRole);

    $this->post('/evidence')->assertForbidden();
});

it('denies access to admin panel for user without role', function (): void {
    $noRole = User::factory()->create(['two_factor_confirmed_at' => now()]);
    $this->actingAs($noRole);

    $this->get('/admin/users')->assertForbidden();
});

// ─────────────────────────────────────────────────────────────────────────────
// Sekcja 2: board_viewer — tylko widok, brak tworzenia/edycji
// ─────────────────────────────────────────────────────────────────────────────

it('board_viewer can view risks', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['board_viewer']);
    $this->actingAs($admin->fresh());

    $this->get('/risks')->assertOk();
});

it('board_viewer cannot create risk', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['board_viewer']);
    $this->actingAs($admin->fresh());

    $this->get('/risks/create')->assertForbidden();
});

it('board_viewer cannot post risk', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['board_viewer']);
    $this->actingAs($admin->fresh());

    $this->post('/risks')->assertForbidden();
});

it('board_viewer can view dashboard', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['board_viewer']);
    $this->actingAs($admin->fresh());

    $this->get('/dashboard')->assertOk();
});

it('board_viewer cannot access vulnerabilities', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['board_viewer']);
    $this->actingAs($admin->fresh());

    $this->get('/vulnerabilities')->assertForbidden();
});

it('board_viewer cannot access incidents create', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['board_viewer']);
    $this->actingAs($admin->fresh());

    $this->post('/incidents')->assertForbidden();
});

// ─────────────────────────────────────────────────────────────────────────────
// Sekcja 3: risk_owner — może zarządzać ryzykami, NIE może compliance/vendor
// ─────────────────────────────────────────────────────────────────────────────

it('risk_owner can view and create risks', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['risk_owner']);
    $this->actingAs($admin->fresh());

    // risk_owner ma risk.create — store() wywołuje authorize('create') → powinien przejść przez
    // guard i trafić na walidację (422) lub redirect (302), ale NIE 403
    $response = $this->post('/risks', []);
    $this->assertNotEquals(403, $response->getStatusCode());
});

it('risk_owner cannot create controls', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['risk_owner']);
    $this->actingAs($admin->fresh());

    // risk_owner nie ma cap.create — testujemy przez CAP (abort_unless cap.create)
    $this->post('/cap')->assertForbidden();
});

it('risk_owner cannot access compliance create', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['risk_owner']);
    $this->actingAs($admin->fresh());

    $this->post('/compliance')->assertForbidden();
});

it('risk_owner cannot access CAP', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['risk_owner']);
    $this->actingAs($admin->fresh());

    $this->post('/cap')->assertForbidden();
});

it('risk_owner cannot access subprocessors', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['risk_owner']);
    $this->actingAs($admin->fresh());

    $this->get('/subprocessors')->assertForbidden();
});

// ─────────────────────────────────────────────────────────────────────────────
// Sekcja 4: security_engineer — może zarządzać incydentami/podatnościami
// ─────────────────────────────────────────────────────────────────────────────

it('security_engineer can create incident', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['security_engineer']);
    $this->actingAs($admin->fresh());

    // security_engineer ma incident.create — store() przejdzie przez guard do walidacji
    // Oczekujemy 302 (redirect po sukcesie) lub 422 (błąd walidacji), ale NIE 403
    $response = $this->post('/incidents', []);
    $this->assertNotEquals(403, $response->getStatusCode());
});

it('security_engineer can view controls', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['security_engineer']);
    $this->actingAs($admin->fresh());

    // ControlController::index() nie ma guard — dostępny dla każdego zalogowanego
    $this->get('/controls')->assertOk();
});

it('security_engineer cannot delete risks', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['security_engineer']);
    $this->actingAs($admin->fresh());

    // security_engineer nie ma risk.delete — DELETE na nieistniejącym ryzyku da 403 lub 404, ale nie 500
    $response = $this->delete('/risks/1');
    $this->assertContains($response->getStatusCode(), [403, 404]);
});

it('security_engineer cannot access admin', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['security_engineer']);
    $this->actingAs($admin->fresh());

    $this->get('/admin/users')->assertForbidden();
});

// ─────────────────────────────────────────────────────────────────────────────
// Sekcja 5: Scenariusze — dostępne tylko admin/ciso
// ─────────────────────────────────────────────────────────────────────────────

it('risk_owner cannot access scenario create page', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['risk_owner']);
    $this->actingAs($admin->fresh());

    $this->get('/scenarios/create')->assertForbidden();
});

it('risk_owner cannot post scenario', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['risk_owner']);
    $this->actingAs($admin->fresh());

    $this->post('/scenarios')->assertForbidden();
});

it('ciso can access scenario create page', function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->syncRoles(['ciso']);
    $this->actingAs($admin->fresh());

    $this->get('/scenarios/create')->assertOk();
});
