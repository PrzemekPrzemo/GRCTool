<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThirdParty extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'third_parties';

    protected $fillable = [
        'code', 'name', 'service_provided', 'data_categories',
        'country_of_processing', 'legal_basis', 'transfer_mechanism',
        'dpa_url', 'certifications', 'tier',
        'last_assessment_date', 'next_assessment_due',
        'security_rating', 'rating_history', 'is_active',
    ];

    protected $casts = [
        'data_categories' => 'array',
        'certifications' => 'array',
        'rating_history' => 'array',
        'last_assessment_date' => 'date',
        'next_assessment_due' => 'date',
        'is_active' => 'boolean',
    ];
}
