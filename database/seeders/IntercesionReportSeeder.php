<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntercesionReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $template = ReportTemplate::where(
                'slug',
                'intercesion'
            )->firstOrFail();

            $eventos = [
                ['vigilias', 'Vigilias'],
                ['ayunos', 'Ayunos'],
                ['cultos_intercesion', 'Cultos de intercesión'],
                ['cadenas_oracion', 'Cadenas de oración'],
                ['devocional_amanecer', 'Devocional al amanecer'],
                ['cumbre_intercesores', 'Cumbre de intercesores'],
                ['encuentro_intercesores', 'Encuentro de intercesores'],
                ['eventos_nacionales', 'Eventos nacionales'],
                ['actividades_economicas', 'Actividades económicas'],
                ['visitas', 'Visitas'],
            ];

            $resultados = [
                ['sanidades', 'Sanidades'],
                ['milagros', 'Milagros'],
                ['bautismos_espiritu_santo', 'Bautismos del Espíritu Santo'],
                ['renovados_espiritu_santo', 'Renovados con el Espíritu Santo'],
                ['reconciliados', 'Reconciliados'],
                ['decisiones_fe', 'Decisiones de fe'],
            ];

            $camposEventos = [];
            $camposProgramas = [];

            foreach ($eventos as $evento) {
                $nombre = $evento[0];
                $etiqueta = $evento[1];

                $camposEventos[] = [
                    'evento_' . $nombre . '_cantidad',
                    $etiqueta . ' - Cantidad',
                    'number'
                ];

                $camposEventos[] = [
                    'evento_' . $nombre . '_asistencia',
                    $etiqueta . ' - Asistencia',
                    'number'
                ];

                $camposEventos[] = [
                    'evento_' . $nombre . '_horas_oracion',
                    $etiqueta . ' - Horas aproximadas de oración',
                    'number'
                ];

                $camposProgramas[] = [
                    'programa_' . $nombre . '_cantidad',
                    $etiqueta . ' - Cantidad',
                    'number'
                ];

                $camposProgramas[] = [
                    'programa_' . $nombre . '_asistencia',
                    $etiqueta . ' - Asistencia',
                    'number'
                ];

                $camposProgramas[] = [
                    'programa_' . $nombre . '_horas_oracion',
                    $etiqueta . ' - Horas aproximadas de oración',
                    'number'
                ];
            }

            $camposResultadosEventos = [];
            $camposResultadosProgramas = [];

            foreach ($resultados as $resultado) {
                $camposResultadosEventos[] = [
                    'evento_resultado_' . $resultado[0],
                    $resultado[1],
                    'number'
                ];

                $camposResultadosProgramas[] = [
                    'programa_resultado_' . $resultado[0],
                    $resultado[1],
                    'number'
                ];
            }

            $camposResultadosEventos[] = [
                'otras_experiencias',
                'Otras experiencias de los eventos',
                'textarea'
            ];

            $sections = [
                [
                    'title' => 'Eventos de Intercesión',
                    'fields' => $camposEventos,
                ],
                [
                    'title' => 'Grupos de Intercesión',
                    'fields' => [
                        [
                            'cantidad_grupos_intercesion',
                            '¿Cuántos grupos de intercesión tiene?',
                            'number'
                        ],
                        [
                            'integrantes_grupos_intercesion',
                            '¿Cuántos hermanos integran los grupos?',
                            'number'
                        ],
                    ],
                ],
                [
                    'title' => 'Resultados de los eventos',
                    'fields' => $camposResultadosEventos,
                ],
                [
                    'title' => 'Datos de programas de Intercesión',
                    'fields' => $camposProgramas,
                ],
                [
                    'title' => 'Resultados de los programas',
                    'fields' => $camposResultadosProgramas,
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
