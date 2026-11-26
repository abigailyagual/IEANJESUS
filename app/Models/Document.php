<?php

namespace App\Models;

use App\Models\Category;
use App\Models\DocumentActionLog;
use App\Models\DocumentWorkflowStep;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_LABELS = [
        'internal' => 'Interno',
        'incoming' => 'Entrante',
        'outgoing' => 'Saliente',
        'contract' => 'Contrato',
        'order' => 'Orden',
    ];

    public const STATUS_LABELS = [
        'draft' => 'Borrador',
        'in_review' => 'En revisión',
        'approved' => 'Aprobado',
        'rejected' => 'Devuelto para corrección',
        'archived' => 'Archivado',
    ];

    public const PRIORITY_LABELS = [
        'low' => 'Bajo',
        'normal' => 'Normal',
        'high' => 'Alto',
        'critical' => 'Crítico',
    ];

    protected $fillable = [
        'name',
        'registration_number',
        'document_type',
        'status',
        'priority',
        'file',
        'summary',
        'external_partner',
        'category_id',
        'author_id',
        'approver_id',
        'visibility',
        'due_at',
        'submitted_at',
        'approved_at',
        'archived_at',
        'rejection_reason',
        'workflow_round',
        'report_month',
        'report_year',
    ];

    protected $casts = [
        'visibility' => 'boolean',
        'due_at' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'archived_at' => 'datetime',
        'workflow_round' => 'integer',
        'report_month' => 'integer',
        'report_year' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function workflowSteps()
    {
        return $this->hasMany(DocumentWorkflowStep::class)
            ->orderByDesc('round')
            ->orderBy('sequence');
    }

    public function actionLogs()
    {
        return $this->hasMany(DocumentActionLog::class)->latest();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->canSeeAllDocuments()) {
            return $query;
        }

        $departmentIds = $user->categories()->pluck('categories.id');

        return $query->where(function (Builder $builder) use ($user, $departmentIds) {
            $builder
                ->where('author_id', $user->id)
                ->orWhere('approver_id', $user->id)
                ->orWhereHas('workflowSteps', function (Builder $stepQuery) use ($user) {
                    $stepQuery->where('user_id', $user->id);
                })
                ->orWhere(function (Builder $departmentScope) use ($departmentIds, $user) {
                    $departmentScope
                        ->whereIn('category_id', $departmentIds)
                        ->when(!$user->isDepartmentHead(), function (Builder $visibleQuery) {
                            $visibleQuery->where('visibility', true);
                        });
                });
        });
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->document_type] ?? $this->document_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITY_LABELS[$this->priority] ?? $this->priority;
    }

    public function isEditableBy(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->author_id === $user->id && in_array($this->status, ['draft', 'rejected'], true);
    }

    public function canBeSubmittedBy(User $user): bool
    {
        if ($user->isAdmin()) {
            return in_array($this->status, ['draft', 'rejected'], true);
        }

        return $this->author_id === $user->id && in_array($this->status, ['draft', 'rejected'], true);
    }

    public function canBeApprovedBy(User $user): bool
    {
        if ($this->status !== 'in_review') {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $currentStep = $this->current_step;

        return $currentStep?->status === DocumentWorkflowStep::STATUS_IN_PROGRESS
            && (int) $currentStep->user_id === (int) $user->id;
    }

    public function canBeArchivedBy(User $user): bool
    {
        return ($user->isAdmin() || $user->isOffice()) && $this->status === 'approved';
    }

    public function getCurrentStepAttribute(): ?DocumentWorkflowStep
    {
        if ($this->workflow_round < 1) {
            return null;
        }

        if ($this->relationLoaded('workflowSteps')) {
            return $this->workflowSteps
                ->first(fn (DocumentWorkflowStep $step) => (int) $step->round === (int) $this->workflow_round
                    && in_array($step->status, [
                        DocumentWorkflowStep::STATUS_PENDING,
                        DocumentWorkflowStep::STATUS_IN_PROGRESS,
                    ], true));
        }

        return $this->workflowSteps()
            ->where('round', $this->workflow_round)
            ->whereIn('status', [
                DocumentWorkflowStep::STATUS_PENDING,
                DocumentWorkflowStep::STATUS_IN_PROGRESS,
            ])
            ->orderBy('sequence')
            ->first();
    }

    public function getCurrentStepLabelAttribute(): string
    {
        if ($this->status === 'approved') {
            return 'Flujo finalizado';
        }

        if ($this->status === 'archived') {
            return 'Enviado al archivo';
        }

        if ($this->status === 'rejected') {
            return 'Pendiente de corrección por el autor';
        }

        return $this->current_step?->title ?? 'Flujo no iniciado';
    }

    public function isAwaitingActionBy(User $user): bool
    {
        if ($this->status !== 'in_review') {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return (int) $this->current_step?->user_id === (int) $user->id;
    }
}
