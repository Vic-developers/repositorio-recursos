<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_resource', function (Blueprint $table) {
            $table->uuid('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->uuid('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->primary(['collection_id', 'resource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_resource');
    }
};
