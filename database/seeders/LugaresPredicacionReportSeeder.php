<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LugaresPredicacionReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $template = ReportTemplate::where(
                'slug',
                'lugares-predicacion'
            )->firstOrFail();

            $sections = [
                [
                    'title' => 'Actividades espirituales',
                    'fields' => [
                        ['ayunos_locales', 'Ayunos locales', 'number'],
                        ['cadenas_oracion', 'Cadenas de oración', 'number'],
                        ['evangelizaciones', 'Evangelizaciones', 'number'],
                        ['otras_actividades_espirituales', 'Otras actividades espirituales', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Actividades sociales',
                    'fields' => [
                        ['agasajos_cumpleanos', 'Agasajos por cumpleaños', 'number'],
                        ['visitas_sociales', 'Visitas sociales', 'number'],
                        ['otras_actividades_sociales', 'Otras actividades sociales', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Actividades económicas',
                    'fields' => [
                        ['ventas_generales', 'Ventas en general (actividades económicas)', 'number'],
                        ['votos_talentos', 'Votos encargados del Lugar de Predicación / Talento', 'number'],
                        ['otras_actividades_economicas', 'Otras actividades económicas', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Asistencia y resultados espirituales',
                    'fields' => [
                        ['promedio_asistencia_culto', 'Promedio de asistencia al culto', 'number'],
                        ['decisiones_fe', 'Decisiones de fe', 'number'],
                        ['refam_abiertas', 'REFAM abiertas', 'number'],
                    ],
                ],
                [
                    'title' => 'Reuniones y coordinación',
                    'fields' => [
                        ['reuniones_encargados', 'Reuniones con encargados', 'number'],
                        ['reuniones_pastor_diaconos', 'Reuniones con el Pastor/Diáconos', 'number'],
                        ['reuniones_asesoramiento', 'Reuniones de asesoramiento', 'number'],
                        ['reuniones_misiones', 'Reuniones con Misiones', 'number'],
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
