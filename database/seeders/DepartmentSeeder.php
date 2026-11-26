<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Pastoral', 'code' => 'PAS'],
            ['name' => 'Junta Local', 'code' => 'JUN'],
            ['name' => 'Escuela Dominical', 'code' => 'EDO'],
            ['name' => 'Comité de Jóvenes', 'code' => 'JOV'],
            ['name' => 'Damas Dorcas', 'code' => 'DOR'],
            ['name' => 'Comité de Música', 'code' => 'MUS'],
            ['name' => 'Misiones', 'code' => 'MIS'],
            ['name' => 'Obra Social', 'code' => 'OBS'],
            ['name' => 'Comunicaciones', 'code' => 'COM'],
            ['name' => 'Comité Protemplo', 'code' => 'PRO'],
            ['name' => 'Intercesión', 'code' => 'INT'],
            ['name' => 'REFAM', 'code' => 'REF'],
            ['name' => 'Lugares de Predicación', 'code' => 'LDP'],
            ['name' => 'Ministerio Estudiantil', 'code' => 'EST'],
        ];

        foreach ($departments as $department) {
            Category::updateOrCreate(
                ['code' => $department['code']],
                ['name' => $department['name']]
            );
        }
    }
}
