<?php
// app/Http/Controllers/MainControllers/ServiceController.php
namespace App\Http\Controllers\MainControllers;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    // قائمة الخدمات (فلترة اختيارية)
    public function index(Request $request)
    {
        $q = Service::query()
            ->when($request->filled('service_category_id_fk'), fn($x) =>
                $x->where('service_category_id_fk', $request->service_category_id_fk))
            ->when($request->boolean('only_active'), fn($x) => $x->where('is_active', true))
            ->orderBy('name');

        // بدون paging حسب رغبتك
        return response()->json(['data' => $q->get()]);
    }

    // عرض خدمة مفردة
    public function show(string $id)
    {
        $item = Service::findOrFail($id);
        return response()->json(['data' => $item]);
    }

    // إضافة خدمة
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'                   => ['required','string','max:250',
                    // فريد داخل نفس التصنيف + تجاهل السوفت دليت
                    Rule::unique('services','name')
                        ->where(fn($q) => $q->where('service_category_id_fk', $request->service_category_id_fk)
                                            ->whereNull('deleted_at'))
                ],
                'image'                  => 'nullable|image|max:2048',
                'service_category_id_fk' => 'required|uuid|exists:service_categories,id',
                'is_active'             => 'nullable|boolean',
            ],
            [
                'name.required' => 'اسم الخدمة مطلوب.',
                'name.unique'   => 'اسم الخدمة مستخدم داخل هذا التصنيف.',
                'image.image'   => 'الملف يجب أن يكون صورة.',
                'service_category_id_fk.required' => 'التصنيف مطلوب.',
                'service_category_id_fk.exists'   => 'التصنيف غير موجود.',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $data = $validator->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('services','public');
        }

        $item = Service::create([
            'name'                   => $data['name'],
            'image'                  => $imagePath,
            'service_category_id_fk' => $data['service_category_id_fk'],
            'is_active'              => $data['is_active'] ?? true,
        ]);

        return response()->json(['message'=>'تمت الإضافة بنجاح','data'=>$item], 201);
    }

    // تعديل خدمة
    public function update(Request $request, string $id)
    {
        $item = Service::findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required','string','max:250',
                    Rule::unique('services','name')
                        ->where(fn($q) => $q->where('service_category_id_fk',
                                    $request->service_category_id_fk ?? $item->service_category_id_fk)
                                          ->whereNull('deleted_at'))
                        ->ignore($item->id)
                ],
                'image'                  => 'nullable|image|max:2048',
                'service_category_id_fk' => 'sometimes|required|uuid|exists:service_categories,id',
                'is_active'             => 'nullable|boolean',
            ],
            [
                'name.required' => 'اسم الخدمة مطلوب.',
                'name.unique'   => 'اسم الخدمة مستخدم داخل هذا التصنيف.',
                'image.image'   => 'الملف يجب أن يكون صورة.',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $data = $validator->validated();

        // تحديث الصورة إن وُجدت
        if ($request->hasFile('image')) {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $request->file('image')->store('services','public');
        }

        $item->update([
            'name'                   => $data['name'],
            'image'                  => $data['image'] ?? $item->image,
            'service_category_id_fk' => $data['service_category_id_fk'] ?? $item->service_category_id_fk,
            'is_active'              => array_key_exists('is_active',$data) ? (bool)$data['is_active'] : $item->is_active,
        ]);

        return response()->json(['message'=>'تم التعديل بنجاح','data'=>$item]);
    }

    // حذف (Soft Delete)
    public function destroy(string $id)
    {
        $item = Service::findOrFail($id);
        $item->delete();
        return response()->json(['message'=>'تم حذف الخدمة (Soft Delete).']);
    }

    // عرض المحذوفات فقط
    public function indexDeleted()
    {
        $items = Service::onlyTrashed()->orderBy('name')->get();
        return response()->json(['data'=>$items]);
    }

    // عرض الكل مع المحذوف
    public function indexWithTrashed()
    {
        $items = Service::withTrashed()->orderBy('name')->get();
        return response()->json(['data'=>$items]);
    }

    // استرجاع خدمة محذوفة
    public function restore(string $id)
    {
        $item = Service::onlyTrashed()->findOrFail($id);
        $item->restore();
        return response()->json(['message'=>'تم الاسترجاع بنجاح','data'=>$item]);
    }

    // حذف نهائي (اختياري)
    public function forceDelete(string $id)
    {
        $item = Service::withTrashed()->findOrFail($id);
        if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }
        $item->forceDelete();
        return response()->json(['message'=>'تم الحذف النهائي.']);
    }
}
