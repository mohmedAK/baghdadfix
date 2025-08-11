<?php

namespace App\Http\Controllers\MainControllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;

use App\Http\Requests\RegisterRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


    public function registerUser(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'     => 'required|string|max:250',
            'email'    => 'required_without:phone|nullable|email|max:250|unique:users,email',
            'phone'    => 'required_without:email|nullable|string|max:50|unique:users,phone',
            'password' => 'required|string|min:6|confirmed', // اطلب password_confirmation
            'role'     => 'required|in:admin,technical,customer',
            'state'    => 'required|string|max:250',
            'area'     => 'required|string|max:250',
        ], [
            'email.required_without' => 'email or phone is required',
            'phone.required_without' => 'phone or email is required',
        ]);

        if ($v->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $v->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $user = User::create([
                    // لو عندك UUIDTrait ما تحتاج id هنا، وإلا:
                    // 'id'   => Str::uuid()->toString(),
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'phone'    => $request->phone,
                    'role'     => $request->role,
                    'state'    => $request->state,
                    'area'     => $request->area,
                    'password' => Hash::make($request->password),
                ]);

                // Passport:
                $token = $user->createToken('api')->accessToken;
                // Sanctum (بدلاً من السطر فوق):
                // $token = $user->createToken('api')->plainTextToken;

                return response()->json([
                    'message' => 'User registered',
                    'user'    => $user,
                    'token'   => $token,
                ], 201);
            });
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to register'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RegisterRequest $request)
    {
      //  dd($request->all());
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
            // Sanctum (استبدل السطر فوق بهذا):
            // $token = $user->createToken('api')->plainTextToken;

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
