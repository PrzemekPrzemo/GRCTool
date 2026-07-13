<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Wiąże wartość z tokenu SSO (App Role "Value" lub Entra Group Object ID) z rolą
 * Spatie w GRCTool. Odczytywane przez EntraRoleMappingService przy każdym logowaniu
 * przez Microsoft — patrz app/Services/Security/EntraRoleMappingService.php.
 */
class SsoRoleMapping extends Model
{
    protected $fillable = [
        'provider', 'entra_type', 'entra_value', 'label', 'system_role',
    ];
}
