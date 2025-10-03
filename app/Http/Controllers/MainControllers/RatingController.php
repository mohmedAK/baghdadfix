<?php

namespace App\Http\Controllers\MainControllers;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\OrderService;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    /**
     * الزبون يُقيم الطلب (إن وُجد تقييم سيتم تحديثه)
     */
    public function storeOrUpdate(Request $request, OrderService $order): JsonResponse
    {
        $user = $request->user();

        // 1) تحقق الدور والملكية
        if ($user->role !== UserRole::Customer || $order->customer_id_fk !== $user->id) {
            abort(403, 'Only the customer who owns the order can rate it.');
        }

        // 2) تحقق الحالة (عدّلها إن أردت)
        if (! in_array($order->status->value ?? $order->status, ['completed', 'approved'])) {
            abort(422, 'Order is not rateable yet.');
        }

        // 3) يجب أن يكون هناك فنّي للطلب
        if (! $order->technical_id_fk) {
            abort(422, 'Order has no assigned technician.');
        }

        // 4) فالديشن
        $v = Validator::make($request->all(), [
            'rate'    => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $data = $v->validated();

        // 5) حفظ/تحديث التقييم (لدينا unique على order_service_id_fk)
        $rating = Rating::updateOrCreate(
            ['order_service_id_fk' => $order->id], // المفتاح الفريد
            [
                'rater_id_fk'      => $user->id,
                'technical_id_fk'  => $order->technical_id_fk,
                'rate'             => $data['rate'],
                'comment'          => $data['comment'] ?? null,
            ]
        );

        // 6) بإمكانك تحديث كاش المعدّل على مستوى الفني (إن عندك أعمدة لذلك)
        // $this->refreshTechnicianAggregate($order->technical_id_fk);

        return response()->json([
            'message' => 'Rating saved',
            'data'    => $rating->fresh(),
        ], 200);
    }

    /**
     * إظهار تقييم هذا الطلب (إن وُجد)
     */
    public function showForOrder(Request $request, OrderService $order): JsonResponse
    {
        $user = $request->user();

        // يستطيع المالك أو الأدمن رؤية التقييم
        if (! ($user->id === $order->customer_id_fk || $user->role === 'admin')) {
            abort(403);
        }

        $rating = Rating::where('order_service_id_fk', $order->id)->first();

        return response()->json([
            'exists' => (bool) $rating,
            'data'   => $rating,
        ]);
    }

    /**
     * قائمة تقييمات فنّي
     */
    public function listForTechnician(User $technician): JsonResponse
    {
        if ($technician->role !== 'technical') {
            abort(404);
        }

        $ratings = Rating::with(['rater:id,name', 'orderService:id,service_id_fk'])
            ->where('technical_id_fk', $technician->id)
            ->latest('created_at')
            ->paginate(15);

        return response()->json($ratings);
    }

    /**
     * ملخص تقييمات فنّي
     */
    public function summaryForTechnician(User $technician): JsonResponse
    {
        if ($technician->role !== 'technical') {
            abort(404);
        }

        $query = Rating::where('technical_id_fk', $technician->id);

        $count = (clone $query)->count();
        $avg   = (float) (clone $query)->avg('rate');

        return response()->json([
            'technician_id' => $technician->id,
            'count'         => $count,
            'average'       => round($avg, 2),
        ]);
    }

    /**
     * (اختياري) تحديث مجموع/متوسط التقييمات وتخزينها في جدول users ككاش
     */
    protected function refreshTechnicianAggregate(string $technicianId): void
    {
        $q     = Rating::where('technical_id_fk', $technicianId);
        $count = (clone $q)->count();
        $avg   = (float) (clone $q)->avg('rate');

        User::whereKey($technicianId)->update([
            'ratings_count'  => $count,
            'rating_average' => $count ? $avg : null,
        ]);
    }
}
