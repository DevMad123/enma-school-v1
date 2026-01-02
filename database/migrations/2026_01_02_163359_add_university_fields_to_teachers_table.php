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
        Schema::table('teachers', function (Blueprint $table) {
            // Vérifier et ajouter seulement les colonnes qui n'existent pas
            if (!Schema::hasColumn('teachers', 'ufr_id')) {
                $table->foreignId('ufr_id')->nullable()->constrained('u_f_r_s')->onDelete('set null');
            }
            if (!Schema::hasColumn('teachers', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            }
            if (!Schema::hasColumn('teachers', 'academic_rank')) {
                $table->string('academic_rank')->nullable();
                $table->index('academic_rank');
            }
            if (!Schema::hasColumn('teachers', 'research_interests')) {
                $table->text('research_interests')->nullable();
            }
            if (!Schema::hasColumn('teachers', 'office_location')) {
                $table->string('office_location')->nullable();
            }
            if (!Schema::hasColumn('teachers', 'salary')) {
                $table->decimal('salary', 10, 2)->nullable();
            }
            
            // Ajouter un index composé si les colonnes ufr_id et department_id existent
            if (Schema::hasColumn('teachers', 'ufr_id') && Schema::hasColumn('teachers', 'department_id')) {
                $table->index(['ufr_id', 'department_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // Supprimer les foreign keys et index si les colonnes existent
            if (Schema::hasColumn('teachers', 'ufr_id')) {
                $table->dropForeign(['ufr_id']);
            }
            if (Schema::hasColumn('teachers', 'department_id')) {
                $table->dropForeign(['department_id']);
            }
            
            // Supprimer les colonnes
            $columnsToRemove = [];
            if (Schema::hasColumn('teachers', 'ufr_id')) {
                $columnsToRemove[] = 'ufr_id';
            }
            if (Schema::hasColumn('teachers', 'department_id')) {
                $columnsToRemove[] = 'department_id';
            }
            if (Schema::hasColumn('teachers', 'academic_rank')) {
                $columnsToRemove[] = 'academic_rank';
            }
            if (Schema::hasColumn('teachers', 'research_interests')) {
                $columnsToRemove[] = 'research_interests';
            }
            if (Schema::hasColumn('teachers', 'office_location')) {
                $columnsToRemove[] = 'office_location';
            }
            if (Schema::hasColumn('teachers', 'salary')) {
                $columnsToRemove[] = 'salary';
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};
