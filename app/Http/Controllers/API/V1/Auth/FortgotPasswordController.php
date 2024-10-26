<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\ChangePasswordRequest;
use App\Http\Requests\api\v1\VerificationRequest;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Auth;

class FortgotPasswordController extends Controller
{
    public function verify(VerificationRequest $request){
        $user = Auth::user();
        (new Otp())->validate($user->email || $user->phone_number, $request->otp);

        return response()->json([
            'status' => 200,
            'message' => 'please proceed to reset your password'
        ],200);
    }

    public function resetPassword(ChangePasswordRequest $request){
        $user = Auth::user();
        $newPassword = $request->validated(['new_password']);

        $user->update(['password' => $newPassword]);
        $user->tokens()->delete();

        return response()->json([
            'status' => 200,
            'message' => 'your password has been reset successfully'
        ], 200);
    }
}
