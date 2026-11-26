
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_section_id')
                ->constrained('report_sections')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('label');
            $table->string('field_type')->default('number');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['report_section_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_fields');
    }
};
