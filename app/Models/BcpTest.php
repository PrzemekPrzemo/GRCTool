<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BcpTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'bcp_plan_id', 'code', 'test_type', 'tested_at', 'conducted_by',
        'participants', 'test_scenario', 'result', 'gaps_identified',
        'actions_taken', 'next_test_due',
    ];

    protected $casts = [
        'participants' => 'array',
        'tested_at' => 'date',
        'next_test_due' => 'date',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(BcpPlan::class, 'bcp_plan_id');
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }
}
