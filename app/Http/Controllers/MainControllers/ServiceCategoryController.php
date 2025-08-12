<?php

namespace App\Http\Controllers\MainControllers;

use App\Http\Controllers\Controller;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use App\Http\Requests\ServiceCategories\StoreServiceCategoryRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ServiceCategoryController extends Controller
{
    // GET /api/service-categories
    public function index(Request $request): JsonResponse
    {
        // فلترة اختيارية
        $q = ServiceCategory::query()
            ->when($request->boolean('only_active'), fn($x) => $x->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('name');

        // إرجاع كل النتائج بدون paging
        $items = $q->get();

        return response()->json(['data' => $items]);
    }


    // GET /api/service-categories/{id}
    public function show(string $id)
    {
        $item = ServiceCategory::findOrFail($id);
        return response()->json(['data' => $item]);
    }

    // POST /api/service-categories  (admin)
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                // لو عندك SoftDeletes وتريد منع تكرار الاسم بين السجلات غير المحذوفة فقط:
                // 'name' => 'required|string|max:250|unique:service_categories,name,NULL,id,deleted_at,NULL',
                'name'       => 'required|string|max:250|unique:service_categories,name',
                'image'      => 'nullable|image|max:2048', // 2MB
                'is_active'  => 'nullable|boolean',
                'sort_order' => 'nullable|integer',
            ],
            [
                'name.required' => 'اسم التصنيف مطلوب.',
                'name.unique'   => 'اسم التصنيف مستخدم من قبل.',
                'image.image'   => 'الملف يجب أن يكون صورة.',
                'image.max'     => 'حجم الصورة يجب ألا يتجاوز 2MB.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // رفع الصورة (اختياري)
        $imagePath = null;
        if ($request->hasFile('image')) {
            // تأكد أنك عامل symlink: php artisan storage:link
            $imagePath = $request->file('image')->store('service-categories', 'public');
        }

        $item = ServiceCategory::create([
            'name'       => $validated['name'],
            'image'      => $imagePath,
            'is_active'  => array_key_exists('is_active', $validated)
                ? (bool)$validated['is_active']
                : true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return response()->json([
            'message' => 'تم إنشاء التصنيف بنجاح.',
            'data'    => $item,
        ], 201);
    }






    // UPDATE: تعديل تصنيف
    public function update(Request $request, string $id)
    {
        $item = ServiceCategory::findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            [
                // تجاهل نفس السجل في فحص الـ unique
                'name'       => [
                    'required',
                    'string',
                    'max:250',
                    Rule::unique('service_categories', 'name')->ignore($item->id),
                ],
                'image'      => 'nullable|image|max:2048',
                'is_active'  => 'nullable|boolean',
                'sort_order' => 'nullable|integer',
            ],
            [
                'name.required' => 'اسم التصنيف مطلوب.',
                'name.unique'   => 'اسم التصنيف مستخدم من قبل.',
                'image.image'   => 'الملف يجب أن يكون صورة.',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // معالجة الصورة (اختياري)
        if ($request->hasFile('image')) {
            // حذف القديمة إن وُجدت
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $request->file('image')->store('service-categories', 'public');
        }

        // قيم افتراضية للحقلين الاختياريين
        if (!array_key_exists('is_active', $data))  $data['is_active'] = $item->is_active;
        if (!array_key_exists('sort_order', $data)) $data['sort_order'] = $item->sort_order;

        $item->update($data);

        return response()->json([
            'message' => 'تم تحديث التصنيف بنجاح.',
            'data'    => $item,
        ]);
    }

    // LIST DELETED: عرض المحذوفات فقط
    public function indexDeleted(Request $request)
    {
        $items = ServiceCategory::onlyTrashed()
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        return response()->json(['data' => $items]);
    }

    // LIST WITH TRASHED: عرض الكل بما فيهم المحذوفات
    public function indexWithTrashed(Request $request)
    {
        $items = ServiceCategory::withTrashed()
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        return response()->json(['data' => $items]);
    }

    // RESTORE: استرجاع عنصر محذوف
    public function restore(string $id)
    {
        $item = ServiceCategory::onlyTrashed()->findOrFail($id);
        $item->restore();

        return response()->json(['message' => 'تم الاسترجاع بنجاح.', 'data' => $item]);
    }

    // SOFT DELETE: حذف ناعم
    public function destroy(string $id)
    {
        $item = ServiceCategory::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'تم حذف التصنيف (Soft Delete).']);
    }

    // FORCE DELETE (اختياري): حذف نهائي
    public function forceDelete(string $id)
    {
        $item = ServiceCategory::withTrashed()->findOrFail($id);

        // حذف الصورة من التخزين إن وُجدت
        if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }

        $item->forceDelete();

        return response()->json(['message' => 'تم الحذف النهائي للتصنيف.']);
    }
}
