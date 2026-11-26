<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProtemploReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $template = ReportTemplate::where(
                'slug',
                'protemplo'
            )->firstOrFail();

            $sections = [
                [
                    'title' => 'Actividades espirituales según POA',
                    'fields' => [
                        ['cadenas_oracion', 'Cadenas de oración', 'number'],
                        ['ayunos', 'Ayunos', 'number'],
                        ['otras_actividades_espirituales', 'Otras actividades espirituales realizadas', 'textarea'],
                        ['total_espirituales_trimestre', 'Total de actividades espirituales por trimestre', 'number'],
                        ['detalle_espirituales', 'Detalle de actividades espirituales', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Actividades sociales según POA',
                    'fields' => [
                        ['actividades_sociales', 'Actividades sociales realizadas', 'textarea'],
                        ['total_sociales_trimestre', 'Total de actividades sociales por trimestre', 'number'],
                        ['detalle_sociales', 'Detalle de actividades sociales', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Actividades económicas según POA',
                    'fields' => [
                        ['actividades_economicas', 'Actividades económicas realizadas', 'textarea'],
                        ['total_economicas_trimestre', 'Total de actividades económicas por trimestre', 'number'],
                    ],
                ],
                [
                    'title' => 'Reuniones y coordinación',
                    'fields' => [
                        ['reuniones_pastor_diaconos', 'Reuniones con el Pastor/Diáconos', 'number'],
                        ['reuniones_comite', 'Reuniones con el comité', 'number'],
                        ['mingas_proconstruccion', 'Mingas de proconstrucción', 'number'],
                        ['reuniones_asesoria', 'Reuniones de asesoría', 'number'],
                        ['reuniones_comunicaciones', 'Reuniones con Comunicaciones', 'number'],
                    ],
                ],
                [
                    'title' => 'Construcción y colaboraciones',
                    'fields' => [
                        ['notas_construccion', 'Notas adicionales de la construcción y colaboraciones', 'textarea'],
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
