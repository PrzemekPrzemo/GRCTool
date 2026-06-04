<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nis2Assessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'organization_name', 'conducted_by', 'assessment_date',
        'employee_count', 'annual_turnover_eur', 'balance_sheet_eur',
        'sector', 'subsector', 'is_public_administration', 'is_critical_infrastructure',
        'provides_dns', 'provides_tld', 'provides_ixp', 'provides_cloud',
        'provides_datacentre', 'provides_cdn', 'provides_trust_services',
        'provides_msp_mssp', 'provides_ecomms',
        'entity_size', 'result', 'annex_classification', 'justification',
        'status', 'notes', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'assessment_date'           => 'date',
        'reviewed_at'               => 'datetime',
        'annual_turnover_eur'       => 'decimal:2',
        'balance_sheet_eur'         => 'decimal:2',
        'is_public_administration'  => 'boolean',
        'is_critical_infrastructure'=> 'boolean',
        'provides_dns'              => 'boolean',
        'provides_tld'              => 'boolean',
        'provides_ixp'              => 'boolean',
        'provides_cloud'            => 'boolean',
        'provides_datacentre'       => 'boolean',
        'provides_cdn'              => 'boolean',
        'provides_trust_services'   => 'boolean',
        'provides_msp_mssp'         => 'boolean',
        'provides_ecomms'           => 'boolean',
    ];

    public const ANNEX_I_SECTORS = [
        'energy'                 => 'Energia (elektryczność, ropa, gaz, wodór)',
        'transport'              => 'Transport (lotniczy, kolejowy, wodny, drogowy)',
        'banking'                => 'Bankowość',
        'financial_market'       => 'Infrastruktury rynków finansowych',
        'health'                 => 'Ochrona zdrowia',
        'drinking_water'         => 'Woda pitna',
        'waste_water'            => 'Ścieki',
        'digital_infrastructure' => 'Infrastruktura cyfrowa (IXP, DNS, TLD, chmura, DC, CDN, zaufanie, łączność)',
        'ict_service_management' => 'Zarządzanie usługami ICT (MSP/MSSP)',
        'public_administration'  => 'Administracja publiczna',
        'space'                  => 'Przestrzeń kosmiczna',
    ];

    public const ANNEX_II_SECTORS = [
        'postal'           => 'Pocztowe i kurierskie',
        'waste_management' => 'Gospodarka odpadami',
        'chemicals'        => 'Produkcja i dystrybucja chemikaliów',
        'food'             => 'Produkcja i dystrybucja żywności',
        'manufacturing'    => 'Produkcja (urządzenia med., elektronika, maszyny, pojazdy)',
        'digital_providers'=> 'Dostawcy cyfrowi (marketplace, wyszukiwarki, social media)',
        'research'         => 'Organizacje badawcze',
    ];

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }

    public function resultLabel(): string
    {
        return match($this->result) {
            'not_subject'      => 'Nie podlega pod NIS2',
            'important_entity' => 'Podmiot Ważny (Annex II)',
            'essential_entity' => 'Podmiot Kluczowy (Annex I)',
            default            => '—',
        };
    }

    public function annexLabel(): string
    {
        return match($this->annex_classification) {
            'annex_i'       => 'Załącznik I',
            'annex_ii'      => 'Załącznik II',
            'not_applicable'=> 'Nie dotyczy',
            default         => '—',
        };
    }

    public function sectorLabel(): string
    {
        return self::ANNEX_I_SECTORS[$this->sector]
            ?? self::ANNEX_II_SECTORS[$this->sector]
            ?? ($this->sector === 'other' ? 'Inny' : ($this->sector ?? '—'));
    }
}
