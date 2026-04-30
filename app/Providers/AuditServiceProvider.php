<?php

namespace App\Providers;

use App\Models\AnswerLibrary;
use App\Models\Asset;
use App\Models\AuditEngagement;
use App\Models\Certification;
use App\Models\Client;
use App\Models\Control;
use App\Models\ControlTest;
use App\Models\CorrectiveActionPlan;
use App\Models\EvidenceObject;
use App\Models\EvidenceRequest;
use App\Models\Finding;
use App\Models\Incident;
use App\Models\Indicator;
use App\Models\MinimumControlRequirement;
use App\Models\Policy;
use App\Models\QuestionnaireQuestion;
use App\Models\ReportInstance;
use App\Models\Risk;
use App\Models\RiskAcceptance;
use App\Models\RiskTreatmentPlan;
use App\Models\RtpAction;
use App\Models\SecurityQuestionnaire;
use App\Models\Subprocessor;
use App\Models\ThirdParty;
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
            AuditEngagement::class, EvidenceRequest::class, Finding::class,
            CorrectiveActionPlan::class, EvidenceObject::class, ReportInstance::class,
            Policy::class, ThirdParty::class, Subprocessor::class, Client::class,
            // Trust + TPRM + Questionnaires
            AnswerLibrary::class, SecurityQuestionnaire::class, QuestionnaireQuestion::class,
            MinimumControlRequirement::class, VendorAssessment::class, VendorAssessmentResponse::class,
            Certification::class,
        ];

        foreach ($observable as $modelClass) {
            $modelClass::observe(AuditableObserver::class);
        }
    }
}
