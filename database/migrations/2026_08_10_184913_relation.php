<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        // area ⇢ state
        Schema::table('areas', function (Blueprint $table) {
            $table->foreign('state_id_fk')->references('id')->on('states')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->unique(['state_id_fk','name'], 'uq_area_state_name');
        });

        // otp ⇢ user
        Schema::table('otp', function (Blueprint $table) {
            $table->foreign('user_id_fk')->references('id')->on('users')
                  ->cascadeOnUpdate()->cascadeOnDelete();

            $table->index(['user_id_fk','code'], 'idx_otp_user_code');
        });

        // service ⇢ service_category
        Schema::table('services', function (Blueprint $table) {
            $table->foreign('service_category_id_fk')->references('id')->on('service_categories')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->unique(['service_category_id_fk','name'], 'uq_service_name_in_category');
        });

        // order_service ⇢ user/service/state/area
        Schema::table('order_services', function (Blueprint $table) {
            $table->foreign('customer_id_fk')->references('id')->on('users')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('service_id_fk')->references('id')->on('services')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('technical_id_fk')->references('id')->on('users')
                  ->cascadeOnUpdate()->nullOnDelete();

            $table->foreign('assigned_by_admin_id_fk')->references('id')->on('users')
                  ->cascadeOnUpdate()->nullOnDelete();

            $table->foreign('state_id_fk')->references('id')->on('states')
                  ->cascadeOnUpdate()->nullOnDelete();

            $table->foreign('area_id_fk')->references('id')->on('areas')
                  ->cascadeOnUpdate()->nullOnDelete();

            $table->foreign('admin_initial_by_id_fk')->references('id')->on('users')
                  ->cascadeOnUpdate()->nullOnDelete();
        });

        // rating ⇢ order_service/user
        Schema::table('ratings', function (Blueprint $table) {
            $table->foreign('order_service_id_fk')->references('id')->on('order_services')
                  ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('rater_id_fk')->references('id')->on('users')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('technical_id_fk')->references('id')->on('users')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->unique('order_service_id_fk', 'uq_rating_order');
        });

        // used_coupon ⇢ coupon/user/order_service
        Schema::table('used_coupons', function (Blueprint $table) {
            $table->foreign('coupon_id_fk')->references('id')->on('coupons')
                  ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('customer_id_fk')->references('id')->on('users')
                  ->cascadeOnUpdate()->nullOnDelete();

            $table->foreign('order_service_id_fk')->references('id')->on('order_services')
                  ->cascadeOnUpdate()->nullOnDelete();

            $table->unique(['customer_id_fk','coupon_id_fk'], 'uq_user_coupon_once');
        });
    }

    public function down(): void {

        // Drop constraints in reverse order

        Schema::table('used_coupons', function (Blueprint $table) {
            $table->dropUnique('uq_user_coupon_once');
            $table->dropForeign(['coupon_id_fk']);
            $table->dropForeign(['customer_id_fk']);
            $table->dropForeign(['order_service_id_fk']);
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropUnique('uq_rating_order');
            $table->dropForeign(['order_service_id_fk']);
            $table->dropForeign(['rater_id_fk']);
            $table->dropForeign(['technical_id_fk']);
        });

        Schema::table('order_services', function (Blueprint $table) {
            $table->dropForeign(['customer_id_fk']);
            $table->dropForeign(['service_id_fk']);
            $table->dropForeign(['technical_id_fk']);
            $table->dropForeign(['assigned_by_admin_id_fk']);
            $table->dropForeign(['state_id_fk']);
            $table->dropForeign(['area_id_fk']);
            $table->dropForeign(['admin_initial_by_id_fk']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique('uq_service_name_in_category');
            $table->dropForeign(['service_category_id_fk']);
        });

        Schema::table('otp', function (Blueprint $table) {
            $table->dropIndex('idx_otp_user_code');
            $table->dropForeign(['user_id_fk']);
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->dropUnique('uq_area_state_name');
            $table->dropForeign(['state_id_fk']);
        });
    }
};
