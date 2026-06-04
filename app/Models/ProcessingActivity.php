<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessingActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'processing_activities';

    protected $fillable = [
        'code', 'name', 'description', 'purpose', 'legal_basis', 'legal_basis_detail',
        'data_categories', 'special_categories', 'data_subjects',
        'retention_period', 'retention_basis',
        'controller_id', 'processor_id', 'system_name',
        'security_measures', 'cross_border_transfer', 'transfer_countries',
        'transfer_mechanism', 'dpia_required', 'status', 'notes',
    ];

    protected $casts = [
        'data_categories' => 'array',
        'special_categories' => 'array',
        'data_subjects' => 'array',
        'security_measures' => 'array',
        'transfer_countries' => 'array',
        'cross_border_transfer' => 'boolean',
        'dpia_required' => 'boolean',
    ];

    const LEGAL_BASES = [
        'consent'               => 'Zgoda (Art. 6 ust. 1 lit. a)',
        'contract'              => 'Umowa (Art. 6 ust. 1 lit. b)',
        'legal_obligation'      => 'Obowiązek prawny (Art. 6 ust. 1 lit. c)',
        'vital_interests'       => 'Żywotne interesy (Art. 6 ust. 1 lit. d)',
        'public_task'           => 'Zadanie publiczne (Art. 6 ust. 1 lit. e)',
        'legitimate_interests'  => 'Uzasadniony interes (Art. 6 ust. 1 lit. f)',
    ];

    const DATA_CATEGORIES = [
        'identification'  => 'Dane identyfikacyjne',
        'contact'         => 'Dane kontaktowe',
        'financial'       => 'Dane finansowe',
        'employment'      => 'Dane pracownicze',
        'location'        => 'Dane lokalizacyjne',
        'behavioral'      => 'Dane behawioralne',
        'technical'       => 'Dane techniczne (IP, cookies)',
        'communications'  => 'Treść komunikacji',
    ];

    const SPECIAL_CATEGORIES = [
        'racial_ethnic'       => 'Pochodzenie rasowe / etniczne',
        'political_opinions'  => 'Poglądy polityczne',
        'religious_beliefs'   => 'Przekonania religijne',
        'trade_union'         => 'Przynależność do związków zawodowych',
        'genetic'             => 'Dane genetyczne',
        'biometric'           => 'Dane biometryczne',
        'health'              => 'Dane dotyczące zdrowia',
        'sex_life'            => 'Życie seksualne / orientacja seksualna',
        'criminal'            => 'Dane karne (Art. 10)',
    ];

    const DATA_SUBJECTS = [
        'employees'     => 'Pracownicy',
        'customers'     => 'Klienci',
        'prospects'     => 'Potencjalni klienci',
        'suppliers'     => 'Dostawcy',
        'minors'        => 'Osoby niepełnoletnie',
        'patients'      => 'Pacjenci',
        'users'         => 'Użytkownicy systemów',
        'public'        => 'Ogół społeczeństwa',
    ];

    public function controller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'controller_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    public function thirdParties(): BelongsToMany
    {
        return $this->belongsToMany(ThirdParty::class, 'processing_activity_third_party')
            ->withPivot('role', 'notes')
            ->withTimestamps();
    }

    public function dpias(): HasMany
    {
        return $this->hasMany(Dpia::class);
    }

    public function legalBasisLabel(): string
    {
        return self::LEGAL_BASES[$this->legal_basis] ?? ($this->legal_basis ?? '—');
    }

    public function hasSpecialCategories(): bool
    {
        return !empty($this->special_categories);
    }
}
