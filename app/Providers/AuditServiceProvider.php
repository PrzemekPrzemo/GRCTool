<?php

namespace App\Providers;

use App\Models\AnswerLibrary;
use App\Models\Asset;
use App\Models\AuditEngagement;
use App\Models\BcpPlan;
use App\Models\BcpTest;
use App\Models\CertificateInventory;
use App\Models\Certification;
use App\Models\Client;
use App\Models\ComplianceException;
use App\Models\Control;
use App\Models\ControlTest;
use App\Models\CorrectiveActionPlan;
use App\Models\CryptoKey;
use App\Models\Dpia;
use App\Models\DsarRequest;
use App\Models\EvidenceObject;
use App\Models\EvidenceRequest;
use App\Models\Finding;
use App\Models\GdprBreach;
use App\Models\Incident;
use App\Models\Indicator;
use App\Models\MinimumControlRequirement;
use App\Models\Nis2Assessment;
use App\Models\Policy;
use App\Models\ProcessingActivity;
use App\Models\QuestionnaireQuestion;
use App\Models\ReportInstance;
use App\Models\Risk;
use App\Models\RiskAcceptance;
use App\Models\RiskTreatmentPlan;
use App\Models\RtpAction;
use App\Models\SecurityQuestionnaire;
use App\Models\Subprocessor;
use App\Models\ThirdParty;
use App\Models\Training;
use App\Models\UserTrainingCompletion;
use App\Models\VendorAssessment;
use App\Models\VendorAssessmentResponse;
use App\Models\Vulnerability;
use App\Models\VulnerabilityException;
use App\Observers\AuditableObserver;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $observable = [
            Asset::class, Risk::class, RiskAcceptance::class, RiskTreatmentPlan::class, RtpAction::class,
            Control::class, ControlTest::class, Indicator::class,
            Vulnerability::class, VulnerabilityException::class, Incident::class,
            Nis2Assessment::class,
            ProcessingActivity::class, GdprBreach::class, Dpia::class, DsarRequest::class,
            AuditEngagement::class, EvidenceRequest::class, Finding::class,
            CorrectiveActionPlan::class, EvidenceObject::class, ReportInstance::class,
            Policy::class, ThirdParty::class, Subprocessor::class, Client::class,
            // Trust + TPRM + Questionnaires
            AnswerLibrary::class, SecurityQuestionnaire::class, QuestionnaireQuestion::class,
            MinimumControlRequirement::class, VendorAssessment::class, VendorAssessmentResponse::class,
            Certification::class,
            // New modules
            Training::class, UserTrainingCompletion::class, ComplianceException::class,
            CertificateInventory::class, CryptoKey::class, BcpPlan::class, BcpTest::class,
        ];

        foreach ($observable as $modelClass) {
            $modelClass::observe(AuditableObserver::class);
        }
    }
}
