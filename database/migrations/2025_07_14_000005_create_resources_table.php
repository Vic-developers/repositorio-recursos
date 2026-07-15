<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->uuid('folder_id')->nullable()->constrained('folders')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('type')->default('Other');
            $table->string('status')->default('Published');
            $table->string('version')->default('1.0');
            $table->string('original_file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size_bytes')->default(0);
            $table->string('file_size_display')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('duration')->nullable();
            $table->string('language')->default('es');
            $table->string('level')->nullable();
            $table->string('area')->nullable();
            $table->text('competencies')->nullable();
            $table->text('learning_outcomes')->nullable();
            $table->integer('estimated_time_minutes')->default(0);
            $table->string('author_name')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->integer('usage_count')->default(0);
            $table->boolean('is_favorite')->default(false);
            $table->string('uuid')->unique();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
