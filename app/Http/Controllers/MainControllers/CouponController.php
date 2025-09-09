<?php

namespace App\Http\Controllers\MainControllers;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\UsedCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class CouponController extends Controller
{
    // قائمة الكوبونات (فلاتر اختيارية)
    public function index(Request $request)
    {
        $q = Coupon::query()
            ->when($request->boolean('only_active'), fn($x) => $x->where('is_active', true))
            ->when($request->boolean('valid_now'), function ($x) {
                $now = now();
                $x->where('is_active', true)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                    });
            })
            ->orderByDesc('created_at');

        return response()->json(['data' => $q->get()]);
    }

    // إنشاء كوبون
    public function store(Request $request)
    {
        $v = Validator::make(
            $request->all(),
            [
                'code'      => 'required|string|max:250|unique:coupons,code',
                'discount'  => 'required|integer|min:1|max:100', // نسبة مئوية
                'is_active' => 'nullable|boolean',
                'starts_at' => 'nullable|date',
                'ends_at'   => 'nullable|date|after_or_equal:starts_at',
            ],
            [
                'code.required'     => 'كود الكوبون مطلوب.',
                'code.unique'       => 'الكود مستخدم مسبقًا.',
                'discount.required' => 'نسبة الخصم مطلوبة.',
                'discount.min'      => 'الخصم يجب أن يكون 1% على الأقل.',
                'discount.max'      => 'الخصم لا يتجاوز 100%.',
                'ends_at.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البداية.',
            ]
        );

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $data = $v->validated();

        $coupon = Coupon::create([
            'code'      => trim($data['code']),
            'discount'  => $data['discount'],
            'is_active' => $data['is_active'] ?? true,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at'   => $data['ends_at'] ?? null,
        ]);

        return response()->json(['message' => 'تم إنشاء الكوبون.', 'data' => $coupon], 201);
    }

    // تعديل كوبون
    public function update(Request $request, string $id)
    {
        $coupon = Coupon::findOrFail($id);

        $v = Validator::make(
            $request->all(),
            [
                'code'      => ['required', 'string', 'max:250', Rule::unique('coupons', 'code')->ignore($coupon->id)],
                'discount'  => 'required|integer|min:1|max:100',
                'is_active' => 'nullable|boolean',
                'starts_at' => 'nullable|date',
                'ends_at'   => 'nullable|date|after_or_equal:starts_at',
            ],
            [
                'code.required'     => 'كود الكوبون مطلوب.',
                'code.unique'       => 'الكود مستخدم مسبقًا.',
            ]
        );

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $data = $v->validated();

        $coupon->update([
            'code'      => trim($data['code']),
            'discount'  => $data['discount'],
            'is_active' => array_key_exists('is_active', $data) ? (bool)$data['is_active'] : $coupon->is_active,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at'   => $data['ends_at'] ?? null,
        ]);

        return response()->json(['message' => 'تم التعديل.', 'data' => $coupon]);
    }

    // حذف سوفت
    public function destroy(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();
        return response()->json(['message' => 'تم حذف الكوبون (Soft Delete).']);
    }

    // المحذوفات فقط
    public function deleted()
    {
        return response()->json(['data' => Coupon::onlyTrashed()->orderByDesc('deleted_at')->get()]);
    }

    // الكل مع المحذوف
    public function allWithTrashed()
    {
        return response()->json(['data' => Coupon::withTrashed()->orderByDesc('created_at')->get()]);
    }

    // استرجاع
    public function restore(string $id)
    {
        $coupon = Coupon::onlyTrashed()->findOrFail($id);
        $coupon->restore();
        return response()->json(['message' => 'تم الاسترجاع.', 'data' => $coupon]);
    }

    // التحقق/تطبيق الكوبون على طلب
    public function apply(Request $request)
    {
        $v = Validator::make(
            $request->all(),
            [
                'code'               => 'required|string',
                'customer_id_fk'     => 'required|uuid|exists:users,id',
                'order_service_id_fk' => 'nullable|uuid|exists:order_services,id',
                'subtotal'           => 'required|numeric|min:0', // المبلغ قبل الخصم
            ],
            [
                'code.required'           => 'الكود مطلوب.',
                'customer_id_fk.required' => 'العميل مطلوب.',
                'subtotal.required'       => 'المبلغ مطلوب.',
            ]
        );
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $data = $v->validated();
        $now  = now();

        // ابحث عن الكوبون
        $coupon = Coupon::where('code', trim($data['code']))->first();
        if (!$coupon) {
            return response()->json(['errors' => ['code' => ['الكود غير صحيح.']]], 422);
        }

        // صالحية وتواريخ
        if (!$coupon->is_active) {
            return response()->json(['errors' => ['code' => ['الكوبون غير مفعل.']]], 422);
        }
        if ($coupon->starts_at && $coupon->starts_at->gt($now)) {
            return response()->json(['errors' => ['code' => ['الكوبون غير فعّال بعد.']]], 422);
        }
        if ($coupon->ends_at && $coupon->ends_at->lt($now)) {
            return response()->json(['errors' => ['code' => ['انتهت صلاحية الكوبون.']]], 422);
        }

        // تحقق أنه غير مُستخدم سابقًا من نفس العميل (حسب قيدك uq_user_coupon_once)
        $already = UsedCoupon::where('customer_id_fk', $data['customer_id_fk'])
            ->where('coupon_id_fk', $coupon->id)
            ->exists();
        if ($already) {
            return response()->json(['errors' => ['code' => ['تم استخدام هذا الكوبون من قبل.']]], 422);
        }

        // حساب الخصم
        $subtotal = (float)$data['subtotal'];
        $discountAmount = round($subtotal * ($coupon->discount / 100), 2);
        $total = max(0, $subtotal - $discountAmount);

        // تسجيل الاستخدام (اختياري تربطه بطلب)
        UsedCoupon::create([
            'customer_id_fk'      => $data['customer_id_fk'],
            'coupon_id_fk'        => $coupon->id,
            'order_service_id_fk' => $data['order_service_id_fk'] ?? null,
            'used_at'             => $now,
        ]);

        return response()->json([
            'message'         => 'تم تطبيق الكوبون.',
            'coupon'          => $coupon,
            'subtotal'        => $subtotal,
            'discount_percent' => $coupon->discount,
            'discount_amount' => $discountAmount,
            'total'           => $total,
        ]);
    }
}
