<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_report_id',
        'report_field_id',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function monthlyReport(): BelongsTo
    {
        return $this->belongsTo(
            MonthlyReport::class,
            'monthly_report_id'
        );
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(
            ReportField::class,
            'report_field_id'
        );
    }
}

