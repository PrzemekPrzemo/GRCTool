<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SecurityQuestionnaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'direction', 'template_id', 'client_id', 'third_party_id',
        'name', 'notes',
        'received_at', 'sent_at', 'due_date', 'completed_at',
        'status', 'total_questions', 'auto_filled_count', 'manual_count', 'approved_count',
        'owner_id', 'final_export_id',
    ];

    protected $casts = [
        'received_at' => 'date',
        'sent_at' => 'date',
        'due_date' => 'date',
        'completed_at' => 'date',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireTemplate::class, 'template_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParty::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionnaireQuestion::class, 'questionnaire_id')->orderBy('order');
    }

    public function finalExport(): BelongsTo
    {
        return $this->belongsTo(ReportInstance::class, 'final_export_id');
    }

    public function progressPercent(): int
    {
        if ($this->total_questions === 0) {
            return 0;
        }

        return (int) round($this->approved_count / $this->total_questions * 100);
    }

    public function refreshCounts(): void
    {
        $this->total_questions = $this->questions()->count();
        $this->auto_filled_count = $this->questions()->where('status', 'Auto-filled')->count();
        $this->approved_count = $this->questions()->where('status', 'Approved')->count();
        $this->manual_count = $this->total_questions - $this->auto_filled_count;
        $this->save();
    }
}
