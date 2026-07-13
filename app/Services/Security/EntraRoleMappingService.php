<?php

namespace App\Services\Security;

use App\Models\AppSetting;
use App\Models\SsoRoleMapping;

/**
 * Odwzorowuje App Roles / grupy Entra ID (odczytane z claimów `roles`/`groups`
 * w id_token) na role Spatie w GRCTool, wg konfiguracji w tabeli sso_role_mappings
 * (Admin → Entra ID → Mapowanie ról).
 *
 * id_token pochodzi z bezpośredniego wywołania server-to-server do endpointu
 * tokenów Microsoft (POST .../oauth2/v2.0/token, uwierzytelnionego naszym
 * client_secret) — nie od klienta/przeglądarki — więc dekodujemy payload JWT
 * bez weryfikacji podpisu, tak jak reszta integracji w tym serwisie ufa
 * odpowiedziom Graph API otrzymanym tą samą drogą.
 */
class EntraRoleMappingService
{
    public function isEnabled(): bool
    {
        return AppSetting::get('azure_role_sync_enabled', '0') === '1';
    }

    /**
     * @return array{roles: array<int, string>, groups: array<int, string>}
     */
    public function decodeIdTokenClaims(?string $idToken): array
    {
        if (! $idToken) {
            return ['roles' => [], 'groups' => []];
        }

        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            return ['roles' => [], 'groups' => []];
        }

        $payload = base64_decode(strtr($parts[1], '-_', '+/'), true);
        $claims = $payload !== false ? json_decode($payload, true) : null;

        if (! is_array($claims)) {
            return ['roles' => [], 'groups' => []];
        }

        return [
            'roles' => array_values(array_filter((array) ($claims['roles'] ?? []), 'is_string')),
            'groups' => array_values(array_filter((array) ($claims['groups'] ?? []), 'is_string')),
        ];
    }

    /**
     * @param  array{roles: array<int, string>, groups: array<int, string>}  $claims
     * @return array<int, string> unikalne nazwy ról Spatie do nadania; [] gdy brak dopasowania
     */
    public function resolveSystemRoles(array $claims): array
    {
        $roles = SsoRoleMapping::query()
            ->where('provider', 'azure')
            ->where(function ($q) use ($claims) {
                $q->where(function ($q2) use ($claims) {
                    $q2->where('entra_type', 'app_role')->whereIn('entra_value', $claims['roles'] ?? []);
                })->orWhere(function ($q2) use ($claims) {
                    $q2->where('entra_type', 'group')->whereIn('entra_value', $claims['groups'] ?? []);
                });
            })
            ->pluck('system_role')
            ->unique()
            ->values()
            ->all();

        return $roles;
    }
}
