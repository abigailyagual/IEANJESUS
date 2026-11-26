
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('monthly_report_id')
                ->constrained('monthly_reports')
                ->cascadeOnDelete();

            $table->foreignId('report_field_id')
                ->constrained('report_fields')
                ->restrictOnDelete();

            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique([
                'monthly_report_id',
                'report_field_id'
            ], 'report_values_report_field_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_values');
    }
};
