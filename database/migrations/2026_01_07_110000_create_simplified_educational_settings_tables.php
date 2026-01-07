<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table pour les paramètres par défaut
        Schema::create('default_edu_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('school_type', ['preuniversity', 'university']);
            $table->string('edu_level', 50)->nullable(); // Raccourci pour éviter les problèmes d'index
            $table->string('category', 50);
            $table->string('key', 50);
            $table->json('value');
            $table->boolean('is_required')->default(false);
            $table->json('validation_rules')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Index raccourci pour éviter les problèmes de longueur
            $table->index(['school_type', 'category']);
            $table->index(['key', 'school_type']);
        });

        // Table pour les paramètres spécifiques aux écoles
        Schema::create('school_edu_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->enum('school_type', ['preuniversity', 'university']);
            $table->string('edu_level', 50)->nullable();
            $table->string('category', 50);
            $table->string('key', 50);
            $table->json('value');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamps();

            // Index raccourci
            $table->index(['school_id', 'category']);
            $table->index(['school_type', 'key']);
            
            // Foreign keys
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
        });

        // Table d'audit
        Schema::create('edu_settings_audit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('setting_id');
            $table->enum('action', ['create', 'update', 'delete']);
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['setting_id', 'changed_at']);
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('edu_settings_audit');
        Schema::dropIfExists('school_edu_settings');
        Schema::dropIfExists('default_edu_settings');
    }
};