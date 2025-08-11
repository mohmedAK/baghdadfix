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
        Schema::create('services', function (Blueprint $table) {
           $table->uuid('id')->primary();
            $table->string('name', 250);
            $table->string('image', 1000)->nullable();
            $table->uuid('service_category_id_fk'); // FK بالملف relation
            $table->boolean('is_active')->default(true);
            // unique(service_category_id_fk, name) بالملف relation
               $table->softDeletes(); // For soft delete functionality
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
