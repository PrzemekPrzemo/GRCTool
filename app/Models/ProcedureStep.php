<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureStep extends Model
{
    protected $fillable = [
        'procedure_id', 'step_no', 'description', 'owner_role', 'sla', 'tool',
    ];

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }
}
