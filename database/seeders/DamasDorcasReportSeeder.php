<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DamasDorcasReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $template = ReportTemplate::where(
                'slug',
                'damas-dorcas'
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
                    'title' => 'Reuniones y coordinación',
                    'fields' => [
                        ['reuniones_comite', 'Reuniones con el comité', 'number'],
                        ['reuniones_pastor_diaconos', 'Reuniones con el Pastor/Diáconos', 'number'],
                        ['reuniones_asesoramiento', 'Reuniones de asesoramiento', 'number'],
                        ['reuniones_comunicaciones', 'Reuniones con Comunicaciones', 'number'],
                        ['reuniones_distritales', 'Reunión distrital', 'number'],
                    ],
                ],
                [
                    'title' => 'Resultados espirituales',
                    'fields' => [
                        ['bautizados_espiritu_santo', 'Bautizados con el Espíritu Santo', 'number'],
                        ['bautizados_agua', 'Bautizados en agua', 'number'],
                        ['decisiones_fe', 'Decisiones de fe', 'number'],
                        ['reconciliados', 'Reconciliados', 'number'],
                    ],
                ],
                [
                    'title' => 'Observaciones',
                    'fields' => [
                        ['observaciones_adicionales', 'Observaciones adicionales', 'textarea'],
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
                            'field_type' => $field[2],
                            'is_required' => false,
                            'sort_order' => $order + 1,
                        ]
                    );
                }
            }
        });
    }
}
