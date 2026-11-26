<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefamReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $template = ReportTemplate::where(
                'slug',
                'refam'
            )->firstOrFail();

            $sections = [
                [
                    'title' => 'Programas de REFAM',
                    'fields' => [
                        ['encuentros_cantidad', 'Encuentros de REFAM - Cantidad', 'number'],
                        ['encuentros_asistencia', 'Encuentros de REFAM - Promedio de asistencia', 'number'],
                        ['cadenas_oracion_cantidad', 'Cadenas de oración - Cantidad', 'number'],
                        ['cadenas_oracion_asistencia', 'Cadenas de oración - Promedio de asistencia', 'number'],
                        ['otros_programas', 'Otros programas de REFAM', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Estadísticas generales de REFAM',
                    'fields' => [
                        ['total_refam', 'Total de REFAM', 'number'],
                        ['total_lideres', 'Total de líderes de REFAM', 'number'],
                        ['integrantes_ayudantes', 'Integrantes o ayudantes de REFAM', 'number'],
                        ['asistentes_refam', 'Asistentes de REFAM', 'number'],
                        ['asistentes_templo', 'Asistentes al templo', 'number'],
                        ['refam_abiertas', 'REFAM abiertas', 'number'],
                        ['refam_cerradas', 'REFAM cerradas', 'number'],
                    ],
                ],
                [
                    'title' => 'Resultados de REFAM',
                    'fields' => [
                        ['sanidades', 'Sanidades', 'number'],
                        ['milagros', 'Milagros', 'number'],
                        ['bautismos_espiritu_santo', 'Bautismos del Espíritu Santo', 'number'],
                        ['bautismos_agua', 'Bautismos en agua', 'number'],
                        ['reconciliados', 'Reconciliados', 'number'],
                        ['decisiones_fe', 'Decisiones de fe', 'number'],
                        ['renovados_espiritu_santo', 'Renovados con el Espíritu Santo', 'number'],
                        ['otros_bautismos_espiritu_santo', 'Baut. Esp. Santo (segundo registro del formato)', 'number'],
                        ['otras_experiencias', 'Otras experiencias de programas', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Reuniones y coordinación',
                    'fields' => [
                        ['reuniones_pastor', 'Reuniones con el Pastor', 'number'],
                        ['reuniones_lideres_refam', 'Reuniones con líderes de REFAM', 'number'],
                        ['reuniones_misiones_local', 'Reuniones con el Comité de Misiones Local', 'number'],
                        ['reuniones_comunicaciones', 'Reuniones con Comunicaciones', 'number'],
                        ['reuniones_asesoramiento', 'Reuniones de asesoramiento', 'number'],
                        ['reunion_distrital', 'Reunión distrital', 'number'],
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
