<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $modules = [
            'asset', 'risk', 'risk_acceptance', 'control', 'indicator', 'vulnerability',
            'incident', 'nis2', 'audit_engagement', 'finding', 'cap', 'report', 'evidence',
            'policy', 'third_party', 'subprocessor', 'questionnaire', 'client',
            'business_unit', 'project', 'user', 'role', 'audit_log', 'framework',
            'rcp', 'gdpr_breach', 'dpia', 'dsar',
            'training', 'exception', 'certificate', 'crypto_key', 'bcp',
            'sdlc', 'access_review',
        ];

        $actions = ['view', 'create', 'update', 'delete'];
        $extraPermissions = [
            'risk.accept', 'risk.review', 'control.test', 'control.review',
            'report.generate', 'report.distribute', 'report.revoke',
            'audit_engagement.lead', 'evidence.request',
            'user.manage_roles', 'user.deactivate',
            'system.admin', 'system.audit_view',
            'questionnaire.send', 'questionnaire.auto_fill',
            'mfa.bypass', // SoD: dla nieprzewidzianych przypadków, log alert
        ];

        DB::transaction(function () use ($modules, $actions, $extraPermissions): void {
            foreach ($modules as $module) {
                foreach ($actions as $action) {
                    Permission::firstOrCreate(['name' => "$module.$action"]);
                }
            }
            foreach ($extraPermissions as $perm) {
                Permission::firstOrCreate(['name' => $perm]);
            }

            $roleDefinitions = [
                'admin' => [
                    'description' => 'System administrator. RBAC, integracje, konfiguracja. SoD: nie edytuje danych merytorycznych.',
                    'permissions' => [
                        'user.*', 'role.*', 'business_unit.*', 'project.*', 'client.*',
                        'system.admin', 'audit_log.view', 'framework.*',
                    ],
                ],
                'ciso' => [
                    'description' => 'CISO / Head of Security. Pełen odczyt + akceptacja ryzyk + zatwierdzanie raportów.',
                    'permissions' => [
                        '*.view', 'risk.*', 'risk_acceptance.*', 'control.*', 'indicator.*',
                        'vulnerability.*', 'incident.*', 'nis2.*', 'audit_engagement.*', 'finding.*',
                        'cap.*', 'report.*', 'evidence.*', 'policy.*', 'third_party.*',
                        'subprocessor.*', 'questionnaire.*', 'asset.*',
                        'rcp.*', 'gdpr_breach.*', 'dpia.*', 'dsar.*',
                        'training.*', 'exception.*', 'certificate.*', 'crypto_key.*', 'bcp.*',
                        'sdlc.*', 'access_review.*',
                    ],
                ],
                'security_engineer' => [
                    'description' => 'Operacyjny zespół security. CRUD vuln/incidents/controls; brak akceptacji ryzyk.',
                    'permissions' => [
                        'asset.*', 'vulnerability.*', 'incident.*', 'nis2.view', 'nis2.create', 'nis2.update',
                        'control.view', 'control.update', 'control.test', 'evidence.*',
                        'indicator.view', 'indicator.update', 'finding.view', 'finding.update',
                        'risk.view', 'audit_log.view',
                        'training.view', 'exception.view', 'exception.create',
                        'certificate.*', 'crypto_key.*',
                        'sdlc.*', 'access_review.view', 'access_review.create', 'access_review.update',
                    ],
                ],
                'risk_owner' => [
                    'description' => 'Właściciel ryzyka (np. CTO, Head of Eng.). Edycja swoich ryzyk + zatw. RTP.',
                    'permissions' => [
                        'risk.view', 'risk.update', 'risk.create', 'asset.view', 'control.view',
                        'evidence.view', 'evidence.create', 'finding.view', 'incident.view', 'nis2.view',
                        'rcp.view', 'gdpr_breach.view', 'dpia.view',
                    ],
                ],
                'control_owner' => [
                    'description' => 'Właściciel kontroli. Edycja swoich kontroli, dodawanie evidence.',
                    'permissions' => [
                        'control.view', 'control.update', 'control.test', 'evidence.*',
                        'finding.view', 'risk.view', 'asset.view',
                    ],
                ],
                'audit_lead' => [
                    'description' => 'Lider audytu wewnętrznego. Tworzy engagements, zarządza findings, koordynuje CAP.',
                    'permissions' => [
                        'audit_engagement.*', 'evidence.request', 'evidence.*', 'finding.*',
                        'cap.*', 'control.view', 'risk.view', 'asset.view', 'report.view',
                        'report.generate',
                        'training.view', 'exception.view', 'bcp.view',
                        'access_review.*', 'sdlc.view',
                    ],
                ],
                'external_auditor' => [
                    'description' => 'Audytor zewnętrzny. Read-only, scope ograniczony do swojego engagementu.',
                    'permissions' => [
                        'audit_engagement.view', 'evidence.view', 'finding.view',
                        'control.view', 'risk.view', 'asset.view', 'report.view',
                    ],
                ],
                'board_viewer' => [
                    'description' => 'Zarząd / Board. Tylko Executive Dashboard + raporty.',
                    'permissions' => [
                        'report.view', 'indicator.view', 'risk.view', 'audit_engagement.view',
                        'training.view', 'exception.view', 'certificate.view', 'bcp.view',
                    ],
                ],
                'asset_owner' => [
                    'description' => 'Właściciel aktywa. Edycja swoich aktywów + odpowiedzi na ankiety.',
                    'permissions' => [
                        'asset.view', 'asset.update', 'evidence.view', 'evidence.create',
                        'questionnaire.view', 'questionnaire.update',
                    ],
                ],
                'vendor_manager' => [
                    'description' => 'Manager dostawców. Moduł TPRM.',
                    'permissions' => [
                        'third_party.*', 'subprocessor.*', 'evidence.view', 'evidence.create',
                        'risk.view', 'questionnaire.view', 'questionnaire.send',
                    ],
                ],
                'compliance_officer' => [
                    'description' => 'Legal / Compliance / DPO. Polityki, DPA, subprocessor mgmt, regulatory reports, RODO.',
                    'permissions' => [
                        'policy.*', 'subprocessor.*', 'third_party.view', 'third_party.update',
                        'report.*', 'finding.view', 'audit_engagement.view',
                        'evidence.view', 'evidence.create',
                        'incident.view', 'nis2.*',
                        'rcp.*', 'gdpr_breach.*', 'dpia.*', 'dsar.*',
                        'training.*', 'exception.*', 'certificate.*', 'crypto_key.*', 'bcp.*',
                        'access_review.*', 'sdlc.view',
                    ],
                ],
                'sales' => [
                    'description' => 'Customer Success / Sales. Read-only do AnswerLibrary, inicjacja questionnaire.',
                    'permissions' => [
                        'questionnaire.*', 'client.view',
                    ],
                ],
                'client_contact' => [
                    'description' => 'Kontakt klienta (NDA-gated). Read-only do swojego Trust Portal.',
                    'permissions' => [
                        'report.view', 'subprocessor.view',
                    ],
                ],
                'auditor_sysops' => [
                    'description' => 'Auditor systemu (sec-ops). Pełen audit log access, brak edycji.',
                    'permissions' => [
                        'audit_log.view', 'system.audit_view',
                        '*.view', // read-only globalny
                    ],
                ],
            ];

            foreach ($roleDefinitions as $name => $def) {
                $role = Role::firstOrCreate(['name' => $name]);
                $perms = $this->expandPermissions($def['permissions']);
                $role->syncPermissions($perms);
            }
        });
    }

    private function expandPermissions(array $patterns): array
    {
        $all = Permission::pluck('name')->all();
        $resolved = [];
        foreach ($patterns as $p) {
            if (str_contains($p, '*')) {
                $regex = '/^'.str_replace('\*', '.*', preg_quote($p, '/')).'$/';
                foreach ($all as $name) {
                    if (preg_match($regex, $name)) {
                        $resolved[$name] = true;
                    }
                }
            } else {
                $resolved[$p] = true;
            }
        }

        return array_keys($resolved);
    }
}
