<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // ✅ For SP calls
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // Register (Create user) - Using SP
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Call InsertUser SP
        DB::statement("CALL InsertUser(?, ?, ?)", [
            $request->name,
            $request->email,
            Hash::make($request->password)
        ]);

        // Fetch back user via SP (safe binding)
        $userData = DB::select("CALL GetUserByEmail(?)", [$request->email]);

        if (!$userData || count($userData) === 0) {
            return response()->json(['error' => 'User not created'], 500);
        }

        $userData = $userData[0];

        // Convert to Eloquent model (needed for JWT)
        $user = User::find($userData->id);

        // Generate JWT
        $token = JWTAuth::fromUser($user);

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    // Login - Using SP
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Fetch user by email via SP
        $userData = DB::select("CALL GetUserByEmail(?)", [$credentials['email']]);

        if (!$userData || count($userData) === 0) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $userData = $userData[0];

        if (!Hash::check($credentials['password'], $userData->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Convert to Eloquent model for JWT
        $user = User::find($userData->id);
        $token = JWTAuth::fromUser($user);

        return response()->json(['token' => $token, 'user' => $user]);
    }

    // Profile
    public function profile()
    {
        return response()->json(auth()->user());
    }

    // Refresh Token
    public function refresh()
    {
        try {
            $newToken = auth()->refresh();
            return response()->json(['token' => $newToken, 'user' => auth()->user()]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token refresh failed'], 401);
        }
    }

    // Logout
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    // ========================
    // CRUD Operations with Stored Procedures
    // ========================

    // Read All Users
    public function index()
    {
        $users = DB::select("CALL GetAllUsers()");
        return response()->json($users);
    }

    // Read Single User
    public function show($id)
    {
        $user = DB::select("CALL GetUserById(?)", [$id]);
        if (!$user || count($user) === 0) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user[0]);
    }

    // Update User
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Call UpdateUser SP
        DB::statement("CALL UpdateUser(?, ?, ?, ?)", [
            $id,
            $request->name ?? '',
            $request->email ?? '',
            $request->password ? Hash::make($request->password) : ''
        ]);

        // Fetch updated user via SP
        $user = DB::select("CALL GetUserById(?)", [$id]);

        if (!$user || count($user) === 0) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user[0]);
    }

    // Delete User
    public function destroy($id)
    {
        DB::statement("CALL DeleteUser(?)", [$id]);

        return response()->json(['message' => 'User deleted successfully']);
    }
}
