<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MusicaReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $template = ReportTemplate::where(
                'slug',
                'musica'
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
                    'title' => 'Ensayos y asistencia',
                    'fields' => [
                        ['ensayos', 'Cantidad de ensayos', 'number'],
                        ['promedio_asistencia_culto', 'Promedio de asistencia al culto', 'number'],
                    ],
                ],
                [
                    'title' => 'Reuniones y coordinación',
                    'fields' => [
                        ['reuniones_comite', 'Reuniones con el comité', 'number'],
                        ['reuniones_pastor_diaconos', 'Reuniones con el Pastor/Diáconos', 'number'],
                        ['reuniones_asesoramiento', 'Reuniones de asesoramiento', 'number'],
                        ['reuniones_comunicaciones', 'Reuniones con Comunicaciones', 'number'],
                        ['reuniones_distritales', 'Reunión distrital', 'number'],
                        ['reuniones_vocalistas', 'Reuniones con vocalistas', 'number'],
                        ['reuniones_cantantes', 'Reuniones con cantantes', 'number'],
                        ['reuniones_sonidistas', 'Reuniones con sonidistas', 'number'],
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
