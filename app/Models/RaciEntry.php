<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaciEntry extends Model
{
    protected $fillable = ['domain', 'activity', 'assignments', 'sort_order'];

    protected $casts = [
        'assignments' => 'array',
    ];
}
