<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceCalendarTask extends Model
{
    protected $fillable = [
        'ref', 'task', 'frequency', 'months', 'owner_role', 'related_document', 'status',
    ];
}
