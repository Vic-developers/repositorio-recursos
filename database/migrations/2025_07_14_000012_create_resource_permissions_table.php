<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('role_name')->nullable();
            $table->string('institution')->nullable();
            $table->string('department')->nullable();
            $table->string('permission_level');
            $table->boolean('is_granted')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_permissions');
    }
};
