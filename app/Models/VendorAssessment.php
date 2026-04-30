<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VendorAssessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'third_party_id', 'assessment_type',
        'mcr_set_template_id', 'mcr_snapshot',
        'requested_by', 'requested_at', 'due_date', 'completed_at', 'next_assessment_due',
        'status',
        'vendor_contact_email', 'vendor_contact_name',
        'access_token', 'token_expires_at', 'last_accessed_at',
        'compliance_percentage',
        'mcr_total', 'mcr_compliant', 'mcr_partial', 'mcr_non_compliant', 'mcr_not_applicable',
        'critical_gaps_count',
        'reviewer_notes', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'mcr_snapshot' => 'array',
        'requested_at' => 'date',
        'due_date' => 'date',
        'completed_at' => 'date',
        'next_assessment_due' => 'date',
        'token_expires_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'compliance_percentage' => 'decimal:2',
    ];

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParty::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireTemplate::class, 'mcr_set_template_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(VendorAssessmentResponse::class, 'assessment_id');
    }

    public function generateAccessToken(int $daysValid = 30): string
    {
        $token = Str::random(48);
        $this->update([
            'access_token' => $token,
            'token_expires_at' => now()->addDays($daysValid),
        ]);

        return $token;
    }

    public function isTokenValid(): bool
    {
        return $this->access_token
            && $this->token_expires_at
            && $this->token_expires_at->isFuture();
    }

    public function recomputeScores(): void
    {
        $responses = $this->responses;
        $total = $responses->count();
        $compliant = $responses->where('response_value', 'Compliant')->count();
        $partial = $responses->where('response_value', 'Partial')->count();
        $nonCompliant = $responses->where('response_value', 'Non-compliant')->count();
        $na = $responses->where('response_value', 'Not-applicable')->count();
        $applicable = max(1, $total - $na);

        $this->update([
            'mcr_total' => $total,
            'mcr_compliant' => $compliant,
            'mcr_partial' => $partial,
            'mcr_non_compliant' => $nonCompliant,
            'mcr_not_applicable' => $na,
            'compliance_percentage' => round(($compliant + $partial * 0.5) / $applicable * 100, 2),
            'critical_gaps_count' => $responses->where('gap_severity', 'Critical')->count(),
        ]);
    }
}
