<?php

namespace App\Http\Controllers\MainControllers;

use App\Http\Controllers\Controller;
use App\Models\OrderService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderServiceController extends Controller
{
    // GET /orders  (فلترة: customer_id, tech_id, status, service_id, date range, only_mine ...)
    public function index(Request $request)
    {
        $q = OrderService::query()
            ->with(['customer:id,name,phone','technical:id,name,phone','service:id,name'])
            ->when($request->filled('customer_id'),  fn($x) => $x->where('customer_id_fk',  $request->customer_id))
            ->when($request->filled('technical_id'), fn($x) => $x->where('technical_id_fk', $request->technical_id))
            ->when($request->filled('service_id'),   fn($x) => $x->where('service_id_fk',   $request->service_id))
            ->when($request->filled('status'),       fn($x) => $x->where('status',          $request->status))
            ->when($request->boolean('only_mine'),   fn($x) => $x->where('customer_id_fk', auth()->id()))
            ->when($request->filled('from'),         fn($x) => $x->whereDate('created_at','>=',$request->from))
            ->when($request->filled('to'),           fn($x) => $x->whereDate('created_at','<=',$request->to))
            ->orderByDesc('created_at');

        return response()->json(['data' => $q->get()]);
    }

    // GET /orders/{id}
    public function show(string $id)
    {
        $item = OrderService::with([
            'customer:id,name,phone',
            'technical:id,name,phone',
            'service:id,name',
            'state:id,name', 'area:id,name'
        ])->findOrFail($id);

        return response()->json(['data' => $item]);
    }

    // POST /orders  — العميل ينشئ طلب
    public function store(Request $request)
    {
        $v = Validator::make(
            $request->all(),
            [
                'service_id_fk' => 'required|uuid|exists:services,id',
                'description'   => 'nullable|string',
                'state_id_fk'   => 'nullable|uuid|exists:states,id',
                'area_id_fk'    => 'nullable|uuid|exists:areas,id',
                'gps_lat'       => 'nullable|numeric',
                'gps_lng'       => 'nullable|numeric',
                'image'         => 'nullable|image|max:4096',
                'video'         => 'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:30720', // 30MB
            ],
            [
                'service_id_fk.required' => 'الخدمة مطلوبة.',
            ]
        );
        if ($v->fails()) return response()->json(['errors'=>$v->errors()], 422);

        $data = $v->validated();

        $imagePath = $request->hasFile('image') ? $request->file('image')->store('orders/images','public') : null;
        $videoPath = $request->hasFile('video') ? $request->file('video')->store('orders/videos','public') : null;

        $item = OrderService::create([
            'customer_id_fk' => auth()->id(),
            'service_id_fk'  => $data['service_id_fk'],
            'description'    => $data['description'] ?? null,
            'state_id_fk'    => $data['state_id_fk'] ?? null,
            'area_id_fk'     => $data['area_id_fk'] ?? null,
            'gps_lat'        => $data['gps_lat'] ?? null,
            'gps_lng'        => $data['gps_lng'] ?? null,
            'image'          => $imagePath,
            'video'          => $videoPath,
            'status'         => 'created',
            'submit'         => false,
        ]);

        return response()->json(['message'=>'تم إنشاء الطلب.','data'=>$item], 201);
    }

    // POST /orders/{id}/estimate  — الأدمن يضع السعر الابتدائي
    public function adminEstimate(Request $request, string $id)
    {
        $item = OrderService::findOrFail($id);

        $v = Validator::make($request->all(), [
            'admin_initial_price' => 'required|numeric|min:0',
            'admin_initial_note'  => 'nullable|string|max:500',
        ]);
        if ($v->fails()) return response()->json(['errors'=>$v->errors()], 422);

        $data = $v->validated();

        $item->update([
            'admin_initial_price'   => $data['admin_initial_price'],
            'admin_initial_note'    => $data['admin_initial_note'] ?? null,
            'admin_initial_by_id_fk'=> auth()->id(),
            'admin_initial_at'      => now(),
            'status'                => 'admin_estimated',
        ]);

        return response()->json(['message'=>'تم تحديد السعر الابتدائي.','data'=>$item]);
    }

    // POST /orders/{id}/assign  — الأدمن يعيّن الفني
    public function assignTechnician(Request $request, string $id)
    {
        $item = OrderService::findOrFail($id);

        $v = Validator::make($request->all(), [
            'technical_id_fk' => ['required','uuid', Rule::exists('users','id')->where(fn($q) => $q->where('role','technical'))],
            'assignment_note' => 'nullable|string|max:500',
        ], [
            'technical_id_fk.required' => 'الفني مطلوب.',
        ]);
        if ($v->fails()) return response()->json(['errors'=>$v->errors()], 422);

        $data = $v->validated();

        $item->update([
            'technical_id_fk'        => $data['technical_id_fk'],
            'assigned_by_admin_id_fk'=> auth()->id(),
            'assigned_at'            => now(),
            'assignment_note'        => $data['assignment_note'] ?? null,
            'status'                 => 'assigned',
        ]);

        return response()->json(['message'=>'تم تعيين الفني.','data'=>$item]);
    }

    // POST /orders/{id}/status  — تحديث الحالة (أدمن غالبًا)
    public function updateStatus(Request $request, string $id)
    {
        $item = OrderService::findOrFail($id);

        $validStatuses = [
            'created','admin_estimated','assigned','inspecting','quote_pending',
            'awaiting_customer_approval','approved','rejected','in_progress','completed','canceled'
        ];

        $v = Validator::make($request->all(), [
            'status' => ['required', Rule::in($validStatuses)],
        ]);
        if ($v->fails()) return response()->json(['errors'=>$v->errors()], 422);

        $item->update(['status' => $request->status]);

        return response()->json(['message'=>'تم تحديث الحالة.','data'=>$item]);
    }

    // موافقة/رفض العميل
    public function approve(string $id)
    {
        $item = OrderService::findOrFail($id);

        // يمكن التحقق أن هذا الطلب يخص المستخدم الحالي:
        if ($item->customer_id_fk !== auth()->id()) {
            return response()->json(['message' => 'غير مصرح.'], 403);
        }

        $item->update([
            'submit' => true,
            'status' => 'approved',
        ]);

        return response()->json(['message'=>'تمت الموافقة.','data'=>$item]);
    }

    public function reject(string $id)
    {
        $item = OrderService::findOrFail($id);

        if ($item->customer_id_fk !== auth()->id()) {
            return response()->json(['message' => 'غير مصرح.'], 403);
        }

        $item->update([
            'submit' => false,
            'status' => 'rejected',
        ]);

        return response()->json(['message'=>'تم الرفض.','data'=>$item]);
    }

    // حذف سوفت + المحذوفات + استرجاع
    public function destroy(string $id)
    {
        $item = OrderService::findOrFail($id);
        $item->delete();
        return response()->json(['message'=>'تم حذف الطلب (Soft Delete).']);
    }

    public function deleted()
    {
        $items = OrderService::onlyTrashed()->orderByDesc('deleted_at')->get();
        return response()->json(['data'=>$items]);
    }

    public function restore(string $id)
    {
        $item = OrderService::onlyTrashed()->findOrFail($id);
        $item->restore();
        return response()->json(['message'=>'تم الاسترجاع.','data'=>$item]);
    }
}
