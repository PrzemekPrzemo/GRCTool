<?php

use App\Models\AppSetting;
use App\Models\Incident;
use App\Models\User;
use App\Models\Vulnerability;
use App\Services\Security\AwsSigV4Signer;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $admin = User::where('email', 'admin@grc.local')->firstOrFail();
    $admin->assignRole('ciso');
    $admin->two_factor_confirmed_at = now();
    $admin->save();
    $this->actingAs($admin->fresh());
});

// ─── Settings panels ────────────────────────────────────────────────────────

it('non-admin cannot view AWS Security Hub settings page', function (): void {
    $user = User::factory()->create();
    $user->two_factor_confirmed_at = now();
    $user->save();

    $this->actingAs($user)->get('/admin/aws-security-hub-settings')->assertForbidden();
});

it('admin can save AWS Security Hub settings and the secret is encrypted at rest', function (): void {
    $this->put('/admin/aws-security-hub-settings', [
        'aws_region' => 'eu-west-1',
        'aws_access_key_id' => 'AKIAEXAMPLE',
        'aws_secret_access_key' => 'super-secret',
        'aws_security_hub_enabled' => '1',
    ])->assertRedirect(route('admin.aws-security-hub.show'));

    expect(AppSetting::get('aws_security_hub_enabled'))->toBe('1');
    expect(AppSetting::get('aws_region'))->toBe('eu-west-1');

    $encrypted = AppSetting::get('aws_secret_access_key_encrypted');
    expect($encrypted)->not->toBe('super-secret');
    expect(Crypt::decryptString($encrypted))->toBe('super-secret');
});

it('saves Entra ID identity protection toggle alongside existing SSO settings', function (): void {
    $this->put('/admin/entra-settings', [
        'azure_client_id' => 'client-123',
        'azure_tenant_id' => 'tenant-123',
        'azure_identity_protection_enabled' => '1',
    ])->assertRedirect(route('admin.entra.show'));

    expect(AppSetting::get('azure_identity_protection_enabled'))->toBe('1');
    expect(AppSetting::get('azure_client_id'))->toBe('client-123');
});

it('saves Google Workspace Alert Center admin email and toggle', function (): void {
    $this->put('/admin/google-drive-settings', [
        'google_workspace_admin_email' => 'admin@example.com',
        'google_workspace_alerts_enabled' => '1',
    ])->assertRedirect(route('admin.google-drive.show'));

    expect(AppSetting::get('google_workspace_admin_email'))->toBe('admin@example.com');
    expect(AppSetting::get('google_workspace_alerts_enabled'))->toBe('1');
});

// ─── Entra ID Identity Protection sync ──────────────────────────────────────

it('sync-entra-identity-protection does nothing when disabled', function (): void {
    $this->artisan('grc:sync-entra-identity-protection')->assertSuccessful();

    expect(Incident::where('source', 'Entra ID Identity Protection')->count())->toBe(0);
});

it('sync-entra-identity-protection creates incidents from risk detections and dedups on re-run', function (): void {
    AppSetting::set('azure_client_id', 'client-123');
    AppSetting::set('azure_tenant_id', 'tenant-123');
    AppSetting::set('azure_client_secret_encrypted', Crypt::encryptString('secret'));
    AppSetting::set('azure_identity_protection_enabled', '1');

    Http::fake([
        'https://login.microsoftonline.com/*' => Http::response(['access_token' => 'fake-token'], 200),
        'https://graph.microsoft.com/v1.0/identityProtection/riskDetections*' => Http::response([
            'value' => [
                [
                    'id' => 'detection-1',
                    'riskEventType' => 'unfamiliarFeatures',
                    'riskLevel' => 'high',
                    'riskState' => 'atRisk',
                    'userDisplayName' => 'Jan Kowalski',
                    'detectedDateTime' => '2026-07-01T10:00:00Z',
                    'activityDateTime' => '2026-07-01T09:55:00Z',
                    'ipAddress' => '1.2.3.4',
                    'location' => ['city' => 'Warsaw', 'countryOrRegion' => 'PL'],
                ],
            ],
        ], 200),
    ]);

    $this->artisan('grc:sync-entra-identity-protection')->assertSuccessful();

    expect(Incident::where('source', 'Entra ID Identity Protection')->count())->toBe(1);
    $incident = Incident::where('source_ref', 'detection-1')->firstOrFail();
    expect($incident->severity)->toBe('High');
    expect($incident->status)->toBe('Investigating');

    $this->artisan('grc:sync-entra-identity-protection')->assertSuccessful();
    expect(Incident::where('source', 'Entra ID Identity Protection')->count())->toBe(1);
});

// ─── AWS Security Hub sync ──────────────────────────────────────────────────

it('sync-aws-security-hub-findings does nothing when disabled', function (): void {
    $this->artisan('grc:sync-aws-security-hub-findings')->assertSuccessful();

    expect(Vulnerability::where('source', 'AWS Security Hub')->count())->toBe(0);
});

it('sync-aws-security-hub-findings creates vulnerabilities from findings and dedups on re-run', function (): void {
    AppSetting::set('aws_region', 'eu-west-1');
    AppSetting::set('aws_access_key_id', 'AKIAEXAMPLE');
    AppSetting::set('aws_secret_access_key_encrypted', Crypt::encryptString('secret'));
    AppSetting::set('aws_security_hub_enabled', '1');

    Http::fake([
        'https://securityhub.eu-west-1.amazonaws.com/*' => Http::response([
            'Findings' => [
                [
                    'Id' => 'arn:aws:securityhub:eu-west-1:123456789012:finding/abc-123',
                    'Title' => 'S3 bucket publicly accessible',
                    'Description' => 'Bucket allows public read access.',
                    'Severity' => ['Label' => 'CRITICAL'],
                    'Workflow' => ['Status' => 'NEW'],
                    'FirstObservedAt' => '2026-07-01T00:00:00Z',
                ],
            ],
            'NextToken' => null,
        ], 200),
    ]);

    $this->artisan('grc:sync-aws-security-hub-findings')->assertSuccessful();

    expect(Vulnerability::where('source', 'AWS Security Hub')->count())->toBe(1);
    $vuln = Vulnerability::where('source_ref', 'arn:aws:securityhub:eu-west-1:123456789012:finding/abc-123')->firstOrFail();
    expect($vuln->severity)->toBe('Critical');
    expect($vuln->status)->toBe('Open');

    $this->artisan('grc:sync-aws-security-hub-findings')->assertSuccessful();
    expect(Vulnerability::where('source', 'AWS Security Hub')->count())->toBe(1);
});

it('AWS SigV4 signer produces an Authorization header with the expected structure', function (): void {
    $headers = AwsSigV4Signer::signJsonRequest(
        method: 'POST',
        host: 'securityhub.eu-west-1.amazonaws.com',
        path: '/',
        region: 'eu-west-1',
        service: 'securityhub',
        accessKeyId: 'AKIAEXAMPLE',
        secretAccessKey: 'secret',
        payload: '{}',
        extraHeaders: ['x-amz-target' => 'SecurityHub.GetFindings'],
    );

    expect($headers['authorization'])->toStartWith('AWS4-HMAC-SHA256 Credential=AKIAEXAMPLE/');
    expect($headers['authorization'])->toContain('/eu-west-1/securityhub/aws4_request');
    expect($headers['authorization'])->toContain('SignedHeaders=content-type;host;x-amz-date;x-amz-target');
    expect($headers)->toHaveKey('x-amz-date');
});
