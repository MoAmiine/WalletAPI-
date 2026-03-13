<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends Controller
{

    public function register(RegisterRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "success" => true,
            "message" => "User created",
            "data" => [
                "user" => $user,
                "token" => $token
            ]
        ], 201);
    }
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "message" => "Invalid credentials"
            ], 401);
        }
        $token = $user->createToken("auth_token")->plainTextToken;

        return response()->json([
            "success" => true,
            "message" => "Connexion réussie.",
            "data" => [
                "user" => $user,
                "token" => $token
            ]
        ], 200);
    }
    public function logout(Request $request)
    {

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "success" => true,
            "message" => "Déconnexion réussie."
        ], 200);
    }
    public function user(Request $request)
    {

        return response()->json([
            "success" => true,
            "message" => "Profil utilisateur récupéré.",
            "data" => [
                "user" => $request->user()
            ]
        ], 200);
    }
}
