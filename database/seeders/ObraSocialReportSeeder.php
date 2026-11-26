<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObraSocialReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $template = ReportTemplate::where(
                'slug',
                'obra-social'
            )->firstOrFail();

            $sections = [
                [
                    'title' => 'Causas atendidas',
                    'fields' => [
                        ['operaciones_damnificados', 'Operaciones / Damnificados', 'number'],
                        ['tratamientos_medicos', 'Tratamientos médicos', 'number'],
                        ['canastas_entregadas', 'Canastas entregadas', 'number'],
                        ['calamidades_domesticas', 'Calamidades domésticas', 'number'],
                        ['exequias', 'Exequias', 'number'],
                        ['atencion_discapacitados', 'Atención a discapacitados', 'number'],
                        ['brigadas_medicas', 'Brigadas médicas', 'number'],
                    ],
                ],
                [
                    'title' => 'Actividades espirituales',
                    'fields' => [
                        ['cadenas_oracion', 'Cadenas de oración', 'number'],
                        ['ayunos', 'Ayunos', 'number'],
                        ['cultos', 'Cultos', 'number'],
                        ['vigilias', 'Vigilias', 'number'],
                        ['mes_semana_obra_social', 'Mes o semana de Obra Social', 'number'],
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
                        ['descripcion_economicas', 'Describa las actividades económicas más destacadas', 'textarea'],
                    ],
                ],
                [
                    'title' => 'Visitas y asistencia',
                    'fields' => [
                        ['visitas', 'Visitas realizadas', 'number'],
                        ['detalle_visitas', 'Detalle de las visitas', 'textarea'],
                        ['promedio_asistencia_general', 'Promedio de asistencia general', 'number'],
                    ],
                ],
                [
                    'title' => 'Reuniones y coordinación',
                    'fields' => [
                        ['reuniones_comite', 'Reuniones con el comité', 'number'],
                        ['reuniones_pastor_diaconos', 'Reuniones con el Pastor/Diáconos', 'number'],
                        ['reuniones_asesoria', 'Reuniones de asesoría', 'number'],
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
