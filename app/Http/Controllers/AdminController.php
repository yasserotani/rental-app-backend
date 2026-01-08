<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

    public function get_All_Users(Request $request)
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        return UserResource::collection($users);
    }

    public function login_admin(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string|min:8'
        ]);
        $admin = Admin::where('phone', $request->phone)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Invalid phone or password'], 401);
        }
        $admin = Admin::where('phone', $request->phone)->firstOrFail();
        $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;

        return response()->json([
            'message' => 'Admin login success',
            'admin' => $admin,
            'token' => $token
        ]);
    }

    public function delete_user($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        } else {
            $user->delete();
            return response()->json([
                'message' => 'User deleted successfully',
            ], 200);
        }
    }
    public function Accept_user($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update(['status' => 'approved']);

        return response()->json(['message' => 'User accepted successfully'], 200);
    }
    public function Reject_user($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        $user->update(['status' => 'rejected']);
        return response()->json([
            'message' => 'User rejected successfully',
        ], 200);
    }

    public function get_user($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        return new UserResource($user);
    }

    public function getAllPendingsUsers()
    {
        $pendingUsers = User::where('status', 'pending')->get();

        if ($pendingUsers->isEmpty()) {
            return response()->json([
                'message' => 'There are no pending users'
            ], 404);
        }
        return UserResource::collection($pendingUsers);
    }
}
