<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapAction extends Model
{
    use HasFactory;

    protected $table = 'cap_actions';

    protected $fillable = [
        'cap_id', 'title', 'description', 'owner_id',
        'due_date', 'completed_at', 'status', 'progress_percent',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'date',
    ];

    public function cap(): BelongsTo
    {
        return $this->belongsTo(CorrectiveActionPlan::class, 'cap_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
