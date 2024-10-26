<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Notifications\SendVerifySMS;
use App\Notifications\VerifyEmailNotification;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResendCodeController extends Controller
{
    public function reSendResetPasswordRequestToPhone(){
        $user = Auth::user();
        $phone = $user->phone_number;

        $otp = (new Otp)->generate($phone, 'numeric', '6', '5');
        $user->notify(new SendVerifySMS($otp));
        
        return response()->json([
            'status' => 200,
            'message' => 'a verification code has been sent successfully',
        ], 200);
    }

    public function reSendResetPasswordRequestToEmail(){
        $user = Auth::user();
        $email = $user->email;

            $otp = (new Otp)->generate($email, 'numeric', '6', '5');
            $user->notify(new VerifyEmailNotification($otp));
            
            return response()->json([
                'status' => 200,
                'message' => 'a verification code has been sent successfully',
            ], 200);
    }
}
