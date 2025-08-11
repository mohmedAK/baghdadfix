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
use Illuminate\Http\JsonResponse;


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




    public function login(LoginRequest $request)
    {
        // البحث إما بالإيميل أو الهاتف
        $user = User::query()
            ->when($request->email, fn($q) => $q->where('email', $request->email))
            ->when($request->phone, fn($q) => $q->where('phone', $request->phone))
            ->first();

        // التحقق من كلمة المرور
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // إنشاء التوكن (Passport)
        $token = $user->createToken('api')->accessToken;


        return response()->json([
            'message' => 'Logged in successfully',
            'user'    => $user,
            'token'   => $token,
        ], 200);
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
    public function registerUser(RegisterRequest $request)
    {
        //dd($request->all());
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
