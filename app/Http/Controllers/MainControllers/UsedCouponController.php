<?php

namespace App\Http\Controllers\MainControllers;

use App\Http\Controllers\Controller;
use App\Models\UsedCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsedCouponController extends Controller
{
    // قائمة عامة مع فلاتر اختيارية
    public function index(Request $request)
    {
        $q = UsedCoupon::query()
            ->with(['coupon:id,code,discount', 'customer:id,name,phone', 'order:id'])
            ->when($request->filled('customer_id_fk'), fn($x) => $x->where('customer_id_fk', $request->customer_id_fk))
            ->when($request->filled('coupon_id_fk'),   fn($x) => $x->where('coupon_id_fk',   $request->coupon_id_fk))
            ->when($request->filled('order_service_id_fk'), fn($x) => $x->where('order_service_id_fk', $request->order_service_id_fk))
            ->orderByDesc('used_at');

        return response()->json(['data' => $q->get()]);
    }

    // استخدامات عميل معيّن
    public function byCustomer(string $customerId)
    {
        $items = UsedCoupon::with(['coupon:id,code,discount', 'order:id'])
            ->where('customer_id_fk', $customerId)
            ->orderByDesc('used_at')
            ->get();

        return response()->json(['data' => $items]);
    }

    // استخدامات كوبون معيّن
    public function byCoupon(string $couponId)
    {
        $items = UsedCoupon::with(['customer:id,name,phone', 'order:id'])
            ->where('coupon_id_fk', $couponId)
            ->orderByDesc('used_at')
            ->get();

        return response()->json(['data' => $items]);
    }

    // (اختياري) حذف إداري لسجل الاستخدام
    // أنصح بعدم الحذف، لكن إن احتجته:
    public function destroy(string $id)
    {
        $row = UsedCoupon::findOrFail($id);
        $row->delete(); // لو مفعل SoftDeletes، سيكون سوفت
        return response()->json(['message' => 'usage deleted']);
    }
}
