<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_statistics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('resource_id')->constrained('resources')->cascadeOnDelete()->unique();
            $table->integer('views_count')->default(0);
            $table->integer('unique_users')->default(0);
            $table->integer('total_courses')->default(0);
            $table->integer('total_institutions')->default(0);
            $table->float('average_time_seconds')->default(0);
            $table->timestamp('last_access_at')->nullable();
            $table->json('views_by_day')->nullable();
            $table->json('views_by_device')->nullable();
            $table->json('views_by_browser')->nullable();
            $table->json('views_by_country')->nullable();
            $table->json('views_by_os')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_statistics');
    }
};
