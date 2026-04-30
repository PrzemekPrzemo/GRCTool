<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            FrameworksSeeder::class,
            ScenarioTemplatesSeeder::class,
            IndicatorsSeeder::class,
            ReportTemplatesSeeder::class,
            // Trust + TPRM + Questionnaires
            MinimumControlRequirementsSeeder::class,
            QuestionnaireTemplatesSeeder::class,
            AnswerLibrarySeeder::class,
            // Admin user last (depends on roles)
            AdminUserSeeder::class,
        ]);
    }
}
