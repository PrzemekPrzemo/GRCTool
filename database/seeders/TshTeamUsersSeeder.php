<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Realne konta dla ról funkcyjnych, którymi rejestry TSH-SEC-REG-* opisują
 * właścicieli ryzyk/aktywów/wskaźników (kolumny "Owner"/"Custodian" w
 * REG-001/002/011 i "Owner" w REG-013 KRI). Role bez jednoznacznego,
 * pojedynczego właściciela (np. "Developer", "All Staff") celowo nie mają
 * tu konta — TshSecRegistersSeeder/TshSecIndicatorsSeeder pozostawiają
 * takie powiązania jako null zamiast fabrykować przypisanie.
 */
class TshTeamUsersSeeder extends Seeder
{
    public function run(): void
    {
        $bu = BusinessUnit::firstOrCreate(
            ['code' => 'SEC'],
            ['name' => 'Cybersecurity', 'description' => 'Zespół Security / GRC.', 'is_active' => true],
        );

        $accounts = [
            ['it.lead@grc.local', 'IT Lead', 'security_engineer'],
            ['tech.lead@grc.local', 'Tech Lead', 'control_owner'],
            ['hr.lead@grc.local', 'HR Lead', 'asset_owner'],
            ['pm@grc.local', 'Project Manager', 'risk_owner'],
            ['ceo@grc.local', 'CEO', 'board_viewer'],
        ];

        foreach ($accounts as [$email, $name, $role]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('ChangeMe!2026'),
                    'business_unit_id' => $bu->id,
                    'is_active' => true,
                    'locale' => 'pl',
                    'email_verified_at' => now(),
                ],
            );
            $user->syncRoles([$role]);
        }
    }
}
