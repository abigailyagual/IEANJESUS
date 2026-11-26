<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('position')->nullable();
            $table->string('phone')->nullable();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('code')->nullable();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->string('registration_number')->nullable();
            $table->string('document_type')->default('internal');
            $table->string('status')->default('draft');
            $table->string('priority')->default('normal');
            $table->text('summary')->nullable();
            $table->string('external_partner')->nullable();

             $table->foreignId('author_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('approver_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('due_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->text('rejection_reason')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approver_id');
            $table->dropConstrainedForeignId('author_id');
            $table->dropColumn([
                'registration_number',
                'document_type',
                'status',
                'priority',
                'summary',
                'external_partner',
                'due_at',
                'submitted_at',
                'approved_at',
                'archived_at',
                'rejection_reason',
            ]);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['position', 'phone']);
        });
    }
};
