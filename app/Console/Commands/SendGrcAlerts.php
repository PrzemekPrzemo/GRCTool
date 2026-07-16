<?php

namespace App\Console\Commands;

use App\Models\CapAction;
use App\Models\CertificateInventory;
use App\Models\DsarRequest;
use App\Models\Incident;
use App\Models\User;
use App\Models\Vulnerability;
use App\Notifications\GrcAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendGrcAlerts extends Command
{
    protected $signature = 'grc:send-alerts';

    protected $description = 'Send GRC alert notifications for expiring certificates, vulnerabilities, CAP actions, DSAR requests, and ENISA incidents.';

    public function handle(): int
    {
        $this->sendCertificateAlerts();
        $this->sendVulnerabilityAlerts();
        $this->sendCapActionAlerts();
        $this->sendDsarAlerts();
        $this->sendEnisaIncidentAlerts();

        $this->info('GRC alerts completed.');

        return self::SUCCESS;
    }

    /**
     * Certificates expiring within 30 days — notify active CISO/admin users.
     */
    private function sendCertificateAlerts(): void
    {
        $certs = CertificateInventory::query()
            ->where('expires_at', '<=', now()->addDays(30))
            ->where('expires_at', '>', now())
            ->where('status', 'active')
            ->get();

        if ($certs->isEmpty()) {
            return;
        }

        $admins = User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['ciso', 'admin']))
            ->get();

        foreach ($certs as $cert) {
            $daysLeft = $cert->daysUntilExpiry();

            foreach ($admins as $admin) {
                Notification::route('mail', $admin->email)
                    ->notify(new GrcAlertNotification(
                        subject: "[GRC Alert] Certificate expiring in {$daysLeft} day(s): {$cert->common_name}",
                        bodyLines: [
                            "Certificate **{$cert->common_name}** ({$cert->code}) will expire in **{$daysLeft} day(s)** on {$cert->expires_at->toDateString()}.",
                            "Type: {$cert->cert_type} | Environment: {$cert->environment}",
                            'Please renew or replace the certificate before it expires.',
                        ],
                        actionUrl: url("/certificates/{$cert->id}"),
                        actionLabel: 'View Certificate',
                    ));

                $this->info("Sent: Certificate alert [{$cert->code}] → {$admin->email}");
            }
        }
    }

    /**
     * Vulnerabilities with due_date within 7 days (not yet Fixed) — notify owner.
     */
    private function sendVulnerabilityAlerts(): void
    {
        $vulns = Vulnerability::query()
            ->where('due_date', '<=', now()->addDays(7))
            ->where('due_date', '>', now())
            ->where('status', '!=', 'Fixed')
            ->whereNotNull('owner_id')
            ->with('owner')
            ->get();

        foreach ($vulns as $vuln) {
            $owner = $vuln->owner;

            if (! $owner) {
                continue;
            }

            $daysLeft = (int) now()->diffInDays($vuln->due_date, false);
            $vulnRef = $vuln->code ?? $vuln->cve_id ?? "ID #{$vuln->id}";

            Notification::route('mail', $owner->email)
                ->notify(new GrcAlertNotification(
                    subject: "[GRC Alert] Vulnerability SLA due in {$daysLeft} day(s): {$vuln->title}",
                    bodyLines: [
                        "Vulnerability **{$vuln->title}** ({$vulnRef}) is due in **{$daysLeft} day(s)** on {$vuln->due_date->toDateString()}.",
                        "Severity: {$vuln->severity} | Status: {$vuln->status}",
                        'Please remediate or update the status of this vulnerability before the SLA deadline.',
                    ],
                    actionUrl: url("/vulnerabilities/{$vuln->id}"),
                    actionLabel: 'View Vulnerability',
                ));

            $this->info("Sent: Vulnerability alert [{$vulnRef}] → {$owner->email}");
        }
    }

    /**
     * CAP actions with due_date within 7 days (not Done) — notify action owner or CAP approver.
     */
    private function sendCapActionAlerts(): void
    {
        $actions = CapAction::query()
            ->where('due_date', '<=', now()->addDays(7))
            ->where('due_date', '>=', now())
            ->where('status', '!=', 'Done')
            ->with(['owner', 'cap.approver'])
            ->get();

        foreach ($actions as $action) {
            // Prefer action's own owner, fall back to the CAP approver
            $recipient = $action->owner ?? $action->cap?->approver;

            if (! $recipient) {
                continue;
            }

            $daysLeft = (int) now()->diffInDays($action->due_date, false);
            $capCode = $action->cap?->code ?? "CAP #{$action->cap_id}";

            Notification::route('mail', $recipient->email)
                ->notify(new GrcAlertNotification(
                    subject: "[GRC Alert] CAP Action due in {$daysLeft} day(s): {$action->title}",
                    bodyLines: [
                        "CAP Action **{$action->title}** (part of {$capCode}) is due in **{$daysLeft} day(s)** on {$action->due_date->toDateString()}.",
                        "Status: {$action->status} | Progress: {$action->progress_percent}%",
                        'Please complete or update this corrective action before the deadline.',
                    ],
                    actionUrl: url("/cap/{$action->cap_id}"),
                    actionLabel: 'View CAP Action',
                ));

            $this->info("Sent: CAP Action alert [#{$action->id}] → {$recipient->email}");
        }
    }

    /**
     * DSAR requests older than 25 days still not Completed — urgent GDPR deadline alert to CISO/admin.
     */
    private function sendDsarAlerts(): void
    {
        $dsars = DsarRequest::query()
            ->where('created_at', '<=', now()->subDays(25))
            ->whereNotIn('status', ['completed', 'rejected', 'withdrawn'])
            ->get();

        if ($dsars->isEmpty()) {
            return;
        }

        $admins = User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['ciso', 'admin']))
            ->get();

        foreach ($dsars as $dsar) {
            $daysPending = (int) now()->diffInDays($dsar->created_at);
            $daysLeft = 30 - $daysPending;

            foreach ($admins as $admin) {
                Notification::route('mail', $admin->email)
                    ->notify(new GrcAlertNotification(
                        subject: "[GRC URGENT] DSAR request {$dsar->code} — GDPR 30-day deadline in {$daysLeft} day(s)",
                        bodyLines: [
                            "DSAR request **{$dsar->code}** from **{$dsar->requester_name}** has been pending for **{$daysPending} day(s)**.",
                            "Request type: {$dsar->requestTypeLabel()} | Status: {$dsar->status}",
                            'Under GDPR Art. 12, a response must be provided within 30 days of receipt.',
                            "Deadline: approximately {$daysLeft} day(s) remaining. Immediate action is required.",
                        ],
                        actionUrl: url("/dsar/{$dsar->id}"),
                        actionLabel: 'View DSAR Request',
                    ));

                $this->info("Sent: DSAR alert [{$dsar->code}] → {$admin->email}");
            }
        }
    }

    /**
     * Incidents with is_breach + enisa_is_significant + early warning deadline within 2 hours — notify CISO/admin.
     */
    private function sendEnisaIncidentAlerts(): void
    {
        $incidents = Incident::query()
            ->where('is_breach', true)
            ->where('enisa_is_significant', true)
            ->whereNotNull('enisa_early_warning_deadline')
            ->where('enisa_early_warning_deadline', '>', now())
            ->where('enisa_early_warning_deadline', '<=', now()->addHours(2))
            ->get();

        if ($incidents->isEmpty()) {
            return;
        }

        $admins = User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['ciso', 'admin']))
            ->get();

        foreach ($incidents as $incident) {
            $minutesLeft = (int) now()->diffInMinutes($incident->enisa_early_warning_deadline, false);
            $deadlineStr = $incident->enisa_early_warning_deadline->format('Y-m-d H:i');

            foreach ($admins as $admin) {
                Notification::route('mail', $admin->email)
                    ->notify(new GrcAlertNotification(
                        subject: "[GRC CRITICAL] NIS2 Early Warning deadline in {$minutesLeft} min: {$incident->code}",
                        bodyLines: [
                            "Incident **{$incident->title}** ({$incident->code}) is a **significant NIS2 breach** requiring an early warning notification to the competent authority.",
                            "ENISA Severity: {$incident->enisa_severity_level} (score: {$incident->enisa_severity_score})",
                            "Early warning deadline: **{$deadlineStr}** — approximately **{$minutesLeft} minute(s)** remaining.",
                            'Per NIS2 Art. 23, an early warning must be submitted within 24 hours of detection.',
                            'Immediate action required: notify your national CSIRT / competent authority.',
                        ],
                        actionUrl: url("/incidents/{$incident->id}"),
                        actionLabel: 'View Incident',
                    ));

                $this->info("Sent: ENISA incident alert [{$incident->code}] → {$admin->email}");
            }
        }
    }
}
