<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Booking;
use App\Models\Review;

class UserController extends Controller
{



    public function register(StoreUserRequest $request)
    {
        $data = $request->validated();

        try {
            if ($request->hasFile('profile_image')) {
                // Store in public disk for accessible images
                $data['profile_image'] = $request->file('profile_image')
                    ->store('profile_images', 'public');   // storage/app/public/profile_images
            }

            if ($request->hasFile('id_card_image')) {
                // Store in private disk for sensitive documents
                $data['id_card_image'] = $request->file('id_card_image')
                    ->store('id_cards', 'private');  // storage/app/private/id_cards
            }
            $data['password'] = Hash::make($data['password']);
            $data['status'] = 'pending'; //بانتظار الموافقة 
            $user = User::create($data);
            //توليد توكن 
            $token = $user->createToken('auth_token')->plainTextToken;

            //توليد رابط للصورة منشان الفلاتر يفتحها فورا
            // Handle both old format (public/profile_images/...) and new format (profile_images/...)
            $profileImageUrl = $user->profile_image
                ? asset('storage/' . str_replace('public/', '', $user->profile_image))
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

        // Handle both old format (public/profile_images/...) and new format (profile_images/...)
        $profileImageUrl = $user->profile_image
            ? asset('storage/' . str_replace('public/', '', $user->profile_image))
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
        $tokenId = $user->currentAccessToken()?->id;
        if ($tokenId) {
            $user->tokens()->where('id', $tokenId)->delete(); // حذف التوكن الحالي فقط منشان لو مسجل من اكتر من خادم مايأثر على الباقي
        }

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
        // Handle both old format (public/profile_images/...) and new format (profile_images/...)
        $profileImageUrl = $user->profile_image
            ? asset('storage/' . str_replace('public/', '', $user->profile_image))
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
    public function review(Request $request, $apartment_id)
    {
        $validatedData = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        // Validate apartment exists
        $apartment = \App\Models\Apartment::findOrFail($apartment_id);

        $user = Auth::user();

        // تحقق أن المستخدم لديه حجز Approved للشقة
        $hasBooking = Booking::where('user_id', $user->id)
            ->where('apartment_id', $apartment_id)
            ->where('status', 'approved')
            ->exists();

        if (!$hasBooking) {
            return response()->json([
                'message' => 'You can only review apartments you have rented.'
            ], 403);
        }

        // Check if user already reviewed this apartment
        $existingReview = Review::where('user_id', $user->id)
            ->where('apartment_id', $apartment_id)
            ->exists();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already reviewed this apartment.'
            ], 409);
        }

        // إضافة user_id و apartment_id
        $validatedData['user_id'] = $user->id;
        $validatedData['apartment_id'] = $apartment_id;

        // إنشاء التقييم
        $review = Review::create($validatedData);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review
        ], 201);
    }
}
