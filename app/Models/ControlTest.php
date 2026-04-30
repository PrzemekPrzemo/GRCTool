<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_id', 'tested_by', 'test_date', 'method', 'result',
        'procedures_performed', 'observations', 'exceptions_noted', 'sample_details',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'test_date' => 'date',
        'reviewed_at' => 'datetime',
        'sample_details' => 'array',
    ];

    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class);
    }

    public function tester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
