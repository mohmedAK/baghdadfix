<?php
// app/Http/Controllers/MainControllers/AreaController.php
namespace App\Http\Controllers\MainControllers;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AreaController extends Controller
{
    // قائمة المناطق (فلترة بالمحافظة/التفعيل)
    public function index(Request $request)
    {
        $q = Area::query()
            ->when($request->filled('state_id_fk'), fn($x) => $x->where('state_id_fk', $request->state_id_fk))
            ->when($request->boolean('only_active'), fn($x) => $x->where('is_active', true))
            ->orderBy('sort_order')->orderBy('name');

        return response()->json(['data' => $q->get()]);
    }

    // مناطق محافظة واحدة
    public function byState(string $stateId)
    {
        $items = Area::where('state_id_fk', $stateId)
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        return response()->json(['data' => $items]);
    }

    // إضافة منطقة
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'state_id_fk' => 'required|uuid|exists:states,id',
            'name'        => [
                'required',
                'string',
                'max:100',
                // فريد داخل نفس المحافظة
                Rule::unique('areas', 'name')->where(fn($q) => $q->where('state_id_fk', $request->state_id_fk)),
            ],
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ], [
            'state_id_fk.required' => 'المحافظة مطلوبة.',
            'state_id_fk.exists'   => 'المحافظة غير موجودة.',
            'name.required'        => 'اسم المنطقة مطلوب.',
            'name.unique'          => 'اسم المنطقة مكرر داخل هذه المحافظة.',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $data = $v->validated();

        $item = Area::create([
            'state_id_fk' => $data['state_id_fk'],
            'name'        => trim($data['name']),
            'is_active'   => $data['is_active'] ?? true,
            'sort_order'  => $data['sort_order'] ?? 0,
        ]);

        return response()->json(['message' => 'تمت الإضافة', 'data' => $item], 201);
    }

    // تعديل منطقة
    public function update(Request $request, string $id)
    {
        $item = Area::findOrFail($id);

        $stateId = $request->state_id_fk ?? $item->state_id_fk;

        $v = Validator::make($request->all(), [
            'state_id_fk' => 'sometimes|required|uuid|exists:states,id',
            'name'        => [
                'required',
                'string',
                'max:100',
                Rule::unique('areas', 'name')
                    ->where(fn($q) => $q->where('state_id_fk', $stateId))
                    ->ignore($item->id),
            ],
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ], [
            'name.required' => 'اسم المنطقة مطلوب.',
            'name.unique'   => 'اسم المنطقة مكرر داخل هذه المحافظة.',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $data = $v->validated();

        $item->update([
            'state_id_fk' => $data['state_id_fk'] ?? $item->state_id_fk,
            'name'        => trim($data['name']),
            'is_active'   => array_key_exists('is_active', $data) ? (bool)$data['is_active'] : $item->is_active,
            'sort_order'  => $data['sort_order'] ?? $item->sort_order,
        ]);

        return response()->json(['message' => 'تم التعديل', 'data' => $item]);
    }

    // حذف (سوفت)
    public function destroy(string $id)
    {
        $item = Area::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'تم الحذف (Soft Delete).']);
    }

    // المحذوفات فقط
    public function deleted()
    {
        return response()->json(['data' => Area::onlyTrashed()->orderBy('name')->get()]);
    }

    // الكل مع المحذوف
    public function allWithTrashed()
    {
        return response()->json(['data' => Area::withTrashed()->orderBy('name')->get()]);
    }

    // استرجاع
    public function restore(string $id)
    {
        $item = Area::onlyTrashed()->findOrFail($id);
        $item->restore();
        return response()->json(['message' => 'تم الاسترجاع', 'data' => $item]);
    }
}
