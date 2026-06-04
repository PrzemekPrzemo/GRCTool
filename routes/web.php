<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AnswerLibraryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuditEngagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DpiaController;
use App\Http\Controllers\DsarRequestController;
use App\Http\Controllers\GdprBreachController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\Nis2AssessmentController;
use App\Http\Controllers\FindingController;
use App\Http\Controllers\InboundQuestionnaireController;
use App\Http\Controllers\IndicatorController;
use App\Http\Controllers\McrController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ProcessingActivityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\ScenarioController;
use App\Http\Controllers\ThirdPartyController;
use App\Http\Controllers\TrustCenterController;
use App\Http\Controllers\VendorAssessmentController;
use App\Http\Controllers\VendorPortalController;
use App\Http\Controllers\VulnerabilityController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Public Trust Center — bez logowania
Route::get('trust', [TrustCenterController::class, 'index'])->name('trust.public');
Route::get('trust/policies/{policy}', [TrustCenterController::class, 'policy'])->name('trust.policy');

// Vendor self-service portal — token-based, bez logowania
Route::get('vendor-portal/{token}', [VendorPortalController::class, 'show'])->name('vendor-portal.show');
Route::post('vendor-portal/{token}/responses/{mcrId}', [VendorPortalController::class, 'updateResponse'])->name('vendor-portal.update');
Route::post('vendor-portal/{token}/submit', [VendorPortalController::class, 'submit'])->name('vendor-portal.submit');

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
    Route::get('scenarios/{scenario}', [ScenarioController::class, 'show'])->name('scenarios.show');
    Route::post('scenarios/{scenario}/adopt', [RiskController::class, 'adoptScenario'])->name('scenarios.adopt');

    // Controls
    Route::get('controls/soa', [ControlController::class, 'soa'])->name('controls.soa');
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

    // Vulnerabilities
    Route::get('vulnerabilities/import', [VulnerabilityController::class, 'showImport'])->name('vulnerabilities.import.show');
    Route::post('vulnerabilities/import', [VulnerabilityController::class, 'import'])->name('vulnerabilities.import');
    Route::get('vulnerabilities', [VulnerabilityController::class, 'index'])->name('vulnerabilities.index');
    Route::get('vulnerabilities/{vulnerability}', [VulnerabilityController::class, 'show'])->name('vulnerabilities.show');
    Route::post('vulnerabilities/{vulnerability}/close', [VulnerabilityController::class, 'close'])->name('vulnerabilities.close');

    // Audit engagements
    Route::resource('engagements', AuditEngagementController::class)->except(['destroy']);
    Route::post('engagements/{engagement}/evidence-request', [AuditEngagementController::class, 'addEvidenceRequest'])->name('engagements.evidence_request');
    Route::post('engagements/{engagement}/finding', [AuditEngagementController::class, 'addFinding'])->name('engagements.finding');

    // Findings
    Route::get('findings', [FindingController::class, 'index'])->name('findings.index');
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

    // Third Parties
    Route::resource('third-parties', ThirdPartyController::class)->parameters(['third-parties' => 'thirdParty']);

    // Policies
    Route::resource('policies', PolicyController::class);
    Route::post('policies/{policy}/approve', [PolicyController::class, 'approve'])->name('policies.approve');
    Route::post('policies/{policy}/attest', [PolicyController::class, 'attest'])->name('policies.attest');

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::post('users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
        Route::post('users/{user}/reset', [AdminUserController::class, 'resetPassword'])->name('users.reset');
    });
});
