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
        Schema::create('ratings', function (Blueprint $table) {
           $table->uuid('id')->primary();
            $table->uuid('order_service_id_fk'); // FK بالملف relation
            $table->uuid('rater_id_fk');         // FK بالملف relation
            $table->uuid('technical_id_fk');     // FK بالملف relation
            $table->integer('rate');
            $table->text('comment')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
