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
        Schema::create('order_services', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('customer_id_fk');               // FK بالملف relation
            $table->uuid('service_id_fk');                // FK بالملف relation

            $table->uuid('technical_id_fk')->nullable();  // FK بالملف relation
            $table->uuid('assigned_by_admin_id_fk')->nullable(); // FK

            $table->timestamp('assigned_at')->nullable();
            $table->string('assignment_note', 500)->nullable();

            // الموقع
            $table->uuid('state_id_fk')->nullable();      // FK بالملف relation
            $table->uuid('area_id_fk')->nullable();       // FK بالملف relation
            $table->decimal('gps_lat', 9, 6)->nullable();
            $table->decimal('gps_lng', 9, 6)->nullable();

            // السعر الابتدائي من الأدمن
            $table->decimal('admin_initial_price', 12, 2)->nullable();
            $table->uuid('admin_initial_by_id_fk')->nullable(); // FK
            $table->timestamp('admin_initial_at')->nullable();
            $table->string('admin_initial_note', 500)->nullable();

            // السعر النهائي (اختياري)
            $table->decimal('final_price', 12, 2)->nullable();

            $table->text('description')->nullable();

            $table->enum('status', [
                'created','admin_estimated','assigned','inspecting',
                'quote_pending','awaiting_customer_approval',
                'approved','rejected','in_progress','completed','canceled'
            ])->default('created');

            $table->boolean('submit')->default(false);

            $table->string('image', 1000)->nullable();
            $table->string('video', 1000)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_services');
    }
};
