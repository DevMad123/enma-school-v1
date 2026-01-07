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
        // Table principale des configurations éducatives
        Schema::create('educational_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->enum('school_type', ['preuniversity', 'university']);
            $table->string('educational_level', 30)->nullable(); // prescolaire, primaire, college, lycee, licence, master, doctorat
            $table->string('setting_category', 50); // age_limits, documents, fees, evaluation, lmd_standards
            $table->string('setting_key', 50);
            $table->json('setting_value');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Index uniques et optimisations
            $table->unique(['school_id', 'school_type', 'educational_level', 'setting_category', 'setting_key'], 'edu_settings_unique');
            $table->index(['school_type', 'educational_level'], 'idx_school_type_level');
            $table->index(['setting_category', 'setting_key'], 'idx_category_key');
            $table->index('is_active', 'idx_is_active');
        });

        // Table des templates de configuration par défaut
        Schema::create('default_educational_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('school_type', ['preuniversity', 'university']);
            $table->string('educational_level', 30)->nullable();
            $table->string('setting_category', 50);
            $table->string('setting_key', 50);
            $table->json('setting_value');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('validation_rules')->nullable(); // Règles de validation pour les valeurs
            $table->timestamps();
            
            $table->unique(['school_type', 'educational_level', 'setting_category', 'setting_key'], 'default_edu_settings_unique');
            $table->index(['school_type', 'setting_category'], 'idx_type_category');
        });

        // Table d'audit des changements de configuration
        Schema::create('educational_settings_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_id')->constrained('educational_settings')->cascadeOnDelete();
            $table->enum('action', ['create', 'update', 'delete']);
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->foreignId('changed_by')->constrained('users');
            $table->timestamp('changed_at')->useCurrent();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->index(['setting_id', 'changed_at'], 'idx_setting_date');
            $table->index('changed_by', 'idx_changed_by');
            $table->index('action', 'idx_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educational_settings_audit');
        Schema::dropIfExists('default_educational_settings');
        Schema::dropIfExists('educational_settings');
    }
};