<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

public function get_All_Users(Request $request)
{
    try {
        $users = User::orderBy('created_at', 'desc')->get();

        $usersData = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->first_name . ' ' . $user->last_name,
                'phone' => $user->phone,
              'birth_date' => $user->birth_date,
                'status' => $user->status,
                'profile_image_url' => $user->profile_image
                    ? asset(str_replace('public/', 'storage/', $user->profile_image))
                    : null,
            ];
        });

        return response()->json([
            'message' => 'Get all users success',
            'data' => $usersData
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to get users',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function login_admin(Request $request){
    $request->validate([
'phone'=>'required|string',
'password'=>'required|string|min:8'
    ]);
   $admin = Admin::where('phone', $request->phone)->first();

if (!$admin || !Hash::check($request->password, $admin->password)) {
    return response()->json(['message'=>'Invalid phone or password'], 401);
}
        $admin = Admin::where('phone', $request->phone)->firstOrFail();
$token = $admin->createToken('admin-token', ['admin'])->plainTextToken;

return response()->json([
        'message' => 'Admin login success',
        'admin' => $admin,
        'token' => $token
    ]);
}
   Public function delete_user($id){
$user=User::find($id);
if(!$user){
    return response()->json([
        'message'=>'user not fond'
    ],404);
}
else{
    $user->delete();
    return response()->json([
        'message'=>'user deleted success',
    ],200);
}
   }
public function Accept_user($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $user->update(['enum' => 'approved']);

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
    $user->update(['enum' => 'approved']);
    return response()->json([
        'message' => 'User rejected successfully',
    ], 200);
}


}

            

