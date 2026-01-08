<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{

    public function register(StoreUserRequest $request)
    {
        $data = $request->validated();

        try {
            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
            }

            if ($request->hasFile('id_card_image')) {
                $data['id_card_image'] = $request->file('id_card_image')->store('id_cards', 'private');
            }
            $data['password'] = Hash::make($data['password']);
            $data['status'] = 'pending';
            $user = User::create($data);
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'registration failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkAvailableNumber(Request $request)
    {
        $request->validate(['phone' => 'required']);
        if (User::where('phone', $request->phone)->exists()) {
            return response()->json(['message' => 'Phone number already used'], 409);
        }
        return response()->json(['message' => 'phone number is available'], 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if (!Auth::attempt($request->only('phone', 'password'))) {
            return response()->json(['message' => 'Invalid phone or password'], 401);
        }

        $user = User::where('phone', $request->phone)->firstOrFail();

        if ($user->status == 'rejected') {
            return response()->json(['message' => 'Your account is rejected'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user_data' => new UserResource($user)
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $tokenId = $user->currentAccessToken()?->id;
        if ($tokenId) {
            $user->tokens()->where('id', $tokenId)->delete();
        }
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function getUser(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'invalid token'], 401);
        }
        return response()->json([
            'message' => 'success',
            'user_data' => new UserResource($user)
        ]);
    }

    public function updateUserProfile(Request $request)
    {
        $user = Auth::user();
        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'birth_date' => 'sometimes|nullable|date',
            'profile_image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $validatedData['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        $user->update($validatedData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user_data' => new UserResource($user)
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password does not match'], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Password changed successfully'], 200);
    }
}
