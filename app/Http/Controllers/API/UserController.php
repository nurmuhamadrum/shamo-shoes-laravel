<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['nullable', 'string', 'max:255'],
                'password' => [
                    'required',
                    'string',
                    Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                ]
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]);

            // $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User Register Success');
        } catch (Exception $error) {
            return ResponseFormatter::error(
                $error,
                'Authentication Failed',
                500
            );
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return ResponseFormatter::error(
                'Invalid credentials',
                null,
                401
            );
        }

        $user = Auth::user();

        $token = $user->createToken('authToken')->plainTextToken;

        return ResponseFormatter::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 'Authenticated');
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'Get Data User Successfully');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $user->update($request->only([
            'name',
            'email',
            'phone_number'
        ]));

        return ResponseFormatter::success(
            $user->fresh(),
            'Profile Updated'
        );
    }

    public function logout(Request $request)
    {
        $accessToken = $request->user()?->currentAccessToken();

        if ($accessToken && method_exists($accessToken, 'delete')) {
            $accessToken->delete();
        }

        return ResponseFormatter::success(null, 'Token Revoked');
    }
}
