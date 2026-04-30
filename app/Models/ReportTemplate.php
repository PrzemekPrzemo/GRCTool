<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'category', 'description', 'view_path',
        'sections', 'data_queries', 'output_formats',
        'default_audience', 'default_classification', 'language', 'is_active',
    ];

    protected $casts = [
        'sections' => 'array',
        'data_queries' => 'array',
        'output_formats' => 'array',
        'is_active' => 'boolean',
    ];

    public function instances(): HasMany
    {
        return $this->hasMany(ReportInstance::class, 'template_id');
    }
}
