<?php
// app/Http/Controllers/MainControllers/StateController.php
namespace App\Http\Controllers\MainControllers;

use App\Http\Controllers\Controller;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StateController extends Controller
{
    // قائمة المحافظات (مع إمكانية تضمين المناطق)
    // public function index(Request $request)
    // {
    //     $q = State::query()
    //         ->when($request->boolean('only_active'), fn($x) => $x->where('is_active', true))
    //         ->when($request->boolean('with_areas'), function ($x) use ($request) {
    //             $x->with(['areas' => function ($a) use ($request) {
    //                 $a->when($request->boolean('only_active_areas'), fn($y) => $y->where('is_active', true))
    //                     ->orderBy('sort_order')->orderBy('name');
    //             }]);
    //         })
    //         ->orderBy('sort_order')->orderBy('name');

    //     return response()->json(['data' => $q->get()]);
    // }


    public function index(Request $request)
{
    $q = State::query()
        // فلترة المحافظات
        ->when($request->boolean('only_active'), fn($x) => $x->where('is_active', true))

        // جلب المناطق دائمًا مع إمكانية فلترتها وترتيبها
        ->with(['areas' => function ($a) use ($request) {
            $a->when($request->boolean('only_active_areas'), fn($y) => $y->where('is_active', true))
              ->orderBy('sort_order')
              ->orderBy('name');
        }])

        // (اختياري) لا تجلب المحافظات التي لا تحتوي مناطق بعد الفلترة
        ->when($request->boolean('only_states_with_areas'), function ($x) use ($request) {
            $x->whereHas('areas', function ($y) use ($request) {
                $y->when($request->boolean('only_active_areas'), fn($z) => $z->where('is_active', true));
            });
        })

        ->orderBy('sort_order')
        ->orderBy('name');

    return response()->json(['data' => $q->get()]);
}


    // إضافة محافظة
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'       => 'required|string|max:100|unique:states,name',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ], [
            'name.required' => 'اسم المحافظة مطلوب.',
            'name.unique'   => 'اسم المحافظة موجود مسبقًا.',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $data = $v->validated();
        $item = State::create([
            'name'       => trim($data['name']),
            'is_active'  => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return response()->json(['message' => 'تمت الإضافة', 'data' => $item], 201);
    }

    // تعديل محافظة
    public function update(Request $request, string $id)
    {
        $item = State::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name'       => ['required', 'string', 'max:100', Rule::unique('states', 'name')->ignore($item->id)],
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ], [
            'name.required' => 'اسم المحافظة مطلوب.',
            'name.unique'   => 'اسم المحافظة موجود مسبقًا.',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $data = $v->validated();
        $item->update([
            'name'       => trim($data['name']),
            'is_active'  => array_key_exists('is_active', $data) ? (bool)$data['is_active'] : $item->is_active,
            'sort_order' => $data['sort_order'] ?? $item->sort_order,
        ]);

        return response()->json(['message' => 'تم التعديل', 'data' => $item]);
    }

    // حذف (سوفت)
    public function destroy(string $id)
    {
        $item = State::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'تم الحذف (Soft Delete).']);
    }

    // عرض المحذوفات فقط
    public function deleted()
    {
        return response()->json(['data' => State::onlyTrashed()->orderBy('name')->get()]);
    }

    // عرض الكل مع المحذوف
    public function allWithTrashed()
    {
        return response()->json(['data' => State::withTrashed()->orderBy('name')->get()]);
    }

    // استرجاع
    public function restore(string $id)
    {
        $item = State::onlyTrashed()->findOrFail($id);
        $item->restore();
        return response()->json(['message' => 'تم الاسترجاع', 'data' => $item]);
    }
}
