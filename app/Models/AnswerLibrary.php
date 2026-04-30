<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnswerLibrary extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'answer_library';

    protected $fillable = [
        'code', 'canonical_question', 'aliases',
        'canonical_answer_short', 'canonical_answer_long',
        'evidence_attachments', 'tags', 'frameworks',
        'confidentiality_level', 'version',
        'last_reviewed_at', 'reviewed_by', 'next_review_due',
        'usage_count', 'is_active',
    ];

    protected $casts = [
        'aliases' => 'array',
        'evidence_attachments' => 'array',
        'tags' => 'array',
        'frameworks' => 'array',
        'last_reviewed_at' => 'date',
        'next_review_due' => 'date',
        'is_active' => 'boolean',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AnswerLibraryVersion::class, 'answer_id')->orderByDesc('version_number');
    }

    public function isReviewOverdue(): bool
    {
        return $this->next_review_due && $this->next_review_due->isPast();
    }
}
