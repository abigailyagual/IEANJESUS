
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('report_template_id')
                ->constrained('report_templates')
                ->restrictOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('report_month');
            $table->unsignedSmallInteger('report_year');

            $table->string('status')->default('draft');
            $table->text('observations')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'report_year',
                'report_month',
                'category_id'
            ], 'monthly_reports_period_department_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_reports');
    }
};
