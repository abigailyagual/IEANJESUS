<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComunicacionesReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $template = ReportTemplate::where(
                'slug',
                'comunicaciones'
            )->firstOrFail();

            $sections = [
                [
                    'title' => 'Actividades espirituales',
                    'fields' => [
                        ['cadenas_oracion', 'Cadenas de oración', 'number'],
                        ['ayunos', 'Ayunos', 'number'],
                        ['cultos', 'Cultos', 'number'],
                        ['vigilias', 'Vigilias', 'number'],
                        ['descripcion_espirituales', 'Describa las actividades espirituales más destacadas', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Actividades sociales',
                    'fields' => [
                        ['integraciones', 'Integraciones', 'number'],
                        ['mesas_redondas', 'Mesas redondas', 'number'],
                        ['cumpleanos', 'Cumpleaños', 'number'],
                        ['recreacion_integracion', 'Recreación e integración', 'number'],
                        ['seminarios', 'Seminarios', 'number'],
                        ['descripcion_sociales', 'Describa las actividades sociales más destacadas', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Actividades económicas',
                    'fields' => [
                        ['ventas', 'Ventas', 'number'],
                        ['votos', 'Votos', 'number'],
                        ['talentos', 'Talentos', 'number'],
                        ['donaciones_terceros', 'Donaciones por terceros', 'number'],
                    ],
                ],
                [
                    'title' => 'Transmisiones en línea',
                    'fields' => [
                        ['transmisiones_facebook', 'Transmisiones en Facebook', 'number'],
                        ['transmisiones_youtube', 'Transmisiones en YouTube', 'number'],
                        ['visualizaciones_youtube', 'Visualizaciones en YouTube', 'number'],
                        ['invitaciones', 'Invitaciones realizadas', 'number'],
                    ],
                ],
                [
                    'title' => 'Estadísticas de redes sociales',
                    'fields' => [
                        ['publicidades_redes', 'Publicidades en redes sociales', 'number'],
                        ['pagina_facebook_alcanzadas', 'Página de Facebook: personas alcanzadas', 'number'],
                        ['pagina_facebook_comparticiones', 'Página de Facebook: comparticiones', 'number'],
                        ['pagina_facebook_interacciones', 'Página de Facebook: interacciones', 'number'],
                        ['cuenta_facebook_alcanzadas', 'Cuenta de Facebook: personas alcanzadas', 'number'],
                        ['cuenta_facebook_comparticiones', 'Cuenta de Facebook: comparticiones', 'number'],
                        ['cuenta_facebook_interacciones', 'Cuenta de Facebook: interacciones', 'number'],
                    ],
                ],
                [
                    'title' => 'Reuniones y coordinación',
                    'fields' => [
                        ['reuniones_comite', 'Reuniones con el comité', 'number'],
                        ['reuniones_pastor_diaconos', 'Reuniones con el Pastor/Diáconos', 'number'],
                        ['reuniones_comites', 'Reuniones con los comités', 'number'],
                        ['reuniones_corresponsales', 'Reuniones con los corresponsales', 'number'],
                        ['reuniones_asesoria', 'Reuniones de asesoría', 'number'],
                        ['reunion_distrital', 'Reunión distrital', 'number'],
                    ],
                ],
                [
                    'title' => 'Asistencia y resultados espirituales',
                    'fields' => [
                        ['promedio_asistencia_culto', 'Promedio de asistencia al culto', 'number'],
                        ['decisiones_fe', 'Decisiones de fe', 'number'],
                        ['bautizados_espiritu_santo', 'Bautizados con el Espíritu Santo', 'number'],
                        ['reconciliados', 'Reconciliados', 'number'],
                        ['renovados_espiritu_santo', 'Renovados con el poder del Espíritu Santo', 'number'],
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

