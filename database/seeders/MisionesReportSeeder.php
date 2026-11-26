<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MisionesReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $template = ReportTemplate::where(
                'slug',
                'misiones'
            )->firstOrFail();

            $sections = [
                [
                    'title' => 'Actividades realizadas',
                    'fields' => [
                        [
                            'actividad_espiritual',
                            'Actividades espirituales realizadas',
                            'textarea'
                        ],
                        [
                            'cantidad_actividades_espirituales',
                            'Cantidad de actividades espirituales',
                            'number'
                        ],
                        [
                            'actividad_social',
                            'Actividades sociales realizadas',
                            'textarea'
                        ],
                        [
                            'cantidad_actividades_sociales',
                            'Cantidad de actividades sociales',
                            'number'
                        ],
                        [
                            'actividad_economica',
                            'Actividades económicas realizadas',
                            'textarea'
                        ],
                        [
                            'cantidad_actividades_economicas',
                            'Cantidad de actividades económicas',
                            'number'
                        ],
                    ],
                ],
                [
                    'title' => 'Reuniones y coordinación',
                    'fields' => [
                        [
                            'reuniones_comite',
                            'Reuniones con el comité',
                            'number'
                        ],
                        [
                            'reuniones_pastor_diaconos',
                            'Reuniones con el Pastor/Diáconos',
                            'number'
                        ],
                        [
                            'reuniones_comunicaciones',
                            'Reuniones con Comunicaciones',
                            'number'
                        ],
                        [
                            'reunion_distrital',
                            'Reunión distrital',
                            'number'
                        ],
                        [
                            'reuniones_asesoramiento',
                            'Reuniones de asesoramiento',
                            'number'
                        ],
                        [
                            'reuniones_areas_misiones',
                            'Reuniones con las áreas de Misiones',
                            'number'
                        ],
                    ],
                ],
                [
                    'title' => 'Resultados espirituales',
                    'fields' => [
                        [
                            'bautizados_espiritu_santo',
                            'Bautizados con el Espíritu Santo',
                            'number'
                        ],
                        [
                            'bautizados_agua',
                            'Bautizados en agua',
                            'number'
                        ],
                        [
                            'decisiones_fe',
                            'Decisiones de fe',
                            'number'
                        ],
                        [
                            'reconciliados',
                            'Reconciliados',
                            'number'
                        ],
                    ],
                ],
            ];

            foreach ($sections as $sectionIndex => $sectionData) {

                $section = $template->sections()->firstOrCreate(
                    [
                        'title' => $sectionData['title']
                    ],
                    [
                        'sort_order' => $sectionIndex + 1
                    ]
                );

                foreach ($sectionData['fields'] as $fieldIndex => $fieldData) {

                    $section->fields()->firstOrCreate(
                        [
                            'name' => $fieldData[0]
                        ],
                        [
                            'label' => $fieldData[1],
                            'field_type' => $fieldData[2],
                            'is_required' => false,
                            'sort_order' => $fieldIndex + 1
                        ]
                    );
                }
            }
        });
    }
}
