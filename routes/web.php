<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AnswerLibraryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuditEngagementController;
use App\Http\Controllers\Admin\EntraIdSettingsController;
use App\Http\Controllers\Admin\GoogleDriveSettingsController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\Auth\MicrosoftController;
use App\Http\Controllers\BcpController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\CryptoKeyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DpiaController;
use App\Http\Controllers\DsarRequestController;
use App\Http\Controllers\ExceptionController;
use App\Http\Controllers\GdprBreachController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\Nis2AssessmentController;
use App\Http\Controllers\FindingController;
use App\Http\Controllers\InboundQuestionnaireController;
use App\Http\Controllers\IndicatorController;
use App\Http\Controllers\McrController;
use App\Http\Controllers\OrgMetricsController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ProcessingActivityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\ScenarioController;
use App\Http\Controllers\ThirdPartyController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrustCenterController;
use App\Http\Controllers\VendorAssessmentController;
use App\Http\Controllers\VendorPortalController;
use App\Http\Controllers\VulnerabilityController;
use App\Http\Controllers\AccessReviewController;
use App\Http\Controllers\ComplianceAssessmentController;
use App\Http\Controllers\ComplianceFrameworkAdminController;
use App\Http\Controllers\RiskTreatmentPlanController;
use App\Http\Controllers\SdlcController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\BusinessUnitController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RiskAcceptanceController;
use App\Http\Controllers\SubprocessorController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Public Trust Center — bez logowania
Route::get('trust', [TrustCenterController::class, 'index'])->name('trust.public');
Route::get('trust/policies/{policy}', [TrustCenterController::class, 'policy'])->name('trust.policy');

// Vendor self-service portal — token-based, bez logowania
Route::get('vendor-portal/{token}', [VendorPortalController::class, 'show'])->name('vendor-portal.show');
Route::post('vendor-portal/{token}/responses/{mcrId}', [VendorPortalController::class, 'updateResponse'])->name('vendor-portal.update');
Route::post('vendor-portal/{token}/submit', [VendorPortalController::class, 'submit'])->name('vendor-portal.submit');

// Google OAuth (no middleware — handles its own auth state)
Route::get('auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

// Microsoft Entra ID OAuth (no middleware — handles its own auth state)
Route::get('auth/microsoft', [MicrosoftController::class, 'redirect'])->name('auth.microsoft');
Route::get('auth/microsoft/callback', [MicrosoftController::class, 'callback'])->name('auth.microsoft.callback');

// Auth (guest)
Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('mfa/challenge', [LoginController::class, 'showMfaChallenge'])->name('mfa.challenge');
    Route::post('mfa/challenge', [LoginController::class, 'verifyMfa'])->name('mfa.verify');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// MFA setup (auth required, but bez wymuszenia mfa middleware)
Route::middleware('auth')->group(function (): void {
    Route::get('mfa/setup', [MfaController::class, 'showSetup'])->name('mfa.setup');
    Route::post('mfa/setup', [MfaController::class, 'confirm'])->name('mfa.confirm');
    Route::delete('mfa/setup', [MfaController::class, 'disable'])->name('mfa.disable');
    Route::get('mfa/recovery-codes', [MfaController::class, 'recoveryCodes'])->name('mfa.recovery_codes');
});

// Authenticated + MFA enforced
Route::middleware(['auth', 'mfa'])->group(function (): void {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/export', [DashboardController::class, 'exportPdf'])->name('dashboard.export');

    // Assets
    Route::get('assets/import', [AssetController::class, 'showImport'])->name('assets.import.show');
    Route::post('assets/import', [AssetController::class, 'import'])->name('assets.import');
    Route::resource('assets', AssetController::class);

    // Risks
    Route::resource('risks', RiskController::class);
    Route::post('risks/{risk}/review', [RiskController::class, 'review'])->name('risks.review');
    Route::post('risks/{risk}/acceptance', [RiskController::class, 'proposeAcceptance'])->name('risks.acceptance.propose');
    Route::post('risks/{risk}/acceptance/{acceptance}/approve', [RiskController::class, 'approveAcceptance'])->name('risks.acceptance.approve');
    Route::post('risks/{risk}/rtp', [RiskController::class, 'createTreatmentPlan'])->name('risks.rtp.create');
    Route::post('rtp/{plan}/action', [RiskController::class, 'addAction'])->name('risks.rtp.action');

    // Scenarios
    Route::get('scenarios', [ScenarioController::class, 'index'])->name('scenarios.index');
    Route::get('scenarios/create', [ScenarioController::class, 'create'])->name('scenarios.create');
    Route::post('scenarios', [ScenarioController::class, 'store'])->name('scenarios.store');
    Route::get('scenarios/{scenario}/edit', [ScenarioController::class, 'edit'])->name('scenarios.edit');
    Route::put('scenarios/{scenario}', [ScenarioController::class, 'update'])->name('scenarios.update');
    Route::get('scenarios/{scenario}', [ScenarioController::class, 'show'])->name('scenarios.show');
    Route::post('scenarios/{scenario}/adopt', [RiskController::class, 'adoptScenario'])->name('scenarios.adopt');

    // Risk Treatment Plans & Actions
    Route::get('risk-treatment-plans', [RiskTreatmentPlanController::class, 'index'])->name('risk-treatment-plans.index');
    Route::get('risk-treatment-plans/{plan}', [RiskTreatmentPlanController::class, 'show'])->name('risk-treatment-plans.show');
    Route::patch('rtp-actions/{action}', [RiskController::class, 'updateAction'])->name('rtp-actions.update');

    // Controls
    Route::get('controls/soa', [ControlController::class, 'soa'])->name('controls.soa');
    Route::get('controls/cross-mapping', [ControlController::class, 'crossMapping'])->name('controls.cross-mapping');
    Route::resource('controls', ControlController::class)->except(['destroy']);
    Route::post('controls/{control}/test', [ControlController::class, 'recordTest'])->name('controls.test');

    // Indicators
    Route::resource('indicators', IndicatorController::class)->except(['destroy']);
    Route::post('indicators/{indicator}/measurement', [IndicatorController::class, 'recordMeasurement'])->name('indicators.measurement');
    Route::post('indicators/{indicator}/import', [IndicatorController::class, 'importMeasurements'])->name('indicators.import');

    // Incidents
    Route::resource('incidents', IncidentController::class);
    Route::post('incidents/{incident}/status', [IncidentController::class, 'updateStatus'])->name('incidents.status');
    Route::post('incidents/{incident}/breach', [IncidentController::class, 'toggleBreach'])->name('incidents.breach');

    // NIS2 Applicability Assessments
    Route::resource('nis2', Nis2AssessmentController::class)->parameters(['nis2' => 'nis2']);
    Route::post('nis2/{nis2}/finalize', [Nis2AssessmentController::class, 'finalize'])->name('nis2.finalize');

    // Export CSV
    Route::prefix('export')->name('export.')->group(function (): void {
        Route::get('risks',           [ExportController::class, 'risks'])->name('risks');
        Route::get('controls',        [ExportController::class, 'controls'])->name('controls');
        Route::get('vulnerabilities', [ExportController::class, 'vulnerabilities'])->name('vulnerabilities');
        Route::get('findings',        [ExportController::class, 'findings'])->name('findings');
        Route::get('incidents',       [ExportController::class, 'incidents'])->name('incidents');
    });

    // Global Search
    Route::get('search', [SearchController::class, 'index'])->name('search');

    // Comments (polymorphic)
    Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Vulnerabilities
    Route::get('vulnerabilities/import', [VulnerabilityController::class, 'showImport'])->name('vulnerabilities.import.show');
    Route::post('vulnerabilities/import', [VulnerabilityController::class, 'import'])->name('vulnerabilities.import');
    Route::get('vulnerabilities/create', [VulnerabilityController::class, 'create'])->name('vulnerabilities.create');
    Route::post('vulnerabilities', [VulnerabilityController::class, 'store'])->name('vulnerabilities.store');
    Route::get('vulnerabilities', [VulnerabilityController::class, 'index'])->name('vulnerabilities.index');
    Route::get('vulnerabilities/{vulnerability}/edit', [VulnerabilityController::class, 'edit'])->name('vulnerabilities.edit');
    Route::put('vulnerabilities/{vulnerability}', [VulnerabilityController::class, 'update'])->name('vulnerabilities.update');
    Route::get('vulnerabilities/{vulnerability}', [VulnerabilityController::class, 'show'])->name('vulnerabilities.show');
    Route::post('vulnerabilities/{vulnerability}/close', [VulnerabilityController::class, 'close'])->name('vulnerabilities.close');
    Route::post('vulnerabilities/{vulnerability}/exceptions', [VulnerabilityController::class, 'proposeException'])->name('vulnerabilities.exceptions.propose');
    Route::post('vulnerabilities/exceptions/{exception}/approve', [VulnerabilityController::class, 'approveException'])->name('vulnerabilities.exceptions.approve');

    // Evidence
    Route::resource('evidence', EvidenceController::class)->except(['destroy']);
    Route::delete('evidence/{evidence}', [EvidenceController::class, 'destroy'])->name('evidence.destroy');

    // Clients
    Route::resource('clients', ClientController::class)->except(['destroy']);

    // Business Units
    Route::resource('business-units', BusinessUnitController::class)->except(['destroy']);

    // Projects
    Route::resource('projects', ProjectController::class)->except(['destroy']);

    // Audit engagements
    Route::resource('engagements', AuditEngagementController::class)->except(['destroy']);
    Route::post('engagements/{engagement}/evidence-request', [AuditEngagementController::class, 'addEvidenceRequest'])->name('engagements.evidence_request');
    Route::post('engagements/{engagement}/finding', [AuditEngagementController::class, 'addFinding'])->name('engagements.finding');

    // Findings
    Route::get('findings/create', [FindingController::class, 'create'])->name('findings.create');
    Route::post('findings', [FindingController::class, 'store'])->name('findings.store');
    Route::get('findings', [FindingController::class, 'index'])->name('findings.index');
    Route::get('findings/{finding}/edit', [FindingController::class, 'edit'])->name('findings.edit');
    Route::put('findings/{finding}', [FindingController::class, 'update'])->name('findings.update');
    Route::get('findings/{finding}', [FindingController::class, 'show'])->name('findings.show');
    Route::post('findings/{finding}/close', [FindingController::class, 'close'])->name('findings.close');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/generate/{template}', [ReportController::class, 'generate'])->name('reports.generate');
    Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');
    Route::post('reports/{report}/revoke', [ReportController::class, 'revoke'])->name('reports.revoke');

    // AnswerLibrary
    Route::resource('answer-library', AnswerLibraryController::class)->parameters(['answer-library' => 'answer'])->except(['destroy']);
    Route::post('answer-library/{answer}/review', [AnswerLibraryController::class, 'review'])->name('answer-library.review');

    // MCR
    Route::resource('mcr', McrController::class)->except(['destroy']);

    // Inbound questionnaires (od klientów)
    Route::resource('questionnaires', InboundQuestionnaireController::class)->except(['destroy']);
    Route::post('questionnaires/{questionnaire}/auto-fill', [InboundQuestionnaireController::class, 'autoFill'])->name('questionnaires.auto_fill');
    Route::post('questionnaires/{questionnaire}/questions/{question}/answer', [InboundQuestionnaireController::class, 'updateQuestion'])->name('questionnaires.question.update');
    Route::post('questionnaires/{questionnaire}/questions/{question}/approve', [InboundQuestionnaireController::class, 'approveQuestion'])->name('questionnaires.question.approve');
    Route::post('questionnaires/{questionnaire}/export', [InboundQuestionnaireController::class, 'export'])->name('questionnaires.export');

    // Vendor assessments (outbound)
    Route::resource('vendor-assessments', VendorAssessmentController::class)->parameters(['vendor-assessments' => 'assessment'])->except(['destroy']);
    Route::post('vendor-assessments/{assessment}/send', [VendorAssessmentController::class, 'send'])->name('vendor-assessments.send');
    Route::post('vendor-assessments/{assessment}/responses/{response}/review', [VendorAssessmentController::class, 'reviewResponse'])->name('vendor-assessments.response.review');
    Route::post('vendor-assessments/{assessment}/finalize', [VendorAssessmentController::class, 'finalize'])->name('vendor-assessments.finalize');

    // RODO — RCP (Rejestr Czynności Przetwarzania, Art. 30 GDPR)
    Route::resource('rcp', ProcessingActivityController::class);

    // RODO — Naruszenia danych osobowych (Art. 33/34 GDPR)
    Route::resource('gdpr-breaches', GdprBreachController::class)->parameters(['gdpr-breaches' => 'gdprBreach']);
    Route::post('gdpr-breaches/{gdprBreach}/notify-uodo', [GdprBreachController::class, 'notifyUodo'])->name('gdpr-breaches.notify-uodo');

    // RODO — DPIA (Art. 35 GDPR)
    Route::resource('dpias', DpiaController::class);
    Route::post('dpias/{dpia}/approve', [DpiaController::class, 'approve'])->name('dpias.approve');

    // RODO — DSAR (Art. 15-22 GDPR)
    Route::resource('dsar', DsarRequestController::class);
    Route::post('dsar/{dsar}/complete', [DsarRequestController::class, 'complete'])->name('dsar.complete');
    Route::post('dsar/{dsar}/extend', [DsarRequestController::class, 'extend'])->name('dsar.extend');

    // Risk Acceptances
    Route::get('risk-acceptances', [RiskAcceptanceController::class, 'index'])->name('risk-acceptances.index');
    Route::get('risk-acceptances/{acceptance}', [RiskAcceptanceController::class, 'show'])->name('risk-acceptances.show');
    Route::post('risk-acceptances/{acceptance}/approve', [RiskAcceptanceController::class, 'approve'])->name('risk-acceptances.approve');
    Route::post('risk-acceptances/{acceptance}/reject', [RiskAcceptanceController::class, 'reject'])->name('risk-acceptances.reject');
    Route::post('risk-acceptances/{acceptance}/revoke', [RiskAcceptanceController::class, 'revoke'])->name('risk-acceptances.revoke');

    // Subprocessors
    Route::resource('subprocessors', SubprocessorController::class)->except(['destroy']);
    Route::post('subprocessors/{subprocessor}/notify', [SubprocessorController::class, 'notify'])->name('subprocessors.notify');

    // Third Parties
    Route::resource('third-parties', ThirdPartyController::class)->parameters(['third-parties' => 'thirdParty']);

    // Policies
    Route::get('policies/import', [PolicyController::class, 'showImport'])->name('policies.import.show');
    Route::post('policies/import', [PolicyController::class, 'import'])->name('policies.import');
    Route::get('policies/bulk-edit', [PolicyController::class, 'bulkEdit'])->name('policies.bulk-edit');
    Route::post('policies/bulk-update', [PolicyController::class, 'bulkUpdate'])->name('policies.bulk-update');
    Route::resource('policies', PolicyController::class);
    Route::post('policies/{policy}/approve', [PolicyController::class, 'approve'])->name('policies.approve');
    Route::post('policies/{policy}/attest', [PolicyController::class, 'attest'])->name('policies.attest');
    Route::post('policies/{policy}/documents', [PolicyController::class, 'attachDocument'])->name('policies.documents.store');
    Route::delete('policies/{policy}/documents/{document}', [PolicyController::class, 'detachDocument'])->name('policies.documents.destroy');
    Route::post('policies/{policy}/documents/{document}/sync', [PolicyController::class, 'syncDocument'])->name('policies.documents.sync');

    // Training & Awareness
    Route::resource('trainings', TrainingController::class)->except(['destroy']);
    Route::post('trainings/{training}/complete', [TrainingController::class, 'recordCompletion'])->name('trainings.complete');
    Route::get('trainings-report', [TrainingController::class, 'report'])->name('trainings.report');

    // Exception Management
    Route::resource('exceptions', ExceptionController::class)->except(['destroy']);
    Route::post('exceptions/{exception}/submit', [ExceptionController::class, 'submit'])->name('exceptions.submit');
    Route::post('exceptions/{exception}/approve', [ExceptionController::class, 'approve'])->name('exceptions.approve');
    Route::post('exceptions/{exception}/reject', [ExceptionController::class, 'reject'])->name('exceptions.reject');

    // Certificate Inventory
    Route::resource('certificates', CertificateController::class)->except(['destroy']);
    Route::post('certificates/{certificate}/revoke', [CertificateController::class, 'revoke'])->name('certificates.revoke');

    // Crypto Key Inventory
    Route::resource('crypto-keys', CryptoKeyController::class)->parameters(['crypto-keys' => 'cryptoKey'])->except(['destroy']);
    Route::post('crypto-keys/{cryptoKey}/rotate', [CryptoKeyController::class, 'rotate'])->name('crypto-keys.rotate');

    // BCP / DR
    Route::resource('bcp', BcpController::class)->except(['destroy']);
    Route::post('bcp/{bcp}/approve', [BcpController::class, 'approve'])->name('bcp.approve');
    Route::post('bcp/{bcp}/test', [BcpController::class, 'addTest'])->name('bcp.test');

    // Org Metrics Dashboard
    Route::get('org-metrics', [OrgMetricsController::class, 'index'])->name('org-metrics.index');

    // Secure SDLC / AppSec
    Route::resource('sdlc', SdlcController::class)->except(['destroy']);
    Route::post('sdlc/{sdlc}/gate', [SdlcController::class, 'addGate'])->name('sdlc.gate.add');
    Route::post('sdlc/{sdlc}/gate/{gate}', [SdlcController::class, 'updateGate'])->name('sdlc.gate.update');
    Route::post('sdlc/{sdlc}/threat-model', [SdlcController::class, 'addThreatModel'])->name('sdlc.threat_model.add');

    // Access Reviews
    Route::resource('access-reviews', AccessReviewController::class)->parameters(['access-reviews' => 'campaign'])->except(['destroy']);
    Route::post('access-reviews/{campaign}/launch', [AccessReviewController::class, 'launch'])->name('access-reviews.launch');
    Route::post('access-reviews/{campaign}/complete', [AccessReviewController::class, 'complete'])->name('access-reviews.complete');
    Route::post('access-reviews/{campaign}/items', [AccessReviewController::class, 'addItem'])->name('access-reviews.item.add');
    Route::post('access-reviews/{campaign}/items/{item}/decide', [AccessReviewController::class, 'decide'])->name('access-reviews.item.decide');
    Route::post('access-reviews/{campaign}/bulk-approve', [AccessReviewController::class, 'bulkApprove'])->name('access-reviews.bulk_approve');

    // Compliance Management
    Route::get('compliance/frameworks', [ComplianceAssessmentController::class, 'frameworks'])->name('compliance.frameworks');
    Route::resource('compliance', ComplianceAssessmentController::class)->except(['destroy']);
    Route::post('compliance/{assessment}/complete', [ComplianceAssessmentController::class, 'complete'])->name('compliance.complete');
    Route::post('compliance/{assessment}/publish', [ComplianceAssessmentController::class, 'publish'])->name('compliance.publish');
    Route::get('compliance/{assessment}/respond', [ComplianceAssessmentController::class, 'showRespond'])->name('compliance.respond');
    Route::post('compliance/{assessment}/respond', [ComplianceAssessmentController::class, 'respond'])->name('compliance.respond.save');
    Route::get('compliance/{assessment}/export/csv', [ComplianceAssessmentController::class, 'exportCsv'])->name('compliance.export.csv');
    Route::get('compliance/{assessment}/export/soa', [ComplianceAssessmentController::class, 'exportSoa'])->name('compliance.export.soa');
    Route::get('compliance/{assessment}/export/gap', [ComplianceAssessmentController::class, 'exportGap'])->name('compliance.export.gap');

    // Compliance Framework Administration
    Route::prefix('compliance/manage')->name('compliance.admin.')->group(function (): void {
        Route::get('frameworks', [ComplianceFrameworkAdminController::class, 'index'])->name('frameworks');
        Route::get('frameworks/create', [ComplianceFrameworkAdminController::class, 'create'])->name('frameworks.create');
        Route::post('frameworks', [ComplianceFrameworkAdminController::class, 'store'])->name('frameworks.store');
        Route::get('frameworks/{framework}/edit', [ComplianceFrameworkAdminController::class, 'edit'])->name('frameworks.edit');
        Route::put('frameworks/{framework}', [ComplianceFrameworkAdminController::class, 'update'])->name('frameworks.update');
        Route::delete('frameworks/{framework}', [ComplianceFrameworkAdminController::class, 'destroy'])->name('frameworks.destroy');
        Route::get('frameworks/{framework}/domains', [ComplianceFrameworkAdminController::class, 'domains'])->name('frameworks.domains');
        Route::get('frameworks/{framework}/domains/create', [ComplianceFrameworkAdminController::class, 'createDomain'])->name('domains.create');
        Route::post('frameworks/{framework}/domains', [ComplianceFrameworkAdminController::class, 'storeDomain'])->name('domains.store');
        Route::get('frameworks/{framework}/domains/{domain}/edit', [ComplianceFrameworkAdminController::class, 'editDomain'])->name('domains.edit');
        Route::put('frameworks/{framework}/domains/{domain}', [ComplianceFrameworkAdminController::class, 'updateDomain'])->name('domains.update');
        Route::delete('frameworks/{framework}/domains/{domain}', [ComplianceFrameworkAdminController::class, 'destroyDomain'])->name('domains.destroy');
        Route::get('frameworks/{framework}/domains/{domain}/requirements', [ComplianceFrameworkAdminController::class, 'requirements'])->name('requirements.index');
        Route::get('frameworks/{framework}/domains/{domain}/requirements/create', [ComplianceFrameworkAdminController::class, 'createRequirement'])->name('requirements.create');
        Route::post('frameworks/{framework}/domains/{domain}/requirements', [ComplianceFrameworkAdminController::class, 'storeRequirement'])->name('requirements.store');
        Route::get('frameworks/{framework}/domains/{domain}/requirements/{requirement}/edit', [ComplianceFrameworkAdminController::class, 'editRequirement'])->name('requirements.edit');
        Route::put('frameworks/{framework}/domains/{domain}/requirements/{requirement}', [ComplianceFrameworkAdminController::class, 'updateRequirement'])->name('requirements.update');
        Route::delete('frameworks/{framework}/domains/{domain}/requirements/{requirement}', [ComplianceFrameworkAdminController::class, 'destroyRequirement'])->name('requirements.destroy');
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('users', AdminUserController::class);
        Route::post('users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
        Route::post('users/{user}/reset', [AdminUserController::class, 'resetPassword'])->name('users.reset');
        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log');
        Route::resource('roles', RoleController::class)->except(['show']);
    });

    // Entra ID Settings (ciso + admin — handled in controller)
    Route::get('admin/entra-settings', [EntraIdSettingsController::class, 'show'])->name('admin.entra.show');
    Route::put('admin/entra-settings', [EntraIdSettingsController::class, 'update'])->name('admin.entra.update');

    // Google Drive Settings (ciso + admin — handled in controller)
    Route::get('admin/google-drive-settings', [GoogleDriveSettingsController::class, 'show'])->name('admin.google-drive.show');
    Route::put('admin/google-drive-settings', [GoogleDriveSettingsController::class, 'update'])->name('admin.google-drive.update');
    Route::post('admin/google-drive-settings/test', [GoogleDriveSettingsController::class, 'test'])->name('admin.google-drive.test');
});
