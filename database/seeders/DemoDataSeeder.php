<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1) States
        DB::table('states')->insert([
            ['id' => '11111111-1111-1111-1111-111111111111', 'name' => 'بغداد', 'is_active' => 1, 'sort_order' => 1],

        ]);

        // 2) Areas
        DB::table('areas')->insert([
            ['id' => 'aaaa1111-1111-1111-1111-aaaaaaaaaaaa', 'state_id_fk' => '11111111-1111-1111-1111-111111111111', 'name' => 'الكرادة', 'is_active' => 1, 'sort_order' => 1],
            ['id' => 'bbbb2222-2222-2222-2222-bbbbbbbbbbbb', 'state_id_fk' => '11111111-1111-1111-1111-111111111111', 'name' => 'المنصور', 'is_active' => 1, 'sort_order' => 2],

        ]);

        // 3) Users
        DB::table('users')->insert([
            ['id' => 'u1u1u1u1-1111-1111-1111-111111111111', 'name' => 'Admin One', 'email' => 'admin@admin.com', 'phone' => '07700000001', 'role' => 'admin', 'state' => 'بغداد', 'area' => 'الكرادة', 'password' => bcrypt('password'), 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'u2u2u2u2-2222-2222-2222-222222222222', 'name' => 'Tech One', 'email' => 'tech1@tech.com', 'phone' => '07700000002', 'role' => 'technical', 'state' => 'بغداد', 'area' => 'المنصور', 'password' => bcrypt('password'), 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'u3u3u3u3-3333-3333-3333-333333333333', 'name' => 'Customer One', 'email' => '', 'phone' => '07700000003', 'role' => 'customer', 'state' => 'البصرة', 'area' => 'الزبير', 'password' => bcrypt('password'), 'created_at' => $now, 'updated_at' => $now],
        ]);


        // 5) Service Categories
        DB::table('service_categories')->insert([
            ['id' => 'scat1111-1111-1111-1111-scat11111111', 'name' => 'ترميم وبناء', 'image' => null, 'is_active' => 1, 'sort_order' => 1],
            ['id' => 'scat2222-2222-2222-2222-scat22222222', 'name' => 'أعمال كهرباء', 'image' => null, 'is_active' => 1, 'sort_order' => 2],
        ]);

        // 6) Services
        DB::table('services')->insert([
            ['id' => 'serv1111-1111-1111-1111-serv11111111', 'name' => 'دهان جدران', 'image' => null, 'service_category_id_fk' => 'scat1111-1111-1111-1111-scat11111111', 'is_active' => 1],
            ['id' => 'serv2222-2222-2222-2222-serv22222222', 'name' => 'تمديد أسلاك', 'image' => null, 'service_category_id_fk' => 'scat2222-2222-2222-2222-scat22222222', 'is_active' => 1],
        ]);

        // 7) Order Services


        // 8) Ratings
        // DB::table('ratings')->insert([
        //     ['id' => 'rate1111-1111-1111-1111-rate11111111', 'order_service_id_fk' => 'ord11111-1111-1111-1111-ord111111111', 'rater_id_fk' => 'u3u3u3u3-3333-3333-3333-333333333333', 'technical_id_fk' => 'u2u2u2u2-2222-2222-2222-222222222222', 'rate' => 5, 'comment' => 'خدمة ممتازة', 'created_at' => $now],
        // ]);

        // 9) Coupons
        DB::table('coupons')->insert([
            ['id' => 'coup1111-1111-1111-1111-coup11111111', 'code' => 'WELCOME10', 'discount' => 10, 'is_active' => 1, 'starts_at' => $now, 'ends_at' => $now->copy()->addDays(30)],
        ]);

        // 10) Used Coupons
        // DB::table('used_coupons')->insert([
        //     ['id' => 'used1111-1111-1111-1111-used11111111', 'customer_id_fk' => 'u3u3u3u3-3333-3333-3333-333333333333', 'coupon_id_fk' => 'coup1111-1111-1111-1111-coup11111111', 'order_service_id_fk' => 'ord11111-1111-1111-1111-ord111111111', 'used_at' => $now],
        // ]);
    }
}
