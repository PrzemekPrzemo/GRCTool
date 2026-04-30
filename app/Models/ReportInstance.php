<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportInstance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'template_id', 'generated_by', 'generated_at',
        'period_start', 'period_end', 'scope', 'parameters',
        'output_files', 'digital_signature', 'watermark_text', 'watermark_metadata',
        'distribution_log', 'revoked', 'revoked_at', 'revoked_reason', 'classification',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
        'scope' => 'array',
        'parameters' => 'array',
        'output_files' => 'array',
        'watermark_metadata' => 'array',
        'distribution_log' => 'array',
        'revoked' => 'boolean',
        'revoked_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'template_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
