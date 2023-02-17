<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        /** @var User $user */
        $user = User::query()->create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password)')),
            'balance_euros' => 0,
            'balance_cents' => 0,
        ]);

        $token = $user->createToken('auth_token');

        return response()->json([
            'message' => "Successfully registered as $user->name.",
            'token' => $token->plainTextToken
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        /** @var User $user */
        $user = User::query()->where('email', $request->get('email'))->first();

        if (!isset($user) || !Hash::check(request()->password, $user->password)) {
            return response()->json([
                'message' => 'These credentials do not match our records.',
            ], 401);
        }

        $token = $user->createToken('auth_token');

        return response()->json([
            'message' => "Successfully logged in as $user->name.",
            'token' => $token->plainTextToken
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }
}
