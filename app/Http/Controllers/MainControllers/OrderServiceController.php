<?php

namespace App\Http\Controllers\MainControllers;

use App\Http\Controllers\Controller;
use App\Models\OrderService;
use App\Models\OrderServiceMedia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderServiceController extends Controller
{
    /* ===================== Helpers ===================== */

    private function userRole(User $user): string
    {
        // يدعم enum أو نص خام
        return is_object($user->role) && property_exists($user->role, 'value')
            ? $user->role->value
            : (string) $user->role;
    }

    private function canView(OrderService $o, User $u): bool
    {
        $role = $this->userRole($u);
        return $role === 'admin' || $o->customer_id_fk === $u->id || $o->technical_id_fk === $u->id;
    }

    private function ensureAdmin(User $u): void
    {
        if ($this->userRole($u) !== 'admin') {
            abort(403, 'Admins only.');
        }
    }

    /* ===================== Queries ===================== */

    // GET /orders (فلترة بسيطة)
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $this->userRole($user);

        $q = OrderService::query()
            ->with([
                'service:id,name',
                'customer:id,name,phone',
                'technical:id,name,phone',
                'state:id,name',
                'area:id,name',
                'media:id,order_service_id_fk,type,file_path,is_primary,sort_order',
            ])
            ->when($role === 'customer', fn (Builder $b) => $b->where('customer_id_fk', $user->id))
            ->when($role === 'technical', fn (Builder $b) => $b->where('technical_id_fk', $user->id));

        if ($status = $request->query('status'))      $q->where('status', $status);
        if ($service = $request->query('service_id')) $q->where('service_id_fk', $service);
        if ($from = $request->query('from'))          $q->whereDate('created_at', '>=', $from);
        if ($to = $request->query('to'))              $q->whereDate('created_at', '<=', $to);

        $items = $q->latest('created_at')->paginate(15);

        return response()->json(['data' => $items]);
    }

    // GET /orders/{id}
    public function show(Request $request, string $id)
    {
        $user  = $request->user();
        $order = OrderService::with([
            'service:id,name',
            'customer:id,name,phone',
            'technical:id,name,phone',
            'state:id,name',
            'area:id,name',
            'media:id,order_service_id_fk,type,file_path,is_primary,sort_order',
        ])->findOrFail($id);

        abort_unless($this->canView($order, $user), 403);

        return response()->json(['data' => $order]);
    }

    /* ===================== Customer: create ===================== */

    // POST /orders
    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($this->userRole($user) === 'customer', 403, 'Only customers can create orders.');

        $data = $request->validate([
            'service_id_fk' => ['required', 'uuid', 'exists:services,id'],
            'state_id_fk'   => ['nullable', 'uuid', 'exists:states,id'],
            'area_id_fk'    => ['nullable', 'uuid', 'exists:areas,id'],
            'gps_lat'       => ['nullable', 'numeric'],
            'gps_lng'       => ['nullable', 'numeric'],
            'description'   => ['nullable', 'string'],

            // ميديا اختيارية عند الإنشاء
             'images.*'      => ['file', 'mimes:jpg,jpeg,png,webp', 'max:20480'], // 20MB
             'videos.*'      => ['file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo', 'max:51200'], // 50MB
        ]);

        $order = OrderService::create([
            'id'             => (string) Str::uuid(),
            'customer_id_fk' => $user->id,
            'service_id_fk'  => $data['service_id_fk'],
            'state_id_fk'    => $data['state_id_fk'] ?? null,
            'area_id_fk'     => $data['area_id_fk'] ?? null,
            'gps_lat'        => $data['gps_lat'] ?? null,
            'gps_lng'        => $data['gps_lng'] ?? null,
            'description'    => $data['description'] ?? null,
            'status'         => 'created',
            'submit'         => false,
        ]);



        // رفع صور متعددة (اختياري)
        if ($request->hasFile('images')) {
            $i = 0;
            foreach ($request->file('images') as $file) {
                $path = $file->store('orders/images', 'public');

                OrderServiceMedia::create([
                    'order_service_id_fk' => $order->id,
                    'type'                => 'image',
                    'file_path'           => $path,
                    'mime'                => $file->getMimeType() ?? null,
                    'size_bytes'                => $file->getSize() ?? null,
                    'is_primary'          => $i === 0,
                    'sort_order'          => $i++,
                ]);
            }
        }

        // رفع فيديوهات متعددة (اختياري)
        if ($request->hasFile('videos')) {
            $i = 0;
            foreach ($request->file('videos') as $file) {
                $path = $file->store('orders/videos', 'public');

                OrderServiceMedia::create([
                    'order_service_id_fk' => $order->id,
                    'type'                => 'video',
                    'file_path'           => $path,
                    'mime'                => $file->getMimeType() ?? null,
                    'size_bytes'                => $file->getSize() ?? null,
                    'is_primary'          => false,
                    'sort_order'          => $i++,
                ]);
            }
        }

        return response()->json(['message' => 'Order created', 'data' => $order->load('media')], 201);
    }

    // POST /orders/{id}/media  (image|video) — يمكن تكراره لملفات متعددة
    public function addMedia(Request $request, string $id)
    {
        $user  = $request->user();
        $order = OrderService::findOrFail($id);
        abort_unless($this->canView($order, $user), 403);

        $data = $request->validate([
            'type'      => ['required', Rule::in(['image','video'])],
            'file'      => ['required', 'file', 'max:51200'], // عدّل السعة حسب الحاجة
            'is_primary'=> ['nullable', 'boolean'],
            'sort_order'=> ['nullable', 'integer'],
        ]);

        $dir  = $data['type'] === 'image' ? 'orders/images' : 'orders/videos';
        $path = $request->file('file')->store($dir, 'public');

        $media = OrderServiceMedia::create([
            'order_service_id_fk' => $order->id,
            'type'                => $data['type'],
            'file_path'           => $path,
            'mime'                => $request->file('file')->getMimeType() ?? null,
            'size'                => $request->file('file')->getSize() ?? null,
            'is_primary'          => (bool) ($data['is_primary'] ?? false),
            'sort_order'          => $data['sort_order'] ?? 0,
        ]);

        return response()->json([
            'message' => 'Media uploaded',
            'data'    => [
                'id'   => $media->id,
                'type' => $media->type,
                'url'  => Storage::disk('public')->url($media->file_path),
            ],
        ], 201);
    }

    /* ===================== Technician: quote ===================== */

    // POST /orders/{id}/technician-quote
    public function technicianQuote(Request $request, string $id)
    {
        $user  = $request->user();
        $order = OrderService::findOrFail($id);

        abort_unless($this->userRole($user) === 'technical' && $order->technical_id_fk === $user->id, 403, 'Only assigned technician can submit a quote.');

        if (! in_array($order->status, ['assigned', 'inspecting'])) {
            abort(422, 'Order is not in an inspectable state.');
        }

        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'note'  => ['nullable', 'string', 'max:500'],
        ]);

        $order->update([
            'technician_quote_price' => $data['price'],
            'technician_quote_note'  => $data['note'] ?? null,
            'technician_quote_at'    => now(),
            'status'                 => 'quote_pending',
        ]);

        return response()->json(['message' => 'Quote submitted', 'data' => $order->fresh()], 200);
    }

    /* ===================== Admin: estimate/assign/final ===================== */

    // POST /orders/{id}/estimate
    public function adminEstimate(Request $request, string $id)
    {
        $user = $request->user();
        $this->ensureAdmin($user);

        $order = OrderService::findOrFail($id);

        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'note'  => ['nullable', 'string', 'max:500'],
        ]);

        $order->update([
            'admin_initial_price'    => $data['price'],
            'admin_initial_note'     => $data['note'] ?? null,
            'admin_initial_at'       => now(),
            'admin_initial_by_id_fk' => $user->id,
            'status'                 => 'admin_estimated',
        ]);

        return response()->json(['message' => 'Estimated', 'data' => $order->fresh()]);
    }

    // POST /orders/{id}/assign
    public function assignTechnician(Request $request, string $id)
    {
        $user = $request->user();
        $this->ensureAdmin($user);

        $order = OrderService::findOrFail($id);

        $data = $request->validate([
            'technical_id_fk' => ['required', 'uuid', 'exists:user,id'],
            'note'            => ['nullable', 'string', 'max:500'],
        ]);

        // تأكد أن المستخدم فني
        $tech = User::findOrFail($data['technical_id_fk']);
        if ($this->userRole($tech) !== 'technical') {
            abort(422, 'Selected user is not a technician.');
        }

        $order->update([
            'technical_id_fk'         => $tech->id,
            'assignment_note'         => $data['note'] ?? null,
            'assigned_at'             => now(),
            'assigned_by_admin_id_fk' => $user->id,
            'status'                  => 'assigned',
        ]);

        return response()->json(['message' => 'Technician assigned', 'data' => $order->fresh()]);
    }

    // POST /orders/{id}/final-price
    public function setFinalPrice(Request $request, string $id)
    {
        $user = $request->user();
        $this->ensureAdmin($user);

        $order = OrderService::findOrFail($id);

        $data = $request->validate([
            'final_price' => ['required', 'numeric', 'min:0'],
        ]);

        $order->update([
            'final_price' => $data['final_price'],
            'status'      => 'awaiting_customer_approval',
        ]);

        return response()->json(['message' => 'Final price set', 'data' => $order->fresh()]);
    }

    // POST /orders/{id}/status  (generic admin status change)
    public function updateStatus(Request $request, string $id)
    {
        $user = $request->user();
        $this->ensureAdmin($user);

        $data = $request->validate([
            'status' => ['required', Rule::in([
                'created','admin_estimated','assigned','inspecting',
                'quote_pending','awaiting_customer_approval',
                'approved','rejected','in_progress','completed','canceled',
            ])],
        ]);

        $order = OrderService::findOrFail($id);
        $order->update(['status' => $data['status']]);

        return response()->json(['message' => 'Status updated', 'data' => $order->fresh()]);
    }

    /* ===================== Customer: approve / reject ===================== */

    public function approve(Request $request, string $id)
    {
        $user  = $request->user();
        $order = OrderService::findOrFail($id);

        abort_unless($this->userRole($user) === 'customer' && $order->customer_id_fk === $user->id, 403);

        if ($order->status !== 'awaiting_customer_approval') {
            abort(422, 'Order is not awaiting customer approval.');
        }

        $order->update([
            'submit'              => true,
            'customer_decided_at' => now(),
            'status'              => 'approved',
        ]);

        return response()->json(['message' => 'Approved', 'data' => $order->fresh()]);
    }

    public function reject(Request $request, string $id)
    {
        $user  = $request->user();
        $order = OrderService::findOrFail($id);

        abort_unless($this->userRole($user) === 'customer' && $order->customer_id_fk === $user->id, 403);

        if ($order->status !== 'awaiting_customer_approval') {
            abort(422, 'Order is not awaiting customer approval.');
        }

        $order->update([
            'submit'              => false,
            'customer_decided_at' => now(),
            'status'              => 'rejected',
        ]);

        return response()->json(['message' => 'Rejected', 'data' => $order->fresh()]);
    }

    /* ===================== Trash / Restore ===================== */

    public function destroy(string $id)
    {
        $order = OrderService::findOrFail($id);
        $order->delete(); // Soft delete (لا نحذف ملفات الميديا هنا)
        return response()->json(['message' => 'Order soft-deleted.']);
    }

    public function deleted()
    {
        $items = OrderService::onlyTrashed()->orderByDesc('deleted_at')->get();
        return response()->json(['data' => $items]);
    }

    public function restore(string $id)
    {
        $order = OrderService::onlyTrashed()->findOrFail($id);
        $order->restore();
        return response()->json(['message' => 'Order restored', 'data' => $order]);
    }
}
