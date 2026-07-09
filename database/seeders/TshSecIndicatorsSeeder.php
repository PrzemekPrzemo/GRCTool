<?php

namespace Database\Seeders;

use App\Models\Indicator;
use App\Models\IndicatorMeasurement;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Import TSH-SEC-REG-013 Security KPI Dashboard: 37 KPI + 20 KRI (definicje
 * + jeden pomiar Q1 2025, jedyny okres faktycznie wypełniony w źródle —
 * kolumny Q2-Q4 to puste szablony i nie są importowane).
 */
class TshSecIndicatorsSeeder extends Seeder
{
    private ?int $cisoId = null;

    public function run(): void
    {
        $this->cisoId = User::where('email', 'ciso@grc.local')->value('id');

        $this->seedKpis();
        $this->seedKris();
    }

    /**
     * @param  array{0:string,1:string,2:string,3:?float,4:string,5:?float,6:?string}  $row  code,name,unit,target,direction,q1value,rag
     */
    private function createKpi(array $row): void
    {
        [$code, $name, $unit, $target, $direction, $q1, $rag] = $row;

        $indicator = Indicator::updateOrCreate(
            ['code' => $code],
            [
                'name' => $name, 'type' => 'KPI', 'unit' => $unit,
                'target_value' => $target, 'direction' => $direction,
                'frequency' => 'quarterly', 'owner_id' => $this->cisoId,
                'consumer_audience' => 'Board', 'data_source' => 'TSH-SEC-REG-013',
            ]
        );

        if ($q1 !== null) {
            IndicatorMeasurement::updateOrCreate(
                ['indicator_id' => $indicator->id, 'measured_at' => '2025-03-31'],
                ['value' => $q1, 'status' => $rag]
            );
        }
    }

    private function seedKpis(): void
    {
        $rows = [
            ['KPI-INC-01', 'P1 incidents (active attacks, confirmed breaches)', 'count', 0, 'lower_is_better', 0, 'green'],
            ['KPI-INC-02', 'P2 incidents (suspected breach, major control failure)', 'count', 1, 'lower_is_better', 0, 'green'],
            ['KPI-INC-03', 'P3/P4 incidents — total logged (informational, no target)', 'count', null, 'lower_is_better', 3, null],
            ['KPI-INC-04', 'Mean time to contain P1/P2 (hours)', 'hours', 4, 'lower_is_better', null, null],
            ['KPI-INC-05', 'Post-incident reviews completed on time (P1/P2)', '%', 100, 'higher_is_better', null, null],
            ['KPI-INC-06', 'Client notifications sent within 24h SLA', '%', 100, 'higher_is_better', null, null],

            ['KPI-END-01', '% MacBooks enrolled in IRU and compliant', '%', 100, 'higher_is_better', 98, 'amber'],
            ['KPI-END-02', '% iPhones enrolled in IRU (company-owned)', '%', 100, 'higher_is_better', 100, 'green'],
            ['KPI-END-03', '% BYOD iPhones with MAM policy active', '%', 90, 'higher_is_better', 85, 'amber'],
            ['KPI-END-04', '% Windows devices compliant (Intune)', '%', 100, 'higher_is_better', 100, 'green'],
            ['KPI-END-05', 'Critical OS patches deployed within 48h', '%', 100, 'higher_is_better', 100, 'green'],
            ['KPI-END-06', 'High severity patches within 7 days', '%', 95, 'higher_is_better', 97, 'green'],
            ['KPI-END-07', 'Devices with FileVault/BitLocker OFF', 'count', 0, 'lower_is_better', 0, 'green'],

            ['KPI-ACC-01', 'MFA enrollment — all users', '%', 100, 'higher_is_better', 99, 'amber'],
            ['KPI-ACC-02', 'FIDO2 enrollment — admin accounts', '%', 100, 'higher_is_better', 100, 'green'],
            ['KPI-ACC-03', 'Offboarding SLA met (access revoked in time)', '%', 100, 'higher_is_better', 100, 'green'],
            ['KPI-ACC-04', 'Quarterly access review completion', '%', 100, 'higher_is_better', 100, 'green'],
            ['KPI-ACC-05', 'Stale accounts suspended (>90 days inactive)', 'count', 0, 'lower_is_better', 0, 'green'],
            ['KPI-ACC-06', 'PIM activations reviewed (monthly)', 'count', null, 'higher_is_better', null, 'green'],

            ['KPI-TRN-01', 'Onboarding security training — completion within 5 days', '%', 100, 'higher_is_better', 100, 'green'],
            ['KPI-TRN-02', 'Annual security training — completion rate', '%', 100, 'higher_is_better', 94, 'amber'],
            ['KPI-TRN-03', 'Phishing simulation click rate', '%', 5, 'lower_is_better', 3.2, 'green'],
            ['KPI-TRN-04', 'Security Champions — active coverage (teams with SC)', '%', 80, 'higher_is_better', 70, 'amber'],

            ['KPI-DLP-01', 'Google Workspace DLP rule matches — RESTRICTED blocked', 'count', null, 'lower_is_better', 2, null],
            ['KPI-DLP-02', 'MDE Endpoint DLP — USB copy blocked', 'count', null, 'lower_is_better', 0, null],
            ['KPI-DLP-03', 'DLP false positive rate (as % of all matches)', '%', 20, 'lower_is_better', 100, 'amber'],
            ['KPI-DLP-04', 'Active DLP rules in Google Admin', 'count', 3, 'higher_is_better', 4, 'green'],
            ['KPI-DLP-05', 'Projects with DPA signed (where PII processed)', '%', 100, 'higher_is_better', 100, 'green'],

            ['KPI-VUL-01', 'Critical CVEs open > 48h in TSH systems', 'count', 0, 'lower_is_better', 0, 'green'],
            ['KPI-VUL-02', 'High CVEs open > 7 days', 'count', 0, 'lower_is_better', 1, 'amber'],
            ['KPI-VUL-03', 'Dependabot/Snyk critical alerts unresolved in production', 'count', 0, 'lower_is_better', 0, 'green'],
            ['KPI-VUL-04', 'GitHub secret scanning alerts unresolved', 'count', 0, 'lower_is_better', 0, 'green'],
            ['KPI-VUL-05', 'Annual www.tsh.io pentest conducted', 'count', null, 'higher_is_better', null, null],

            ['KPI-RSK-01', 'Open Critical risks (score 20-25)', 'count', 0, 'lower_is_better', 0, 'green'],
            ['KPI-RSK-02', 'Open High risks (score 12-19)', 'count', 2, 'lower_is_better', 1, 'green'],
            ['KPI-RSK-03', 'Risk treatment plans overdue', 'count', 0, 'lower_is_better', 1, 'amber'],
            ['KPI-RSK-04', 'Security exceptions active (REG-012)', 'count', 3, 'lower_is_better', 1, 'green'],
        ];

        foreach ($rows as $row) {
            $this->createKpi($row);
        }
    }

    /**
     * @param  array{0:string,1:string,2:string,3:?float,4:?float,5:string,6:?float,7:string}  $row  code,name,unit,amber,red,direction,current,ownerRole
     */
    private function createKri(array $row): void
    {
        [$code, $name, $unit, $amber, $red, $direction, $current, $ownerRole] = $row;

        $indicator = Indicator::updateOrCreate(
            ['code' => $code],
            [
                'name' => $name, 'type' => 'KRI', 'unit' => $unit,
                'amber_threshold' => $amber, 'red_threshold' => $red, 'direction' => $direction,
                'frequency' => 'monthly', 'owner_id' => $ownerRole === 'CSO' ? $this->cisoId : null,
                'consumer_audience' => 'CISO', 'data_source' => 'TSH-SEC-REG-013',
            ]
        );

        if ($current !== null) {
            IndicatorMeasurement::updateOrCreate(
                ['indicator_id' => $indicator->id, 'measured_at' => '2025-03-31'],
                ['value' => $current, 'status' => 'green']
            );
        }
    }

    private function seedKris(): void
    {
        $rows = [
            ['KRI-01', 'Users without MFA active', '%', 3, 10, 'lower_is_better', 1, 'IT Lead'],
            ['KRI-02', 'Stale privileged accounts (not activated in PIM >60 days)', 'count', 2, 5, 'lower_is_better', 0, 'IT Lead'],
            ['KRI-03', 'Failed MFA attempts (per week)', 'count', 50, 200, 'lower_is_better', 12, 'IT Lead'],
            ['KRI-04', 'Access review response rate', '%', 95, 80, 'higher_is_better', 100, 'IT Lead'],
            ['KRI-05', 'Offboarding overdue (>24h after last day)', 'count', 1, 3, 'lower_is_better', 0, 'IT Lead'],

            ['KRI-06', 'Devices non-compliant in IRU/Intune', 'count', 5, 10, 'lower_is_better', 2, 'IT Lead'],
            ['KRI-07', 'Devices with OS below minimum version', 'count', 3, 8, 'lower_is_better', 0, 'IT Lead'],
            ['KRI-08', 'Critical patches undeployed >48h', 'count', 2, 5, 'lower_is_better', 0, 'IT Lead'],
            ['KRI-09', 'Devices without MDE agent active', 'count', 2, 5, 'lower_is_better', 0, 'IT Lead'],

            ['KRI-10', 'Critical CVEs in production dependencies (unresolved)', 'count', 2, 5, 'lower_is_better', 0, 'Tech Lead'],
            ['KRI-11', 'Secret scanning alerts unresolved', 'count', 1, 3, 'lower_is_better', 0, 'Tech Lead'],
            ['KRI-12', 'SAST critical findings in active repos (>7 days)', 'count', 3, 8, 'lower_is_better', 0, 'Tech Lead'],
            ['KRI-13', 'Repos without branch protection / SAST enabled', 'count', 1, 3, 'lower_is_better', 0, 'IT Lead'],

            ['KRI-14', 'Open High/Critical risks without treatment plan', 'count', 3, 5, 'lower_is_better', 1, 'CSO'],
            ['KRI-15', 'Security exceptions approaching expiry (<14 days)', 'count', 2, 4, 'lower_is_better', 0, 'CSO'],
            ['KRI-16', 'Compliance obligations with GAP status', 'count', 2, 4, 'lower_is_better', 0, 'CSO'],
            ['KRI-17', 'Annual tasks overdue (from REG-015)', 'count', 2, 4, 'lower_is_better', 0, 'CSO'],

            ['KRI-18', 'Staff without current security training', '%', 5, 15, 'lower_is_better', 0, 'CSO'],
            ['KRI-19', 'Phishing simulation click rate (last simulation)', '%', 5, 15, 'lower_is_better', 3.2, 'CSO'],
            ['KRI-20', 'Security Champions coverage', '%', 60, 40, 'higher_is_better', 70, 'CSO'],
        ];

        foreach ($rows as $row) {
            $this->createKri($row);
        }
    }
}
