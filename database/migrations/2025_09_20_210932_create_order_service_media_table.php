<?php

// database/migrations/2025_09_21_000002_create_order_service_media_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_service_media', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('order_service_id_fk');
            $table->enum('type', ['image','video']);
            $table->string('file_path', 1000);
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable(); // للفيديو فقط
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->uuid('uploaded_by_user_id_fk')->nullable();

            $table->timestamps();
            $table->softDeletes();


            $table->index(['order_service_id_fk','sort_order'], 'ix_media_order_sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_service_media');
    }
};
