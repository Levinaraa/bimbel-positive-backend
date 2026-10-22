<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    // =========================================================================
    // 1. REGISTER
    // =========================================================================
    
public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:8',
        'phone' => 'nullable|string|max:20',
        'role' => 'nullable|in:student,guru',
        'jenis_pembelajaran' => 'required_if:role,student|nullable|in:online,offline',
    ]);

    $role = $validated['role'] ?? 'student';

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'] ?? null,
        'password' => Hash::make($validated['password']),
        'role' => $role,
        'jenis_pembelajaran' => $role === 'student'
            ? ($validated['jenis_pembelajaran'] ?? null)
            : null,
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Registrasi berhasil',
        'access_token' => $token,
        'token_type' => 'Bearer',
        'user' => $user,
    ], 201);
}


    // =========================================================================
    // 2. LOGIN
    // =========================================================================
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Login berhasil',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user
        ]);
    }

    // =========================================================================
    // 3. LOGOUT
    // =========================================================================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    // =========================================================================
    // 4. GET USER PROFILE
    // =========================================================================
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    // =========================================================================
    // 5. UPDATE PROFILE
    // =========================================================================
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email,' . $user->id,
            'phone'  => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048'
        ]);

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? $user->phone;

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user'    => $user
        ]);
    }

    // =========================================================================
    // 6. GET TUTORS (PUBLIC) -- [INI YANG SEBELUMNYA HILANG]
    // =========================================================================
    public function tutors()
    {
        // Mengambil semua user yang memiliki role 'guru'
        // Anda bisa menambahkan ->select(...) jika ingin membatasi kolom yang dikirim
        $tutors = User::where('role', 'guru')
                    ->latest()
                    ->take(6) // Ambil 6 guru terbaru
                    ->get();

        return response()->json($tutors);
    }
}