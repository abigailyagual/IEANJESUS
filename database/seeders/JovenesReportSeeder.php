<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JovenesReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $template = ReportTemplate::where(
                'slug',
                'jovenes'
            )->firstOrFail();

            $sections = [
                [
                    'title' => 'Actividades destacadas',
                    'fields' => [
                        ['actividad_espiritual', 'Actividad espiritual más destacada', 'textarea'],
                        ['actividad_social', 'Actividad social más destacada', 'textarea'],
                        ['actividad_economica', 'Actividad económica más destacada', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Visitas',
                    'fields' => [
                        ['visitas', 'Visitas realizadas', 'number'],
                    ],
                ],
                [
                    'title' => 'Reuniones y coordinación',
                    'fields' => [
                        ['reuniones_comite', 'Reuniones con el comité', 'number'],
                        ['reuniones_pastor_diaconos', 'Reuniones con el Pastor/Diáconos', 'number'],
                        ['reuniones_asesoria', 'Reuniones de asesoría', 'number'],
                        ['reuniones_comunicaciones', 'Reuniones con Comunicaciones', 'number'],
                        ['reuniones_distritales', 'Reunión distrital', 'number'],
                    ],
                ],
                [
                    'title' => 'Resultados espirituales',
                    'fields' => [
                        ['decisiones_fe', 'Decisión de fe', 'number'],
                    ],
                ],
            ];

            foreach ($sections as $sectionIndex => $sectionData) {

                $section = $template->sections()->firstOrCreate(
                    ['title' => $sectionData['title']],
                    ['sort_order' => $sectionIndex + 1]
                );

                foreach ($sectionData['fields'] as $fieldIndex => $fieldData) {

                    $section->fields()->firstOrCreate(
                        ['name' => $fieldData[0]],
                        [
                            'label' => $fieldData[1],
                            'field_type' => $fieldData[2],
                            'is_required' => false,
                            'sort_order' => $fieldIndex + 1,
                        ]
                    );
                }
            }
        });
    }
}

