<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\ChangePasswordRequest;
use App\Http\Requests\api\v1\ForgotPasswordEmailRequest;
use App\Http\Requests\api\v1\ForgotPasswordPhoneRequest;
use App\Http\Requests\api\v1\VerificationRequest;
use App\Models\User;
use App\Notifications\SendVerifySMS;
use App\Notifications\VerifyEmailNotification;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Auth;

class ForgotPasswordController extends Controller
{
    public function sendResetPasswordRequestToEmail(ForgotPasswordEmailRequest $request){
        $email = $request->validated();

        $user = User::where('email', $email)->first();

        if($user){
            $otp = (new Otp)->generate($email, 'numeric', '6', '5');
            $device = substr($request->userAgent() ?? ' ', 0, 255);
            $user->notify(new VerifyEmailNotification($otp));
            
            return response()->json([
                'status' => 200,
                'message' => 'a verification code has been sent successfully',
                'acess_token' => $user->createToken($device, ['password:reset'], now()->addMinutes(5))->plainTextToken
            ], 200);
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'no user found for the provided email address.'
            ], 404);
        }
    }

    public function sendResetPasswordRequestToPhone(ForgotPasswordPhoneRequest $request){
        $Phone = $request->validated();

        $user = User::where('email', $Phone)->first();

        if($user){
            $otp = (new Otp)->generate($Phone, 'numeric', '6', '5');
            $device = substr($request->userAgent() ?? ' ', 0, 255);
            $user->notify(new VerifyEmailNotification($otp));
            
            return response()->json([
                'status' => 200,
                'message' => 'a verification code has been sent successfully',
                'acess_token' => $user->createToken($device, ['password:reset'], now()->addMinutes(5))->plainTextToken
            ], 200);
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'no user found for the provided phone number.'
            ], 404);
        }
    }
}
