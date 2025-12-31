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
        Schema::table('levels', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->nullable()->after('school_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('code')->nullable()->after('name'); // L1, M2, TLE
            $table->enum('type', ['secondary', 'university'])->default('secondary')->after('code');
            $table->integer('order')->default(0)->after('type');
            $table->boolean('is_active')->default(true)->after('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn(['school_id', 'academic_year_id', 'code', 'type', 'order', 'is_active']);
        });
    }
};
