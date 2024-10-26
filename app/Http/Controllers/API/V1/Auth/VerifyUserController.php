<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\VerificationRequest;
use App\Notifications\SendVerifySMS;
use App\Notifications\VerifyEmailNotification;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Auth;

class VerifyUserController extends Controller
{
    public function verifyEmail(VerificationRequest $request){
        
        $user = Auth::user();
        $otpv = (new Otp)->validate($user->email, $request->otp);

        $isValid = $otpv->status;
        $message = $otpv->message;
        
            if(! $isValid)
            {
                return response()->json([
                    'status' => 403,
                    $message], 403);
            }
        $user->markEmailAsVerified();
        return response()->json([
            'status' => 200,
            'message' => 'email verified successfully'], 200);
    
    }

    public function verifyPhone(VerificationRequest $request){

        $user = Auth::user();
        $otpv = (new Otp)->validate($user->phone_number, $request->otp);

        $isValid = $otpv->status;
        $message = $otpv->message;
        
            if(! $isValid)
            {
                return response()->json([
                    'status' => 403,
                    $message], 403);
            }
            
        $user->markPhoneAsVerified();

        return response()->json([
            'status', 200,
            'message' => 'phone number verified successfully'], 200);
    }

    public function resendVerificationOtpToEmail(){
        $user = Auth::user();

        $otp = (new Otp())->generate($user->email , 'numeric', 6, 15);
        $user->notify(new VerifyEmailNotification($otp));

        return response()->json([
            'status' => 200,
            'message' => 'new verification code sent to your email.',
        ], 200);
    }

    public function resendVerificationOtpToPhone(){
        $user = Auth::user();

        $otp = (new Otp())->generate($user->phone_number , 'numeric', 6, 15);
        $user->notify(new SendVerifySMS($otp));

        return response()->json([
            'status' => 200,
            'message' => 'new verification code sent to your phone number via SMS.',
        ], 200);
    }
}
