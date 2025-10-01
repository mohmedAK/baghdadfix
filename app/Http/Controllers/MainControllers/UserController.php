<?php

namespace App\Http\Controllers\MainControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Auth\RegisterRequest; // تأكد من وجود هذا الـ Request
use App\Models\OrderService;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index(): JsonResponse
    {
        $users = User::all(); // أو User::paginate(10) إذا تريد تقسيم الصفحات

        return response()->json([
            'data' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }




    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email'    => 'required_without:phone|nullable|email|max:250',
                'phone'    => 'required_without:email|nullable|string|max:50',
                'password' => 'required|string|min:6',
            ],
            [
                'email.required_without' => 'email or phone is required',
                'email.email'            => 'email is invalid',
                'email.max'              => 'email must not exceed 250 characters',

                'phone.required_without' => 'phone or email is required',
                'phone.string'           => 'phone must be a string',
                'phone.max'              => 'phone must not exceed 50 characters',

                'password.required' => 'password is required',
                'password.min'      => 'password must be at least 6 characters',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find by email or phone
        $user = User::query()
            ->when($request->filled('email'), fn($q) => $q->where('email', $request->email))
            ->when($request->filled('phone'), fn($q) => $q->where('phone', $request->phone))
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Passport token
        $token = $user->createToken('api')->accessToken;

        $extra = [];
        // dd($user->role);
        // If the logged in user is a technician, include his orders & ratings
        if ($user->role->value === 'technical') {
            // All orders assigned to this technician
            $orders = OrderService::query()
                ->where('technical_id_fk', $user->id)
                ->with([
                    // include small related info (adjust as you like)
                    'customer:id,name',
                    'service:id,name',
                    'state:id,name',
                    'area:id,name',
                ])
                ->select([
                    'id',
                    'customer_id_fk',
                    'service_id_fk',
                    'state_id_fk',
                    'area_id_fk',
                    'description',
                    'status',
                    'submit',
                    'admin_initial_price',
                    'final_price',
                    'assigned_at',
                    'created_at',
                    'updated_at',
                    'technical_id_fk',
                    'gps_lat',
                    'gps_lng',
                ])
                ->latest('created_at')
                ->get();

            // All ratings for this technician
            $ratings = Rating::query()
                ->where('technical_id_fk', $user->id)
                ->with([
                    'rater:id,name',
                    'order:id'  // or 'orderService:id' depending on your relation name
                ])
                ->select(['id', 'order_service_id_fk', 'rater_id_fk', 'technical_id_fk', 'rate', 'comment', 'created_at'])
                ->latest('created_at')
                ->get();

            // Some quick aggregates (optional)
            $extra = [
                'orders'  => $orders,
                'ratings' => [
                    'items'      => $ratings,
                    'count'      => $ratings->count(),
                    'avg_rate'   => (float) number_format((float) $ratings->avg('rate'), 2, '.', ''),
                ],
            ];
        }

        return response()->json(array_merge([
            'message' => 'Logged in successfully',
            'user'    => $user,
            'token'   => $token,
        ], $extra), 200);
    }

    public function logout()
    {
        // Passport: إلغاء التوكن الحالي
        $token = auth()->user()->token();
        $token->revoke();

        return response()->json(['message' => 'Logged out successfully.']);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function registerUser(Request $request)
    {
        //dd($request->all());


        $validator = Validator::make(
            $request->all(),
            [
                'name'     => 'required|string|max:250',
                'email'    => 'required_without:phone|nullable|email|max:250|unique:users,email',
                'phone'    => [
                    'required_without:email',
                    'nullable',
                    'string',
                    'max:50',
                    // لو عندك soft deletes في users استخدم whereNull:
                    Rule::unique('users', 'phone')->whereNull('deleted_at'),
                ],
                'password' => 'required|string|min:6',
                'role'     => 'required|in:admin,technical,customer',
                'state'    => 'required|string|max:250',
                'area'     => 'required|string|max:250',
            ],
            [
                'name.required' => 'name is required',

                'email.required_without' => 'email or phone is required',
                'email.email'            => 'email is invalid',
                'email.unique'           => 'email must be unique',

                'phone.required_without' => 'phone or email is required',
                'phone.unique'           => 'phone must be unique',

                'password.required' => 'password is required',
                'password.min'      => 'password must be at least 6 characters',

                'role.required' => 'role is required',
                'role.in'       => 'role must be one of: admin, technical, customer',

                'state.required' => 'state is required',
                'area.required'  => 'area is required',
            ]
        );

        if ($validator->fails()) {
            return  $validator->errors();
        }

        $user = DB::transaction(function () use ($request) {
            return User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'role'     => $request->role,
                'state'    => $request->state,
                'area'     => $request->area,
                'password' => Hash::make($request->password),
            ]);
        });

        // ✅ إن كان الطلب API (Accept: application/json أو route من api.php) رجّع JSON + Token
        if ($request->expectsJson() || $request->is('api/*')) {
            // Passport:
            $token = $user->createToken('api')->accessToken;

            return response()->json([
                'message' => 'User registered',
                'user'    => $user,
                'token'   => $token,
            ], 201);
        }

        // 🌐 إن كان الطلب Web عادي → Redirect + Flash
        return redirect()->route('dashboard') // غيّرها لوجهتك
            ->with('success', 'تم التسجيل بنجاح. أهلاً بك يا ' . $user->name . '!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
