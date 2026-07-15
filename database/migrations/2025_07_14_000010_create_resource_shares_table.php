<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->string('token')->unique();
            $table->string('type');
            $table->string('permission_level')->default('View');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->integer('max_access_count')->nullable();
            $table->integer('access_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_shares');
    }
};
