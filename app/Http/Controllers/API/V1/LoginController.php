<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\EmailLoginRequest;
use App\Http\Requests\api\v1\PhoneLoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function emailLogin(EmailLoginRequest $request){

        $attributes = $request->validated();

        if (! Auth::attempt($attributes)) {
            return response()->json(['error' => 'Sorry, those credentials do not match.', 422]);
        }

        $user = User::where('email', $request->email)->first();

        $device = substr($request->userAgent() ?? ' ', 0, 255);
        return response()->json([
            'message' => 'user logged in successfully', 200,
            'access_token' => $user->createToken($device)->plainTextToken
        ]);
    }

    public function phoneLogin(PhoneLoginRequest $request){

        $attributes = $request->validated();

        if (! Auth::attempt($attributes)) {
            return response()->json(['error' => 'Sorry, those credentials do not match.'], 422);
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        $device = substr($request->userAgent() ?? ' ', 0, 255);
        return response()->json([
            'access_token' => $user->createToken($device)->plainTextToken
        ]);
    }

    public function logoutUser(){

        $user = Auth::user();
        if ($user && $user->currentAccessToken()) {

            $user->currentAccessToken()->delete();
    
            return response()->json(['success' => 'Logged out.'], 200);
        }
        
        return response()->json(['error' => 'No active session found.'], 401);
    }
}
