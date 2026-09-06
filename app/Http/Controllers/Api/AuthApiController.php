<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    /**
     * Authenticate user credentials and issue a real Laravel Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $username = trim((string) $request->input('username', ''));
        $password = trim((string) $request->input('password', ''));

        if (empty($username) || empty($password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username/email dan kata sandi wajib diisi.'
            ], 422);
        }

        // 1. Query real database User by username OR email
        $user = User::where('username', $username)
            ->orWhere('email', $username)
            ->first();

        // 2. User not found -> 401
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password tidak sesuai.'
            ], 401);
        }

        // 3. Verify password hash against database
        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password tidak sesuai.'
            ], 401);
        }

        // 4. Verify account is active -> 403
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif.'
            ], 403);
        }

        // 5. Update last login timestamp
        $user->update([
            'last_login_at' => now(),
        ]);

        // 6. Issue real Laravel Sanctum token
        $user->tokens()->delete();
        $token = $user->createToken('siuba-web')->plainTextToken;

        // 7. Load Spatie roles and permissions
        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');
        $permissionArray = $permissions->toArray();

        // Wildcard access for super-admin
        if ($roles->contains('super-admin') || $roles->contains('super_admin')) {
            if (!in_array('*', $permissionArray)) {
                $permissionArray[] = '*';
            }
        }

        $safeUserData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'roles' => $roles,
            'role' => $roles->first() ?? 'user',
            'permissions' => $permissionArray,
            'is_active' => (bool) $user->is_active,
            'last_login_at' => $user->last_login_at?->toISOString() ?? $user->last_login_at,
        ];

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $safeUserData,
            'message' => 'Login berhasil.'
        ], 200);
    }

    /**
     * Get current authenticated user profile and permissions from Sanctum token.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');
        $permissionArray = $permissions->toArray();

        if ($roles->contains('super-admin') || $roles->contains('super_admin')) {
            if (!in_array('*', $permissionArray)) {
                $permissionArray[] = '*';
            }
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $roles,
                'role' => $roles->first() ?? 'user',
                'permissions' => $permissionArray,
                'is_active' => (bool) $user->is_active,
                'last_login_at' => $user->last_login_at?->toISOString() ?? $user->last_login_at,
            ]
        ], 200);
    }

    /**
     * Invalidate current Sanctum session token.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()?->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.'
        ], 200);
    }

    /**
     * Update user password in database immediately.
     */
    public function updatePassword(Request $request, $id): JsonResponse
    {
        $password = trim((string) $request->input('password', ''));
        $username = trim((string) $request->input('username', ''));

        if (empty($password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password wajib diisi.'
            ], 422);
        }

        // Find user by ID or by username
        $user = is_numeric($id) ? User::find((int) $id) : null;
        if (!$user && !empty($username)) {
            $user = User::where('username', $username)->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengguna tidak ditemukan.'
            ], 404);
        }

        $user->password = $password; // 'hashed' cast in User model auto-hashes
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Password untuk @{$user->username} berhasil disimpan dan dapat langsung digunakan untuk login.",
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
            ]
        ], 200);
    }

    /**
     * Update user profile & credentials.
     */
    public function updateUser(Request $request, $id): JsonResponse
    {
        $user = is_numeric($id) ? User::find((int) $id) : null;
        $username = trim((string) $request->input('username', ''));

        if (!$user && !empty($username)) {
            $user = User::where('username', $username)->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengguna tidak ditemukan.'
            ], 404);
        }

        if ($request->has('name')) {
            $user->name = (string) $request->input('name');
        }
        if ($request->has('email')) {
            $user->email = (string) $request->input('email');
        }
        if ($request->has('is_active')) {
            $user->is_active = (bool) $request->input('is_active');
        }
        if ($request->filled('password')) {
            $user->password = (string) $request->input('password'); // 'hashed' cast auto-hashes
        }

        $user->save();

        if ($request->filled('role')) {
            $role = (string) $request->input('role');
            $user->syncRoles([$role]);
        }

        return response()->json([
            'success' => true,
            'message' => "Data pengguna @{$user->username} berhasil diperbarui.",
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => (bool) $user->is_active,
                'roles' => $user->getRoleNames(),
            ]
        ], 200);
    }

    /**
     * Create new user with hashed password.
     */
    public function createUser(Request $request): JsonResponse
    {
        $username = trim((string) $request->input('username', ''));
        $name = trim((string) $request->input('name', ''));
        $password = trim((string) $request->input('password', 'password'));
        $role = trim((string) $request->input('role', 'guru'));
        $email = trim((string) $request->input('email', "{$username}@utsman.sch.id"));

        if (empty($username) || empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama dan username wajib diisi.'
            ], 422);
        }

        $user = User::updateOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $email,
                'password' => $password, // 'hashed' cast in User model auto-hashes
                'is_active' => (bool) $request->input('is_active', true),
            ]
        );

        $user->syncRoles([$role]);

        return response()->json([
            'success' => true,
            'message' => "Pengguna @{$user->username} berhasil didaftarkan.",
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => (bool) $user->is_active,
                'roles' => $user->getRoleNames(),
            ]
        ], 201);
    }
}
