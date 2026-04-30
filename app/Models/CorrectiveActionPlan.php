<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorrectiveActionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'corrective_action_plans';

    protected $fillable = [
        'code', 'title', 'description', 'finding_ids',
        'approver_id', 'approved_at', 'effectiveness_review_date', 'status',
    ];

    protected $casts = [
        'finding_ids' => 'array',
        'approved_at' => 'datetime',
        'effectiveness_review_date' => 'date',
    ];

    public function actions(): HasMany
    {
        return $this->hasMany(CapAction::class, 'cap_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
