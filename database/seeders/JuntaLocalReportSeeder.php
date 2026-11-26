<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class JuntaLocalReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $template = ReportTemplate::where(
                'slug',
                'junta-local'
            )->firstOrFail();

            $sections = [
                [
                    'title' => 'Actividades espirituales',
                    'activities' => [
                        'Cadenas de oración',
                        'Ayunos',
                        'Cultos',
                        'Vigilias',
                        'Discipulado',
                    ],
                ],
                [
                    'title' => 'Actividades sociales',
                    'activities' => [
                        'Reuniones de servidores',
                        'Confraternidades',
                        'Cumpleaños',
                        'Recreación e integración',
                        'Seminarios',
                    ],
                ],
                [
                    'title' => 'Actividades económicas',
                    'activities' => [
                        'Ventas',
                        'Votos',
                        'Talentos',
                        'Protemplo',
                        'Donaciones por terceros',
                    ],
                ],
            ];

            foreach ($sections as $sectionIndex => $data) {
                $section = $template->sections()
                    ->firstOrCreate(
                        ['title' => $data['title']],
                        ['sort_order' => $sectionIndex + 1]
                    );

                $fieldOrder = 1;

                foreach ($data['activities'] as $activity) {
                    $slug = \Illuminate\Support\Str::slug(
                        $activity,
                        '_'
                    );

                    foreach ([
                        'cantidad' => 'Cantidad',
                        'asistencia' => 'Asistencia',
                    ] as $suffix => $label) {
                        $section->fields()->firstOrCreate(
                            ['name' => $slug . '_' . $suffix],
                            [
                                'label' => $activity . ' - ' . $label,
                                'field_type' => 'number',
                                'is_required' => false,
                                'sort_order' => $fieldOrder,
                            ]
                        );

                        $fieldOrder++;
                    }
                }
            }

            $observations = $template->sections()
                ->firstOrCreate(
                    ['title' => 'Eventos destacados'],
                    ['sort_order' => 4]
                );

            $observations->fields()->firstOrCreate(
                ['name' => 'eventos_destacados'],
                [
                    'label' => 'Describa los eventos más destacados',
                    'field_type' => 'textarea',
                    'is_required' => false,
                    'sort_order' => 1,
                ]
            );

            $sectionCount = $template->sections()->count();

            if ($sectionCount !== 4) {
                throw new RuntimeException(
                    'La plantilla de Junta Local debe tener '
                    . 'exactamente 4 secciones. '
                    . 'Se encontraron: ' . $sectionCount
                );
            }
        });
    }
}
