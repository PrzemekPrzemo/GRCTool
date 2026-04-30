<?php

namespace App\Console\Commands;

use App\Models\Framework;
use App\Models\FrameworkControl;
use App\Models\Indicator;
use App\Models\ReportTemplate;
use App\Models\ScenarioTemplate;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SmokeCountsCommand extends Command
{
    protected $signature = 'grc:smoke-counts';

    protected $description = 'Verify seed counts post-migration (used in CI smoke test).';

    public function handle(): int
    {
        $expectations = [
            'Frameworks' => [Framework::count(), 10],
            'FrameworkControls' => [FrameworkControl::count(), 159],
            'ScenarioTemplates' => [ScenarioTemplate::count(), 42],
            'Indicators' => [Indicator::count(), 33],
            'ReportTemplates' => [ReportTemplate::count(), 3],
            'Roles' => [Role::count(), 14],
            'Permissions' => [Permission::count(), 108],
        ];

        $failed = false;
        foreach ($expectations as $name => [$actual, $expected]) {
            if ($actual < $expected) {
                $this->error("FAIL: $name expected >=$expected, got $actual");
                $failed = true;
            } else {
                $this->info("OK: $name = $actual (>=$expected)");
            }
        }

        return $failed ? Command::FAILURE : Command::SUCCESS;
    }
}
