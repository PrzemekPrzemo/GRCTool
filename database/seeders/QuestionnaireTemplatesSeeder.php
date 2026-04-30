<?php

namespace Database\Seeders;

use App\Models\MinimumControlRequirement;
use App\Models\QuestionnaireTemplate;
use App\Models\QuestionnaireTemplateQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionnaireTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedSigLite();
            $this->seedCaiqLite();
            $this->seedMcrInternal();
        });
    }

    /** SIG Lite — Standardized Information Gathering (Lite) — popularne wśród klientów enterprise. Skrót. */
    private function seedSigLite(): void
    {
        $tpl = QuestionnaireTemplate::firstOrCreate(
            ['code' => 'SIG_LITE'],
            [
                'name' => 'SIG Lite (Standardized Information Gathering)',
                'description' => 'Shared Assessments SIG Lite — popularny questionnaire enterprise. Inbound — odpowiadamy klientom.',
                'source_org' => 'Shared Assessments',
                'direction' => 'inbound',
                'version' => '2026',
            ],
        );

        $questions = [
            ['SL-A-001', 'Risk Mgmt', 'Czy organizacja posiada udokumentowaną politykę bezpieczeństwa informacji?', 'yesno'],
            ['SL-A-002', 'Risk Mgmt', 'Czy polityka jest aktualizowana przynajmniej raz w roku?', 'yesno'],
            ['SL-A-003', 'Risk Mgmt', 'Kiedy ostatnio przeprowadzona była niezależna ocena ryzyka cyber?', 'text'],
            ['SL-B-001', 'Compliance', 'Jakie certyfikaty bezpieczeństwa posiadacie (ISO 27001, SOC 2, ITM, TISAX)?', 'evidence'],
            ['SL-B-002', 'Compliance', 'Czy zgodni z GDPR? Załącz DPA template.', 'evidence'],
            ['SL-C-001', 'Asset Mgmt', 'Czy prowadzicie inwentaryzację aktywów krytycznych?', 'yesno'],
            ['SL-D-001', 'IAM', 'Czy MFA jest wymuszone dla kont z dostępem do danych klienta?', 'yesno'],
            ['SL-D-002', 'IAM', 'Jaka jest polityka silnych haseł?', 'text'],
            ['SL-D-003', 'IAM', 'Jaki jest SLA off-boardingu pracowników?', 'text'],
            ['SL-E-001', 'Network', 'Czy stosujecie segmentację sieci produkcyjnych?', 'yesno'],
            ['SL-E-002', 'Network', 'Czy WAF jest wdrożony przed aplikacjami publicznymi?', 'yesno'],
            ['SL-F-001', 'AppSec', 'Czy SAST jest częścią pipeline CI/CD?', 'yesno'],
            ['SL-F-002', 'AppSec', 'Czy pentest aplikacji jest przeprowadzany rocznie?', 'evidence'],
            ['SL-F-003', 'AppSec', 'Jaki jest SLA patchowania Critical CVE?', 'text'],
            ['SL-G-001', 'Encryption', 'Jakim algorytmem są szyfrowane dane at-rest?', 'text'],
            ['SL-G-002', 'Encryption', 'Jaka minimalna wersja TLS dla in-transit?', 'text'],
            ['SL-H-001', 'Backup', 'Częstotliwość backupów krytycznych systemów?', 'text'],
            ['SL-H-002', 'Backup', 'Kiedy ostatnio testowano restore (DR drill)?', 'text'],
            ['SL-I-001', 'IR', 'Jaki jest SLA notyfikacji incydentu klientowi?', 'text'],
            ['SL-I-002', 'IR', 'Czy macie udokumentowany IR playbook?', 'yesno'],
            ['SL-I-003', 'IR', 'Kiedy ostatnio testowano IR plan?', 'text'],
            ['SL-J-001', 'Privacy', 'Jak przetwarzane są wnioski Data Subject Access (RODO Art. 15)?', 'text'],
            ['SL-J-002', 'Privacy', 'Lista subprocessorów dostępna publicznie?', 'evidence'],
            ['SL-K-001', 'BCP', 'RTO i RPO dla krytycznych usług?', 'text'],
            ['SL-K-002', 'BCP', 'Kiedy ostatnio testowany BCP?', 'text'],
        ];

        foreach ($questions as $i => [$code, $cat, $text, $type]) {
            QuestionnaireTemplateQuestion::firstOrCreate(
                ['template_id' => $tpl->id, 'code' => $code],
                ['category' => $cat, 'question_text' => $text, 'expected_answer_type' => $type, 'order' => $i],
            );
        }
    }

    /** CAIQ-Lite — Cloud Security Alliance Consensus Assessment Questionnaire (skrócona). */
    private function seedCaiqLite(): void
    {
        $tpl = QuestionnaireTemplate::firstOrCreate(
            ['code' => 'CAIQ_LITE'],
            [
                'name' => 'CAIQ Lite (CSA)',
                'description' => 'Cloud Security Alliance CAIQ — wersja skrócona dla klientów cloud-native. Inbound.',
                'source_org' => 'Cloud Security Alliance',
                'direction' => 'inbound',
                'version' => 'v4',
            ],
        );

        $questions = [
            ['CAIQ-AIS-01', 'Application Security', 'Czy aplikacja używa secure SDLC z code review?', 'yesno'],
            ['CAIQ-AIS-02', 'Application Security', 'Czy stosujecie threat modeling?', 'yesno'],
            ['CAIQ-DSI-01', 'Data Security', 'Polityki klasyfikacji danych — opisz.', 'text'],
            ['CAIQ-DSI-02', 'Data Security', 'Mechanism data deletion na koniec kontraktu?', 'text'],
            ['CAIQ-EKM-01', 'Encryption Key Mgmt', 'Gdzie generowane i przechowywane są klucze szyfrowania?', 'text'],
            ['CAIQ-IAM-01', 'Identity & Access', 'Czy SSO jest dostępne dla klientów?', 'yesno'],
            ['CAIQ-IAM-02', 'Identity & Access', 'RBAC granularność — opisz model uprawnień.', 'text'],
            ['CAIQ-IPY-01', 'Interoperability', 'Czy oferujecie API export danych w formie maszynowo-czytelnej?', 'yesno'],
            ['CAIQ-IVS-01', 'Infrastructure', 'Hosting w jakim regionie? Możliwość wyboru przez klienta?', 'text'],
            ['CAIQ-LOG-01', 'Logging', 'Czy klient ma dostęp do logów audytowych swoich operacji?', 'yesno'],
            ['CAIQ-MOS-01', 'Mobile Security', 'Polityka MDM dla pracowników z dostępem do danych klienta?', 'text'],
            ['CAIQ-PRI-01', 'Privacy', 'Czy DPA jest standardowo dostępna do podpisu?', 'yesno'],
            ['CAIQ-SEF-01', 'Sec. Incidents', 'SLA notyfikacji incident klientowi?', 'text'],
            ['CAIQ-STA-01', 'Supply Chain', 'Lista subprocessorów publicznie dostępna?', 'yesno'],
            ['CAIQ-TVM-01', 'Threat Mgmt', 'Vulnerability scanning — częstotliwość?', 'text'],
        ];

        foreach ($questions as $i => [$code, $cat, $text, $type]) {
            QuestionnaireTemplateQuestion::firstOrCreate(
                ['template_id' => $tpl->id, 'code' => $code],
                ['category' => $cat, 'question_text' => $text, 'expected_answer_type' => $type, 'order' => $i],
            );
        }
    }

    /** MCR Internal — outbound do dostawców, generowane z MinimumControlRequirements. */
    private function seedMcrInternal(): void
    {
        $tpl = QuestionnaireTemplate::firstOrCreate(
            ['code' => 'MCR_INTERNAL'],
            [
                'name' => 'Vendor Assessment — MCR Internal',
                'description' => 'Outbound questionnaire dla dostawców oparte o nasze Minimum Control Requirements.',
                'source_org' => 'Internal',
                'direction' => 'outbound',
                'version' => date('Y'),
            ],
        );

        $mcrs = MinimumControlRequirement::orderBy('order')->get();
        foreach ($mcrs as $i => $mcr) {
            QuestionnaireTemplateQuestion::firstOrCreate(
                ['template_id' => $tpl->id, 'code' => $mcr->code],
                [
                    'category' => $mcr->category,
                    'subcategory' => $mcr->subcategory,
                    'question_text' => $mcr->vendor_facing_text ?: $mcr->description,
                    'guidance' => $mcr->description,
                    'expected_answer_type' => $mcr->requires_evidence ? 'evidence' : 'text',
                    'framework_refs' => $mcr->framework_mappings,
                    'is_required' => $mcr->severity === 'Mandatory',
                    'order' => $i,
                ],
            );
        }
    }
}
