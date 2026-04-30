<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'source_org', 'direction', 'version', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionnaireTemplateQuestion::class, 'template_id')->orderBy('order');
    }
}
