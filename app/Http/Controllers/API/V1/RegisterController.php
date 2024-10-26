<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\RegisterRequest;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Ichtrojan\Otp\Otp;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request){

        $user = User::create($request->validated());
            
        $otp = (new Otp())->generate($request->email , 'numeric', 6, 15);

        $user->notify(new VerifyEmailNotification($otp));

        $device = substr($request->userAgent() ?? ' ', 0, 255);

        return response()->json([
            'status' => 200,
            'message' => 'User created successfully. Verify your email to complete registration.',
            ,'access_token' => $user->createToken($device)->plainTextToken
        ], 200);
    }
}
