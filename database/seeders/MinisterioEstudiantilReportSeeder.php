<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MinisterioEstudiantilReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $template = ReportTemplate::where(
                'slug',
                'ministerio-estudiantil'
            )->firstOrFail();

            $sections = [
                [
                    'title' => 'Programas del Ministerio Estudiantil',
                    'fields' => [
                        ['ayunos_cantidad', 'Ayunos - Cantidad', 'number'],
                        ['ayunos_asistencia', 'Ayunos - Promedio de asistencia', 'number'],
                        ['cadenas_oracion_cantidad', 'Cadenas de oración - Cantidad', 'number'],
                        ['cadenas_oracion_asistencia', 'Cadenas de oración - Promedio de asistencia', 'number'],
                        ['otros_programas', 'Otros programas del Ministerio Estudiantil', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Grupos y actividades económicas',
                    'fields' => [
                        ['total_grupos', 'Total de grupos del Ministerio Estudiantil', 'number'],
                        ['total_integrantes', 'Total de hermanos que integran los grupos de M.E.', 'number'],
                        ['actividades_economicas', 'Actividades económicas realizadas', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Resultados de los eventos',
                    'fields' => [
                        ['sanidades', 'Sanidades', 'number'],
                        ['milagros', 'Milagros', 'number'],
                        ['bautismos_espiritu_santo', 'Bautismos del Espíritu Santo', 'number'],
                        ['reconciliados', 'Reconciliados', 'number'],
                        ['decisiones_fe', 'Decisiones de fe', 'number'],
                        ['renovados_espiritu_santo', 'Renovados con el Espíritu Santo', 'number'],
                        ['otras_experiencias', 'Otras experiencias de programas', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Instituciones educativas',
                    'fields' => [
                        ['instituciones_involucradas', 'Instituciones educativas involucradas', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Reuniones y coordinación',
                    'fields' => [
                        ['reuniones_pastor', 'Reuniones con el Pastor', 'number'],
                        ['reuniones_misiones', 'Reuniones con Misiones', 'number'],
                        ['reuniones_grupos', 'Reuniones con los grupos', 'number'],
                        ['reuniones_asesoria', 'Reuniones de asesoría', 'number'],
                        ['reuniones_comunicaciones', 'Reuniones con Comunicaciones', 'number'],
                        ['reuniones_grupos_locales', 'Reuniones con los grupos de M.E. local', 'number'],
                        ['observaciones_reuniones', 'Observaciones de reuniones y coordinación', 'textarea'],
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
