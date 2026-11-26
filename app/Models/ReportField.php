<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportField extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_section_id',
        'name',
        'label',
        'field_type',
        'options',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(
            ReportSection::class,
            'report_section_id'
        );
    }

    public function values(): HasMany
    {
        return $this->hasMany(ReportValue::class);
    }
}
