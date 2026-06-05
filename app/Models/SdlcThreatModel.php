<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SdlcThreatModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'title', 'methodology', 'status',
        'threats_identified', 'threats_mitigated',
        'document_url', 'notes', 'conducted_by', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'threats_identified' => 'integer',
        'threats_mitigated'  => 'integer',
        'conducted_by'       => 'integer',
        'reviewed_by'        => 'integer',
        'reviewed_at'        => 'datetime',
    ];

    public const METHODOLOGY_LABELS = [
        'stride'   => 'STRIDE',
        'pasta'    => 'PASTA',
        'linddun'  => 'LINDDUN',
        'other'    => 'Inne',
    ];

    public const STATUS_LABELS = [
        'draft'     => 'Szkic',
        'in_review' => 'W przeglądzie',
        'approved'  => 'Zatwierdzony',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(SdlcProject::class, 'project_id');
    }

    public function conductedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
