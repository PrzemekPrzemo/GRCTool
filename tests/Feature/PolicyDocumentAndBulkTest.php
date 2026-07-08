<?php

use App\Models\AppSetting;
use App\Models\EvidenceLink;
use App\Models\Policy;
use App\Models\PolicyVersion;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

function makePolicy(array $overrides = []): Policy
{
    return Policy::create(array_merge([
        'code'   => 'POL-TEST-'.random_int(1000, 999999),
        'title'  => 'Testowa polityka',
        'status' => 'Draft',
    ], $overrides));
}

it('creating a policy records an initial version', function (): void {
    $this->post('/policies', [
        'title'            => 'Polityka haseł',
        'current_version'  => '1.0',
        'status'           => 'Draft',
    ])->assertRedirect();

    $policy = Policy::where('title', 'Polityka haseł')->firstOrFail();
    expect(PolicyVersion::where('policy_id', $policy->id)->count())->toBe(1);
});

it('attaches a Google Drive link to a policy without any API configuration', function (): void {
    $policy = makePolicy();

    $this->post("/policies/{$policy->id}/documents", [
        'title'     => 'Polityka PDF',
        'drive_url' => 'https://drive.google.com/file/d/abc123/view',
    ])->assertRedirect();

    $policy->refresh();
    expect($policy->documentLinks()->count())->toBe(1);
    $evidence = $policy->documentLinks()->first()->evidence;
    expect($evidence->source)->toBe('drive_link');
    expect($evidence->external_url)->toBe('https://drive.google.com/file/d/abc123/view');
});

it('detaches a document from a policy', function (): void {
    $policy = makePolicy();
    $this->post("/policies/{$policy->id}/documents", ['drive_url' => 'https://drive.google.com/file/d/xyz/view']);
    $link = $policy->documentLinks()->first();

    $this->delete("/policies/{$policy->id}/documents/{$link->id}")->assertRedirect();

    expect(EvidenceLink::find($link->id))->toBeNull();
});

it('refuses to sync a document when Google Drive API is disabled', function (): void {
    $policy = makePolicy();
    $this->post("/policies/{$policy->id}/documents", [
        'drive_url'     => 'https://drive.google.com/file/d/xyz/view',
        'drive_file_id' => 'xyz',
    ]);
    $link = $policy->documentLinks()->first();

    $response = $this->post("/policies/{$policy->id}/documents/{$link->id}/sync");

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

it('bulk-updates status and owner for multiple selected policies', function (): void {
    $owner = User::where('email', 'ciso@grc.local')->firstOrFail();
    $p1 = makePolicy(['status' => 'Draft']);
    $p2 = makePolicy(['status' => 'Draft']);

    $this->post('/policies/bulk-update', [
        'ids'          => [$p1->id, $p2->id],
        'apply_status' => '1',
        'status'       => 'Active',
        'apply_owner'  => '1',
        'owner_id'     => $owner->id,
    ])->assertRedirect(route('policies.index'));

    expect($p1->fresh()->status)->toBe('Active');
    expect($p2->fresh()->status)->toBe('Active');
    expect($p1->fresh()->owner_id)->toBe($owner->id);
    expect(PolicyVersion::where('policy_id', $p1->id)->count())->toBe(1);
});

it('bulk-update requires at least one field to be marked for change', function (): void {
    $p1 = makePolicy();

    $response = $this->post('/policies/bulk-update', ['ids' => [$p1->id]]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

it('imports policies from CSV including a Google Drive link column', function (): void {
    $csv = "code,title,category,current_version,status,owner_email,effective_from,next_review_due,description,drive_url\n"
        ."POL-CSV-0001,Polityka importowana,ISMS,1.0,Draft,,,,,https://drive.google.com/file/d/csv1/view\n";

    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('policies.csv', $csv);

    $this->post('/policies/import', ['file' => $file])->assertRedirect(route('policies.index'));

    $policy = Policy::where('code', 'POL-CSV-0001')->firstOrFail();
    expect($policy->title)->toBe('Polityka importowana');
    expect($policy->documentLinks()->count())->toBe(1);
});

it('non-admin cannot view Google Drive settings page', function (): void {
    $user = User::factory()->create();
    $user->two_factor_confirmed_at = now();
    $user->save();

    $this->actingAs($user)->get('/admin/google-drive-settings')->assertForbidden();
});

it('admin can save Google Drive settings with enable toggle and credentials are encrypted at rest', function (): void {
    $credentials = json_encode(['type' => 'service_account', 'client_email' => 'svc@project.iam.gserviceaccount.com']);

    $this->put('/admin/google-drive-settings', [
        'google_drive_folder_id'    => 'folder123',
        'google_drive_credentials'  => $credentials,
        'google_drive_enabled'      => '1',
    ])->assertRedirect(route('admin.google-drive.show'));

    expect(AppSetting::get('google_drive_enabled'))->toBe('1');
    expect(AppSetting::get('google_drive_client_email'))->toBe('svc@project.iam.gserviceaccount.com');

    $encrypted = AppSetting::get('google_drive_credentials_encrypted');
    expect($encrypted)->not->toBe($credentials);
    expect(\Illuminate\Support\Facades\Crypt::decryptString($encrypted))->toBe($credentials);
});

it('rejects invalid JSON when saving Google Drive credentials', function (): void {
    $this->put('/admin/google-drive-settings', [
        'google_drive_credentials' => 'not-json',
    ])->assertSessionHasErrors('google_drive_credentials');
});
