<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_id', 'template_question_id',
        'original_text', 'category', 'expected_answer_type',
        'mapped_answer_id', 'confidence_score',
        'answer_text', 'evidence_ids',
        'status', 'reviewed_by', 'reviewed_at',
        'order',
    ];

    protected $casts = [
        'evidence_ids' => 'array',
        'confidence_score' => 'decimal:3',
        'reviewed_at' => 'datetime',
    ];

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(SecurityQuestionnaire::class, 'questionnaire_id');
    }

    public function mappedAnswer(): BelongsTo
    {
        return $this->belongsTo(AnswerLibrary::class, 'mapped_answer_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
