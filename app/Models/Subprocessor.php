<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subprocessor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'third_party_id', 'name', 'service_provided', 'data_categories',
        'country_of_processing', 'legal_basis', 'transfer_mechanism',
        'dpa_url', 'certifications', 'client_scopes', 'tier',
        'last_assessment_date', 'notification_history', 'public_listing',
    ];

    protected $casts = [
        'data_categories' => 'array',
        'certifications' => 'array',
        'client_scopes' => 'array',
        'notification_history' => 'array',
        'last_assessment_date' => 'date',
        'public_listing' => 'boolean',
    ];

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParty::class);
    }
}
