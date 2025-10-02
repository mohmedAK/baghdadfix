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

        // IDs ثابتة بصيغة UUID v4
        $stateBaghdad   = '5d2f1a14-8e42-4d8c-9a3e-2f1a7d8c9b1e';

        $areaKarrada    = 'c8b3f2a7-1d5e-4e84-8c61-7f9b2cb4a0d2';
        $areaMansour    = '9f1c2d3e-7a6b-4b1f-a2c3-58d9e1f0ab34';

        $adminId        = '2a4c7d8e-3f12-4c56-b789-0a1b2c3d4e5f';
        $techId         = 'f0a1b2c3-4d5e-4f60-9abc-1234567890ab';
        $customerId     = '7e9a2b1c-3d4f-4a6b-8c9d-0f1e2d3c4b5a';

        $catBuildId     = 'a3b4c5d6-7890-4abc-8def-0123456789ab';
        $catElectricId  = '0bb3a3dd-2f67-480a-9d21-7a2bc3d4e5f6';

        $servicePaintId = 'd1e2f3a4-b5c6-4d78-98ab-0c1d2e3f4a5b';
        $serviceWireId  = '34ac12ef-56b7-4c89-a012-3b4c5d6e7f80';

        $couponWelcome  = '6a7b8c9d-0e1f-4a23-b456-c7890a1b2c3d';

        // 1) States
        DB::table('states')->insert([
            ['id' => $stateBaghdad, 'name' => 'بغداد', 'is_active' => 1, 'sort_order' => 1],
        ]);

        // 2) Areas
        DB::table('areas')->insert([
            ['id' => $areaKarrada, 'state_id_fk' => $stateBaghdad, 'name' => 'الكرادة', 'is_active' => 1, 'sort_order' => 1],
            ['id' => $areaMansour, 'state_id_fk' => $stateBaghdad, 'name' => 'المنصور', 'is_active' => 1, 'sort_order' => 2],
        ]);

        // 3) Users
        DB::table('users')->insert([
            [
                'id' => $adminId,
                'name' => 'Admin One',
                'email' => 'admin@admin.com',
                'phone' => '07700000001',
                'role' => 'admin',
                'state' => 'بغداد',
                'area' => 'الكرادة',
                'password' => bcrypt('password'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'       => '5d2f1a14-8e42-4d8c-9a3e-2f1a7d8c9b1e',
                'name'     => 'Editor One',
                'email'    => 'editor@editor.com',
                'phone'    => '07700000005',
                'role'     => 'editor',
                'state'    => 'بغداد',
                'area'     => 'الكرادة',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $techId,
                'name' => 'Tech One',
                'email' => '',
                'phone' => '07700000002',
                'role' => 'technical',
                'state' => 'بغداد',
                'area' => 'المنصور',
                'password' => bcrypt('password'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => $customerId,
                'name' => 'Customer One',
                'email' => '',
                'phone' => '07700000003',
                'role' => 'customer',
                'state' => 'البصرة',
                'area' => 'الزبير',
                'password' => bcrypt('password'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // 5) Service Categories
        DB::table('service_categories')->insert([
            ['id' => $catBuildId,    'name' => 'ترميم وبناء',  'image' => null, 'is_active' => 1, 'sort_order' => 1],
            ['id' => $catElectricId, 'name' => 'أعمال كهرباء', 'image' => null, 'is_active' => 1, 'sort_order' => 2],
        ]);

        // 6) Services
        DB::table('services')->insert([
            ['id' => $servicePaintId, 'name' => 'دهان جدران', 'image' => null, 'service_category_id_fk' => $catBuildId,   'is_active' => 1],
            ['id' => $serviceWireId,  'name' => 'تمديد أسلاك', 'image' => null, 'service_category_id_fk' => $catElectricId, 'is_active' => 1],
        ]);

        // 7) Order Services (اختياري – اتركه فارغ الآن)

        // 8) Ratings (اختياري)
        // DB::table('ratings')->insert([
        //     ['id' => '12ab34cd-56ef-4980-8a1b-2c3d4e5f6071', 'order_service_id_fk' => '...', 'rater_id_fk' => $customerId, 'technical_id_fk' => $techId, 'rate' => 5, 'comment' => 'خدمة ممتازة', 'created_at' => $now],
        // ]);

        // 9) Coupons
        DB::table('coupons')->insert([
            ['id' => $couponWelcome, 'code' => 'WELCOME10', 'discount' => 10, 'is_active' => 1, 'starts_at' => $now, 'ends_at' => $now->copy()->addDays(30)],
        ]);

        // 10) Used Coupons (اختياري)
        // DB::table('used_coupons')->insert([
        //     ['id' => '98ba76dc-5432-4f10-9e8d-7c6b5a4f3e21', 'customer_id_fk' => $customerId, 'coupon_id_fk' => $couponWelcome, 'order_service_id_fk' => '...', 'used_at' => $now],
        // ]);
    }
}
