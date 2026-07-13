<?php

namespace App\Services;

use App\Models\CapAction;
use App\Models\CertificateInventory;
use App\Models\DsarRequest;
use App\Models\GdprBreach;
use App\Models\Incident;
use App\Models\QuestionnaireQuestion;
use App\Models\User;
use App\Models\Vulnerability;

/**
 * Zbiorczy, żywy widok tego, co w SendGrcAlerts trafia tylko do maila raz
 * dziennie — tu liczony na bieżąco i pokazywany w dzwonku w headerze,
 * przefiltrowany do tego, co dana rola faktycznie może zobaczyć/obsłużyć.
 * Celowo pomija ciężkie/niepewne obliczenia (np. compliance_calendar_tasks,
 * gdzie "termin" to wolny tekst miesięcy, nie data) — tylko sygnały z realnym
 * polem daty/terminu.
 */
class SystemAlertsService
{
    /** @return array<int, array{key: string, label: string, severity: string, count: int, viewAllUrl: string, items: array<int, array{title: string, detail: string, url: string}>}> */
    public function forUser(User $user): array
    {
        $groups = [];

        if ($user->can('certificate.view')) {
            $groups[] = $this->certificates();
        }
        if ($user->can('vulnerability.view')) {
            $groups[] = $this->vulnerabilities();
        }
        if ($user->can('cap.update')) {
            $groups[] = $this->capActions();
        }
        if ($user->can('dsar.view')) {
            $groups[] = $this->dsarRequests();
        }
        if ($user->can('gdpr_breach.view')) {
            $groups[] = $this->gdprBreaches();
        }
        if ($user->can('incident.view')) {
            $groups[] = $this->nis2EarlyWarnings();
        }
        if ($user->can('rfp.update')) {
            $groups[] = $this->rfpFlaggedQuestions();
        }

        return array_values(array_filter($groups, fn (array $g) => $g['count'] > 0));
    }

    private function certificates(): array
    {
        $q = CertificateInventory::query()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays(30))
            ->orderBy('expires_at');

        return [
            'key' => 'certificates',
            'label' => 'Certyfikaty wygasające ≤30 dni',
            'severity' => 'amber',
            'count' => $q->count(),
            'viewAllUrl' => route('certificates.index'),
            'items' => $q->limit(5)->get()->map(fn (CertificateInventory $c) => [
                'title' => $c->common_name,
                'detail' => 'wygasa '.$c->expires_at->format('Y-m-d'),
                'url' => route('certificates.show', $c),
            ])->all(),
        ];
    }

    private function vulnerabilities(): array
    {
        $q = Vulnerability::query()
            ->where('status', '!=', 'Fixed')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now()->addDays(7))
            ->orderBy('due_date');

        return [
            'key' => 'vulnerabilities',
            'label' => 'Podatności — termin SLA ≤7 dni',
            'severity' => 'red',
            'count' => $q->count(),
            'viewAllUrl' => route('vulnerabilities.sla'),
            'items' => $q->limit(5)->get()->map(fn (Vulnerability $v) => [
                'title' => $v->title,
                'detail' => $v->severity.' · termin '.$v->due_date->format('Y-m-d'),
                'url' => route('vulnerabilities.show', $v),
            ])->all(),
        ];
    }

    private function capActions(): array
    {
        $q = CapAction::query()
            ->where('status', '!=', 'Done')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now()->addDays(7))
            ->with('cap')
            ->orderBy('due_date');

        return [
            'key' => 'cap_actions',
            'label' => 'Akcje CAP z terminem ≤7 dni',
            'severity' => 'amber',
            'count' => $q->count(),
            'viewAllUrl' => route('cap.index'),
            'items' => $q->limit(5)->get()->map(fn (CapAction $a) => [
                'title' => $a->title,
                'detail' => ($a->cap?->code ?? 'CAP').' · termin '.$a->due_date->format('Y-m-d'),
                'url' => url("/cap/{$a->cap_id}"),
            ])->all(),
        ];
    }

    private function dsarRequests(): array
    {
        $q = DsarRequest::query()
            ->whereNotIn('status', ['completed', 'rejected', 'withdrawn'])
            ->where('created_at', '<=', now()->subDays(20))
            ->orderBy('created_at');

        return [
            'key' => 'dsar',
            'label' => 'Wnioski DSAR blisko terminu 30 dni',
            'severity' => 'red',
            'count' => $q->count(),
            'viewAllUrl' => route('dsar.index'),
            'items' => $q->limit(5)->get()->map(fn (DsarRequest $d) => [
                'title' => $d->code.' — '.$d->requester_name,
                'detail' => (30 - (int) now()->diffInDays($d->created_at)).' dni do ustawowego terminu',
                'url' => route('dsar.show', $d),
            ])->all(),
        ];
    }

    private function gdprBreaches(): array
    {
        $q = GdprBreach::query()
            ->where('notification_required', true)
            ->whereNull('uodo_notified_at')
            ->whereNotNull('uodo_notification_deadline')
            ->where('uodo_notification_deadline', '>', now())
            ->where('uodo_notification_deadline', '<=', now()->addHours(24))
            ->orderBy('uodo_notification_deadline');

        return [
            'key' => 'gdpr_breaches',
            'label' => 'Naruszenia RODO — termin zgłoszenia do UODO ≤24h',
            'severity' => 'red',
            'count' => $q->count(),
            'viewAllUrl' => route('gdpr-breaches.index'),
            'items' => $q->limit(5)->get()->map(fn (GdprBreach $b) => [
                'title' => $b->code ?? "Naruszenie #{$b->id}",
                'detail' => round((float) $b->hoursUntilDeadline(), 1).'h do terminu 72h',
                'url' => route('gdpr-breaches.show', $b),
            ])->all(),
        ];
    }

    private function nis2EarlyWarnings(): array
    {
        $q = Incident::query()
            ->where('is_breach', true)
            ->where('enisa_is_significant', true)
            ->whereNotNull('enisa_early_warning_deadline')
            ->where('enisa_early_warning_deadline', '>', now())
            ->where('enisa_early_warning_deadline', '<=', now()->addHours(2))
            ->orderBy('enisa_early_warning_deadline');

        return [
            'key' => 'nis2_early_warning',
            'label' => 'NIS2 — zgłoszenie wczesnego ostrzeżenia ≤2h',
            'severity' => 'red',
            'count' => $q->count(),
            'viewAllUrl' => route('incidents.index'),
            'items' => $q->limit(5)->get()->map(fn (Incident $i) => [
                'title' => $i->code.' — '.$i->title,
                'detail' => 'termin '.$i->enisa_early_warning_deadline->format('Y-m-d H:i'),
                'url' => route('incidents.show', $i),
            ])->all(),
        ];
    }

    private function rfpFlaggedQuestions(): array
    {
        $q = QuestionnaireQuestion::query()
            ->where('status', 'Needs-Info')
            ->with('questionnaire')
            ->orderByDesc('flagged_at');

        return [
            'key' => 'rfp_flagged',
            'label' => 'Pytania RFP zgłoszone do CSO',
            'severity' => 'amber',
            'count' => $q->count(),
            'viewAllUrl' => route('questionnaires.index'),
            'items' => $q->limit(5)->get()->filter(fn (QuestionnaireQuestion $qq) => $qq->questionnaire !== null)->map(fn (QuestionnaireQuestion $qq) => [
                'title' => \Illuminate\Support\Str::limit($qq->original_text, 60),
                'detail' => $qq->questionnaire->code.($qq->flagged_at ? ' · zgłoszone '.$qq->flagged_at->format('Y-m-d') : ''),
                'url' => route('questionnaires.show', $qq->questionnaire),
            ])->values()->all(),
        ];
    }
}
