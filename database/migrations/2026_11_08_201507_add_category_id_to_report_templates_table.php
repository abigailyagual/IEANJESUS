<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_templates', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->restrictOnDelete();
        });

        // Asociación de las 14 plantillas existentes
        // con sus departamentos correspondientes.
        $mapping = [
            'pastor' => 1,
            'junta-local' => 2,
            'escuela-dominical' => 3,
            'jovenes' => 4,
            'damas-dorcas' => 5,
            'musica' => 6,
            'misiones' => 7,
            'obra-social' => 8,
            'comunicaciones' => 9,
            'protemplo' => 10,
            'intercesion' => 11,
            'refam' => 12,
            'lugares-predicacion' => 13,
            'ministerio-estudiantil' => 14,
        ];

        foreach ($mapping as $slug => $categoryId) {
            DB::table('report_templates')
                ->where('slug', $slug)
                ->update(['category_id' => $categoryId]);
        }
    }

    public function down(): void
    {
        Schema::table('report_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
