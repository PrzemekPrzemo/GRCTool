<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiTool extends Model
{
    protected $fillable = [
        'name', 'vendor', 'category', 'approval_status', 'dpa_status', 'training_use',
        'permitted_data', 'prohibited_data', 'approved_by', 'approval_date', 'review_date',
    ];

    protected $casts = [
        'approval_date' => 'date',
        'review_date' => 'date',
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
