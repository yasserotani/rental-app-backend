<?php

namespace App\Http\Controllers;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{



    public function register(StoreUserRequest $request)
    {
        $data = $request->validated();

        try {
            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $request->file('profile_image')
                    ->store('public/profile_images');   //  storage/app/public
            }

            if ($request->hasFile('id_card_image')) {
                $data['id_card_image'] = $request->file('id_card_image')
                    ->store('private/id_cards');  // storage/app/private
            }
            $data['password'] = Hash::make($data['password']);
            $data['status'] = 'pending';//بانتظار الموافقة 
            $user = User::create($data);
            //توليد توكن 
            $token = $user->createToken('auth_token')->plainTextToken;

            //توليد رابط للصورة منشان الفلاتر يفتحها فورا
            $profileImageUrl = $user->profile_image
                ? asset(str_replace('public/', 'storage/', $user->profile_image))
                : null;
            //شو رح يرجع
            return response()->json([
                'message' => 'User registered successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'phone' => $user->phone,
                    'birth_date' => $user->birth_date,
                    'profile_image_url' => $profileImageUrl,
                    'created_at' => $user->created_at,
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'registration failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    //=============================================================================================

    // check if the number isn't used in the database
    public function checkAvailableNumber(Request $request)
    {
        $request->validate([
            'phone' => 'required'
        ]);

        if (User::where('phone', $request->phone)->exists()) {
            return response()->json([
                'message' => 'Phone number already used'
            ], 409);
        }

        return response()->json([
            'message' => 'phone number is available'
        ], 200);

    }
    //=============================================================================================
    public function login(Request $request)
    {

        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string|min:8',
        ]);
        // محاولة تسجيل الدخول
        if (!Auth::attempt($request->only('phone', 'password'))) {
            return response()->json([
                'message' => 'Invalid phone or password'
            ], 401);
        }
        // جلب المستخدم بعد مالقيتو
        $user = User::where('phone', $request->phone)->firstOrFail();

        // check if the user is rejected 
        if ($user->status == 'rejected') {
            return response()->json([
                'message' => 'Your account is rejected'
            ], 403);
        }
        // إنشاء توكن Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        $profileImageUrl = $user->profile_image
            ? asset(str_replace('public/', 'storage/', $user->profile_image))
            : null;
        // إرجاع التوكن واسم المستخدم فقط
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user_data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'birth_date' => $user->birth_date,
                'profile_image_url' => $profileImageUrl,
                'created_at' => $user->created_at,
            ]
        ], 200);
    }
    //=============================================================================================
    public function logout(Request $request)
    {
        $user = $request->user(); // المستخدم الذي أرسل التوكن
        $user->currentAccessToken()->delete(); // حذف التوكن الحالي فقط منشان لو مسجل من اكتر من خادم مايأثر على الباقي

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
    //=============================================================================================
    // return the current user 
    public function getUser(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'invalid token'
            ], 401);
        }
        $profileImageUrl = $user->profile_image
            ? asset(str_replace('public/', 'storage/', $user->profile_image))
            : null;
        return response()->json([
            'message' => 'success',
            'user_data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'birth_date' => $user->birth_date,
                'profile_image_url' => $profileImageUrl,
                'created_at' => $user->created_at,
            ]
        ]);
    }
}