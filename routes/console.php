<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('grc:send-alerts')->dailyAt('07:00');
Schedule::command('grc:sync-policy-drive-documents')->hourly();
Schedule::command('grc:sync-entra-identity-protection')->hourly();
Schedule::command('grc:sync-google-workspace-alerts')->hourly();
Schedule::command('grc:sync-aws-security-hub-findings')->hourly();
Schedule::command('grc:sync-aws-compliance-evidence')->dailyAt('06:00');
