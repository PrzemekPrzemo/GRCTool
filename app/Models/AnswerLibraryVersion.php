<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnswerLibraryVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'answer_id', 'version_number', 'snapshot', 'changed_by', 'change_reason', 'changed_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'changed_at' => 'datetime',
    ];

    public function answer(): BelongsTo
    {
        return $this->belongsTo(AnswerLibrary::class, 'answer_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
