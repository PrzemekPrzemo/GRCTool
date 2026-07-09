<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDisposal extends Model
{
    protected $fillable = [
        'code', 'asset_id', 'asset_ref', 'device_type', 'serial_number', 'data_classification',
        'sanitisation_method', 'sanitised_at', 'performed_by', 'certificate_ref',
        'disposal_method', 'confirmed_by',
    ];

    protected $casts = [
        'sanitised_at' => 'date',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
