<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $bu = BusinessUnit::firstOrCreate(
            ['code' => 'SEC'],
            ['name' => 'Cybersecurity', 'description' => 'Zespół Security / GRC.', 'is_active' => true],
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@grc.local'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('ChangeMe!2026'),
                'business_unit_id' => $bu->id,
                'is_active' => true,
                'locale' => 'pl',
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles(['admin']);

        $ciso = User::firstOrCreate(
            ['email' => 'ciso@grc.local'],
            [
                'name' => 'CISO / Head of Security',
                'password' => Hash::make('ChangeMe!2026'),
                'business_unit_id' => $bu->id,
                'is_active' => true,
                'locale' => 'pl',
                'email_verified_at' => now(),
            ],
        );
        $ciso->syncRoles(['ciso']);
    }
}
