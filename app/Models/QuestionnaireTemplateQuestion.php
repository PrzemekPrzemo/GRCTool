<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireTemplateQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id', 'code', 'category', 'subcategory',
        'question_text', 'guidance', 'expected_answer_type',
        'answer_options', 'framework_refs', 'is_required', 'order',
    ];

    protected $casts = [
        'answer_options' => 'array',
        'framework_refs' => 'array',
        'is_required' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireTemplate::class, 'template_id');
    }
}
