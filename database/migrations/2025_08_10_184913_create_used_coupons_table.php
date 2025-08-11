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
        Schema::create('used_coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id_fk')->nullable();      // FK بالملف relation
            $table->uuid('coupon_id_fk');                    // FK بالملف relation
            $table->uuid('order_service_id_fk')->nullable(); // FK بالملف relation
               $table->softDeletes(); // For soft delete functionality
            $table->timestamp('used_at')->nullable();

            // unique(customer_id_fk, coupon_id_fk) بالملف relation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('used_coupons');
    }
};
