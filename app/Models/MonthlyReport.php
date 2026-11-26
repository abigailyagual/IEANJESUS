<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthlyReport extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_OBSERVED = 'observed';
    public const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'report_template_id',
        'category_id',
        'user_id',
        'report_month',
        'report_year',
        'status',
        'observations',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'report_month' => 'integer',
        'report_year' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(
            ReportTemplate::class,
            'report_template_id'
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ReportValue::class);
    }
    public function scopeVisibleTo(\Illuminate\Database\Eloquent\Builder $query, User $user): \Illuminate\Database\Eloquent\Builder
    {
        if (!$user->isActive()) {
            return $query->whereRaw('1 = 0');
        }
        if ($user->canSeeAllMonthlyReports()) {
            return $query;
        }
        if (!$user->isDepartmentHead() && !$user->isDepartmentSecretary()) {
            return $query->whereRaw('1 = 0');
        }
        return $query->whereHas('category.users', fn ($q) => $q->whereKey($user->id))
            ->when($user->isDepartmentSecretary(), fn ($q) => $q->where('monthly_reports.user_id', $user->id));
    }
}
