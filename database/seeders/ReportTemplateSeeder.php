<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportTemplate;

class ReportTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['name' => 'Informe mensual del Pastor', 'slug' => 'pastor'],
            ['name' => 'Informe de Junta Local', 'slug' => 'junta-local'],
            ['name' => 'Informe de Escuela Dominical', 'slug' => 'escuela-dominical'],
            ['name' => 'Informe del Comité de Jóvenes', 'slug' => 'jovenes'],
            ['name' => 'Informe de Damas Dorcas', 'slug' => 'damas-dorcas'],
            ['name' => 'Informe del Comité de Música', 'slug' => 'musica'],
            ['name' => 'Informe de Misiones', 'slug' => 'misiones'],
            ['name' => 'Informe de Obra Social', 'slug' => 'obra-social'],
            ['name' => 'Informe de Comunicaciones', 'slug' => 'comunicaciones'],
            ['name' => 'Informe del Comité Protemplo', 'slug' => 'protemplo'],
            ['name' => 'Informe de Intercesión', 'slug' => 'intercesion'],
            ['name' => 'Informe de REFAM', 'slug' => 'refam'],
            ['name' => 'Informe de Lugares de Predicación', 'slug' => 'lugares-predicacion'],
            ['name' => 'Informe de Ministerio Estudiantil', 'slug' => 'ministerio-estudiantil'],
        ];

        foreach ($templates as $template) {
            ReportTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                [
                    'name' => $template['name'],
                    'description' => 'Formato institucional de informe mensual IEANJESÚS',
                    'is_active' => true,
                ]
            );
        }
    }
}
