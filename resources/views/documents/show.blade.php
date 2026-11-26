@extends('layouts.system')

@section('title', $document->name)
@section('page-title', 'Ficha del documento')
@section('eyebrow', $document->registration_number)

@php
    $badgeClass = match ($document->status) {
        'draft' => 'badge-slate',
        'in_review' => 'badge-amber',
        'approved', 'archived' => 'badge-emerald',
        'rejected' => 'badge-rose',
        default => 'badge-blue',
    };

    $stepBadgeClass = function (string $status): string {
        return match ($status) {
            \App\Models\DocumentWorkflowStep::STATUS_IN_PROGRESS => 'badge-amber',
            \App\Models\DocumentWorkflowStep::STATUS_APPROVED => 'badge-emerald',
            \App\Models\DocumentWorkflowStep::STATUS_REJECTED => 'badge-rose',
            \App\Models\DocumentWorkflowStep::STATUS_SKIPPED => 'badge-slate',
            default => 'badge-blue',
        };
    };

    $workflowRounds = $document->workflowSteps->groupBy('round')->sortKeysDesc();
@endphp

@section('page-actions')
    <a href="{{ route('documents.index') }}" class="btn-secondary">Volver al listado</a>
    @if ($document->isEditableBy(auth()->user()))
        <a href="{{ route('documents.edit', $document) }}" class="btn-secondary">Editar</a>
    @endif
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[2fr,1fr]">
        <div class="space-y-6">
            <section class="panel">
                <div class="panel-header">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-lg font-semibold text-slate-900">{{ $document->name }}</h2>
                            <span class="{{ $badgeClass }}">{{ $document->status_label }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">{{ $document->summary ?: 'No se ha ingresado un resumen.' }}</p>
                    </div>
                    @if ($document->file)
                        <a href="{{ url('files/' . $document->file) }}" download class="btn-secondary">Descargar archivo</a>
                    @endif
                </div>

                <div class="grid gap-5 px-5 py-5 md:grid-cols-2">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tipo de documento</div>
                        <div class="mt-1 text-sm text-slate-800">{{ $document->type_label }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Prioridad</div>
                        <div class="mt-1 text-sm text-slate-800">{{ $document->priority_label }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Departamento</div>
                        <div class="mt-1 text-sm text-slate-800">{{ $document->category?->name ?: 'No especificado' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Etapa actual</div>
                        <div class="mt-1 text-sm text-slate-800">{{ $document->current_step_label }}</div>
                        @if ($document->current_step)
                            <div class="mt-1 text-xs text-slate-500">Responsable: {{ $document->current_step->assignee_name }}</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Autor</div>
                        <div class="mt-1 text-sm text-slate-800">{{ $document->author?->name ?: 'No especificado' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Revisor adicional</div>
                        <div class="mt-1 text-sm text-slate-800">{{ $document->approver?->name ?: 'Sin asignar' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Fecha límite</div>
                        <div class="mt-1 text-sm text-slate-800">{{ optional($document->due_at)->format('d.m.Y') ?: 'Sin plazo' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Contraparte / destinatario</div>
                        <div class="mt-1 text-sm text-slate-800">{{ $document->external_partner ?: 'No especificado' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Enviado a revisión</div>
                        <div class="mt-1 text-sm text-slate-800">{{ optional($document->submitted_at)->format('d.m.Y H:i') ?: 'Todavía no enviado' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Aprobación / archivo</div>
                        <div class="mt-1 text-sm text-slate-800">
                            {{ optional($document->approved_at)->format('d.m.Y H:i') ?: 'Sin fecha de aprobación' }}
                            @if ($document->archived_at)
                                <span class="mt-1 block text-xs text-slate-500">Archivo: {{ $document->archived_at->format('d.m.Y H:i') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($document->rejection_reason)
                    <div class="border-t border-slate-200 px-5 py-5">
                        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-4">
                            <div class="text-sm font-medium text-rose-700">Motivo de devolución</div>
                            <div class="mt-2 text-sm text-rose-700">{{ $document->rejection_reason }}</div>
                        </div>
                    </div>
                @endif
            </section>

            <section class="panel overflow-hidden">
                <div class="panel-header">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Flujo de aprobación</h2>
                        <p class="text-sm text-slate-500">Todas las etapas por ronda de envío, incluidos comentarios y responsables.</p>
                    </div>
                </div>
                <div class="space-y-6 px-5 py-5">
                    @forelse ($workflowRounds as $round => $steps)
                        <div>
                            <div class="mb-3 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-900">Ronda {{ $round }}</h3>
                                <span class="badge-slate">{{ $steps->count() }} etapas</span>
                            </div>
                            <div class="space-y-3">
                                @foreach ($steps as $step)
                                    <div class="rounded-lg border border-slate-200 px-4 py-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-slate-900">{{ $step->sequence }}. {{ $step->title }}</span>
                                                    <span class="{{ $stepBadgeClass($step->status) }}">{{ $step->status_label }}</span>
                                                </div>
                                                <div class="mt-2 text-sm text-slate-600">
                                                    {{ $step->assignee_name }}
                                                    @if ($step->department)
                                                        <span class="text-slate-400">· {{ $step->department->name }}</span>
                                                    @endif
                                                </div>
                                                @if ($step->comment)
                                                    <div class="mt-3 rounded-md bg-slate-50 px-3 py-3 text-sm text-slate-600">{{ $step->comment }}</div>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-500 sm:text-right">
                                                <div>Inicio: {{ optional($step->started_at)->format('d.m.Y H:i') ?: '—' }}</div>
                                                <div class="mt-1">Decisión: {{ optional($step->acted_at)->format('d.m.Y H:i') ?: '—' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">El flujo todavía no se ha iniciado.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel overflow-hidden">
                <div class="panel-header">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Historial de acciones</h2>
                        <p class="text-sm text-slate-500">Historial de creación, envío, aprobación, devolución y archivo.</p>
                    </div>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse ($document->actionLogs as $log)
                        <div class="px-5 py-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="text-sm font-medium text-slate-900">{{ $log->title }}</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $log->actor_name }}
                                        @if ($log->workflowStep)
                                            <span class="text-slate-400">· {{ $log->workflowStep->title }}</span>
                                        @endif
                                    </div>
                                    @if ($log->description)
                                        <div class="mt-2 text-sm text-slate-600">{{ $log->description }}</div>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500">{{ $log->created_at->format('d.m.Y H:i') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-sm text-slate-500">El historial está vacío.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="panel p-5">
                <h2 class="text-base font-semibold text-slate-900">Acciones</h2>
                <div class="mt-4 flex flex-col gap-3">
                    @if ($document->canBeSubmittedBy(auth()->user()))
                        <form method="POST" action="{{ route('documents.submit', $document) }}">
                            @csrf
                            <button type="submit" class="btn-primary w-full">Enviar a revisión</button>
                        </form>
                    @endif

                    @if ($document->canBeApprovedBy(auth()->user()))
                        <form method="POST" action="{{ route('documents.approve', $document) }}" class="space-y-3">
                            @csrf
                            <textarea name="comment" rows="3" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400" placeholder="Comentario de aprobación (opcional)"></textarea>
                            <button type="submit" class="btn-primary w-full">Confirmar etapa</button>
                        </form>

                        <form method="POST" action="{{ route('documents.reject', $document) }}" class="space-y-3">
                            @csrf
                            <textarea name="comment" rows="3" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400" placeholder="Qué se debe corregir"></textarea>
                            <button type="submit" class="btn-danger w-full">Devolver para corrección</button>
                        </form>
                    @endif

                    @if ($document->canBeArchivedBy(auth()->user()))
                        <form method="POST" action="{{ route('documents.archive', $document) }}" class="space-y-3">
                            @csrf
                            <textarea name="comment" rows="2" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400" placeholder="Comentario de archivo (opcional)"></textarea>
                            <button type="submit" class="btn-secondary w-full">Archivar</button>
                        </form>
                    @endif

                    @if ($document->isEditableBy(auth()->user()) || auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('documents.destroy', $document) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger w-full" onclick="return confirm('Eliminar documento?')">Eliminar documento</button>
                        </form>
                    @endif
                </div>
            </section>

            <section class="panel p-5">
                <h2 class="text-base font-semibold text-slate-900">Guía de trabajo</h2>
                <div class="mt-4 space-y-4 text-sm text-slate-600">
                    <div>
                        <div class="font-medium text-slate-800">1. Preparación</div>
                        <div class="mt-1">El autor crea el registro, carga el archivo y completa los datos del documento.</div>
                    </div>
                    <div>
                        <div class="font-medium text-slate-800">2. Flujo</div>
                        <div class="mt-1">El sistema genera automáticamente el flujo con el director, el revisor y Secretaría de Junta Local.</div>
                    </div>
                    <div>
                        <div class="font-medium text-slate-800">3. Decisión</div>
                        <div class="mt-1">En cualquier etapa se puede aprobar o devolver el documento para corrección con un comentario.</div>
                    </div>
                    <div>
                        <div class="font-medium text-slate-800">4. Archivo</div>
                        <div class="mt-1">Al finalizar el flujo, el registro pasa al archivo electrónico y se registra en el historial.</div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
