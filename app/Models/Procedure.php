<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procedure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'title', 'description', 'policy_ref', 'related_model',
        'owner_role', 'version', 'effective_from', 'status',
    ];

    protected $casts = [
        'effective_from' => 'date',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(ProcedureStep::class)->orderBy('step_no');
    }

    public function documentLinks(): MorphMany
    {
        return $this->morphMany(EvidenceLink::class, 'linkable');
    }
}
