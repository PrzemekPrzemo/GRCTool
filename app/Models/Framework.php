<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Framework extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'issuer', 'description', 'category', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(FrameworkVersion::class);
    }

    public function currentVersion()
    {
        return $this->versions()->where('is_current', true)->first();
    }
}
