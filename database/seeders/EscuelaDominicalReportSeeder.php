<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EscuelaDominicalReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $template = ReportTemplate::where(
                'slug',
                'escuela-dominical'
            )->firstOrFail();

            $sections = [
                [
                    'title' => 'Clases y asistencia',
                    'fields' => [
                        ['pases_clase', 'Pases de clase'],
                        ['promedio_asistencia_clases',
                         'Promedio de asistencia a las clases'],
                    ],
                ],
                [
                    'title' => 'Reuniones y coordinación',
                    'fields' => [
                        ['reuniones_comite', 'Reuniones con el comité'],
                        ['reuniones_pastor_diaconos',
                         'Reuniones con el Pastor/Diáconos'],
                        ['reuniones_comunicaciones',
                         'Reuniones con Comunicaciones'],
                        ['reuniones_padres', 'Reuniones con los padres'],
                        ['reuniones_ded', 'Reuniones con el DED'],
                        ['reuniones_asesoramiento',
                         'Reuniones de asesoramiento'],
                        ['reuniones_distritales', 'Reunión distrital'],
                    ],
                ],
                [
                    'title' => 'Resultados espirituales',
                    'fields' => [
                        ['bautizados_agua', 'Bautizados en agua'],
                        ['bautizados_espiritu_santo',
                         'Bautizados con el Espíritu Santo'],
                        ['decisiones_fe', 'Decisión de fe'],
                    ],
                ],
            ];

            foreach ($sections as $index => $data) {
                $section = $template->sections()->firstOrCreate(
                    ['title' => $data['title']],
                    ['sort_order' => $index + 1]
                );

                foreach ($data['fields'] as $order => $field) {
                    $section->fields()->firstOrCreate(
                        ['name' => $field[0]],
                        [
                            'label' => $field[1],
                            'field_type' => 'number',
                            'is_required' => false,
                            'sort_order' => $order + 1,
                        ]
                    );
                }
            }

            $section = $template->sections()->firstOrCreate(
                ['title' => 'Actividades destacadas'],
                ['sort_order' => 4]
            );

            for ($i = 1; $i <= 3; $i++) {
                $section->fields()->firstOrCreate(
                    ['name' => 'actividad_destacada_' . $i],
                    [
                        'label' => 'Descripción de actividad destacada ' . $i,
                        'field_type' => 'textarea',
                        'is_required' => false,
                        'sort_order' => $i,
                    ]
                );
            }
        });
    }
}

