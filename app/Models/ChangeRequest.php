<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeRequest extends Model
{
    protected $fillable = [
        'code', 'change_type', 'title', 'systems_affected', 'requester', 'risk_level',
        'approved_by', 'approval_date', 'implementation_date', 'rollback_plan', 'outcome', 'status',
    ];

    protected $casts = [
        'approval_date' => 'date',
        'implementation_date' => 'date',
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
