<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_template_id',
        'title',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(
            ReportTemplate::class,
            'report_template_id'
        );
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ReportField::class)
            ->orderBy('sort_order');
    }
}
