<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiAuthController extends Controller
{
    public function login(LoginRequest $request)
    {

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' =>  "El email o la contraeña no coinciden con ningun usuario"
            ], 401);
        }
        $token = $user->createToken('postman')->plainTextToken;

        return response()->json([
            "token" => $token,
            "token_type" => "Bearer",
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "role" => $user->getRoleNames()->first()
            ]
        ]);
    }
}
