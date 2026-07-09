<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceGap extends Model
{
    protected $fillable = [
        'gap_code', 'title', 'description', 'affected_frameworks',
        'remediation', 'target_date', 'severity', 'status',
    ];

    protected $casts = [
        'affected_frameworks' => 'array',
        'target_date' => 'date',
    ];

    public function isOverdue(): bool
    {
        return $this->status !== 'closed' && $this->target_date !== null && $this->target_date->isPast();
    }
}
