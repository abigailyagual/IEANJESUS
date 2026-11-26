<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentActionLog;
use App\Models\DocumentWorkflowStep;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    
    public const ROLE_ADMIN = 0;
    public const ROLE_EMPLOYEE = 1;
    public const ROLE_OFFICE = 2;
    public const ROLE_DEPARTMENT_HEAD = 3;
    public const ROLE_PASTOR = 4;


    public const ROLE_LABELS = [
    self::ROLE_ADMIN => 'Administrador',
    self::ROLE_EMPLOYEE => 'Secretario de departamento',
    self::ROLE_OFFICE => 'Secretaría de Junta Local',
    self::ROLE_DEPARTMENT_HEAD => 'Director de departamento',
    self::ROLE_PASTOR => 'Pastor',
];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
   protected $fillable = [
    'name',
    'email',
    'position',
    'phone',
    'role',
    'password',
    'is_active',
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'is_active' => 'boolean',
];

    public function authoredDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'author_id');
    }

    public function documentsToApprove(): HasMany
    {
        return $this->hasMany(Document::class, 'approver_id');
    }

    public function workflowSteps(): HasMany
    {
        return $this->hasMany(DocumentWorkflowStep::class);
    }

    public function actionLogs(): HasMany
    {
        return $this->hasMany(DocumentActionLog::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function isAdmin(): bool
    {
        return (int) $this->role === self::ROLE_ADMIN;
    }

    public function isOffice(): bool
    {
        return (int) $this->role === self::ROLE_OFFICE;
    }

    public function isDepartmentHead(): bool
    {
        return (int) $this->role === self::ROLE_DEPARTMENT_HEAD;
    }

    public function isPastor(): bool
    {
        return (int) $this->role === self::ROLE_PASTOR;
    }

    public function isDepartmentSecretary(): bool
{
    return (int) $this->role === self::ROLE_EMPLOYEE;
}

public function canCreateMonthlyReports(): bool
{
    return $this->isDepartmentSecretary();
}

public function canSeeAllMonthlyReports(): bool
{
    return $this->isAdmin()
        || $this->isOffice()
        || $this->isPastor();
}

public function canSeeDepartmentMonthlyReports(): bool
{
    return $this->isDepartmentHead();
}

public function canReviewMonthlyReports(): bool
{
    return $this->isActive() && $this->isOffice();
}

public function canSeeInstitutionalDashboard(): bool
{
    return $this->isAdmin()
        || $this->isOffice()
        || $this->isPastor();
}

public function canApproveMonthlyReports(): bool
{
    return $this->isActive() && $this->isOffice();
}


    public function canSeeAllDocuments(): bool
    {
        return $this->isAdmin() || $this->isOffice();
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABELS[(int) $this->role] ?? 'Empleado';
    }
    /**
 * Determina si la cuenta del usuario está activa.
 */
public function isActive(): bool
{
    return (bool) $this->is_active;
}
public function canCreateMonthlyReportFor(int $categoryId): bool
{
    return $this->isActive()
        && $this->canCreateMonthlyReports()
        && $this->categories()
            ->whereKey($categoryId)
            ->exists();
}
public function canViewMonthlyReport(MonthlyReport $report): bool
{
    if (!$this->isActive()) {
        return false;
    }

    if ($this->canSeeAllMonthlyReports()) {
        return true;
    }

    $belongsToDepartment = $this->categories()
        ->whereKey($report->category_id)
        ->exists();

    if (!$belongsToDepartment) {
        return false;
    }

    if ($this->isDepartmentHead()) {
        return true;
    }

    return $this->isDepartmentSecretary()
        && (int) $report->user_id === (int) $this->id;
}
public function canEditMonthlyReport(MonthlyReport $report): bool
{
    return $this->isActive()
        && $this->isDepartmentSecretary()
        && (int) $report->user_id === (int) $this->id
        && $this->categories()
            ->whereKey($report->category_id)
            ->exists()
        && in_array($report->status, [
            MonthlyReport::STATUS_DRAFT,
            MonthlyReport::STATUS_OBSERVED,
        ], true);
}
public function canSubmitMonthlyReport(MonthlyReport $report): bool
{
    return $this->canEditMonthlyReport($report);
}
}
