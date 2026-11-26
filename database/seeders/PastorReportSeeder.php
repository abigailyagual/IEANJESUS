<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportTemplate;
use App\Models\ReportSection;
use App\Models\ReportField;

class PastorReportSeeder extends Seeder
{
    public function run(): void
    {
        $template = ReportTemplate::where('slug', 'pastor')->firstOrFail();

        $sections = [
            'Resultados espirituales' => [
                ['entregados', 'Entregados', 'number'],
                ['reconciliados', 'Reconciliados', 'number'],
                ['bautizados_agua', 'Bautizados en agua', 'number'],
                ['bautizados_espiritu', 'Bautizados con el poder del Espíritu Santo', 'number'],
                ['lugares_predicacion', 'Número de lugares de predicación', 'number'],
                ['numero_refam', 'Número de REFAM', 'number'],
            ],
            'Promedio de asistencia dominical' => [
                ['caballeros', 'Caballeros', 'number'],
                ['damas', 'Damas', 'number'],
                ['adolescentes', 'Adolescentes', 'number'],
                ['ninos', 'Niños', 'number'],
                ['simpatizantes_hombres', 'Simpatizantes hombres', 'number'],
                ['simpatizantes_mujeres', 'Simpatizantes mujeres', 'number'],
                ['asistencia_virtual', 'Asistencia virtual', 'number'],
            ],
            'Reuniones y actividades' => [
                ['reuniones_junta_local', 'Reuniones con Junta Local', 'number'],
                ['reuniones_distritales', 'Reuniones distritales', 'number'],
                ['reuniones_comites', 'Reuniones con comités varios', 'number'],
                ['analisis_informes', 'Análisis de informes y resultados', 'number'],
                ['revision_libros', 'Oficial distrital revisó y firmó libros', 'boolean'],
            ],
            'Observaciones' => [
                ['observaciones_adicionales', 'Datos adicionales u observaciones', 'textarea'],
            ],
        ];

        foreach ($sections as $sectionIndex => $fields) {
            $section = ReportSection::updateOrCreate(
                [
                    'report_template_id' => $template->id,
                    'title' => $sectionIndex,
                ],
                [
                    'sort_order' => array_search(
                        $sectionIndex,
                        array_keys($sections),
                        true
                    ) + 1,
                ]
            );

            foreach ($fields as $index => $field) {
                ReportField::updateOrCreate(
                    [
                        'report_section_id' => $section->id,
                        'name' => $field[0],
                    ],
                    [
                        'label' => $field[1],
                        'field_type' => $field[2],
                        'is_required' => false,
                        'sort_order' => $index + 1,
                    ]
                );
            }
        }
    }
}

