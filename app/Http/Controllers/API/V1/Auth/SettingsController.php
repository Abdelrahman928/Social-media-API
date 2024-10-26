<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\ChangePasswordRequest;
use App\Http\Requests\api\v1\EmailLoginRequest;
use App\Http\Requests\api\v1\EnterPhoneRequest;
use App\Http\Requests\api\v1\ProfilePictureRequest;
use App\Http\Requests\api\v1\RegisterRequest;
use App\Http\Requests\api\v1\VerificationRequest;
use App\Http\Resources\api\v1\LoginManagementResource;
use App\Notifications\SendVerifySMS;
use App\Notifications\VerifyEmailNotification;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function changeEmail(EmailLoginRequest $request){
        $user = Auth::user();

        $newEmail = $request->validated('email');

        $user->update(['email'=> $newEmail, 'email_verified_at' => null]);
        $otp = (new Otp())->generate($request->email , 'numeric', 6, 5);
        $user->notify(new VerifyEmailNotification($otp));

        return response()->json([
            'status' => 200,
            'message' => 'Email updated successfully, please verify your email.'
        ], 200);
    }

    public function changePassword(ChangePasswordRequest $request){
        $user = Auth::user();
        $newPassword = $request->validated();

        if($user->password !== $newPassword->current_password){
            return response()->json([
                'status' => 422,
                'error' => 'you entered a wrong password'
            ], 422);
        }

        $user->update(['password' => $newPassword->new_password]);
        $user->tokens()->delete();

        return response()->json([
            'status' => 200, 
            'message' => 'password updated successfully'
        ], 200);
    }

    public function verifyDleletePhone(){
        $user = Auth::user();
        $userPhone = $user->phone_number;
        
        $otp = (new Otp())->generate($userPhone, 'numeric', '6', '5');
        $user->notify(new SendVerifySMS($otp));

        return response()->json([
            'status' => 200,
            'message' => 'verification code sent to your phone number.'
        ], 200);
    }

    public function deletePhone(VerificationRequest $request){
        $user = Auth::user();
        $otp = $request->validated();
        (new Otp())->validate($user->phone_number, $otp->code);

        $user->phone_number = null;
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'phone number removed successfully'
        ], 200);
    }

    public function cancelPhoneVerification(){
        $user = Auth::user();
        $user->phone_number = null;
        $user->save();
    }

    public function addPhone(EnterPhoneRequest $request){
        $user = Auth::user();
        $phone = $request->validated();
  
        $otp = (new Otp())->generate($request->phone_number , 'numeric', 6, 15);
        $user->notify(new SendVerifySMS($otp));
        
        $user->update(['phone_number' => $phone]);
        $this->notify(new SendVerifySMS($otp));

        return response()->json([
            'status' => 200,
            'message' => 'Verify your phone number.'
        ], 200);
    }

    public function changeUsername(RegisterRequest $request){
        $user = Auth::user();
        $newUsername = $request->validated('username');

        $user->update(['username' => $newUsername]);

        return response()->json([
            'status' => 200,
            'message' => 'username updated successfully'
        ], 200);
    }

    public function manageLogins(){
        $user = Auth::user();

        $logins = $user->tokens()->get();

        return response()->json([
            'status' => 200,
            'logins' => LoginManagementResource::collection($logins)
        ], 200);
    }

    public function revokeToken($tokenId){
        $user = Auth::user();
        $token = $user->tokens()->where('id', $tokenId)->first();

        if (!$token) {
            return response()->json([
                'status' => 403,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $token->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Logged out from device successfully',
            ], 200);
    }
}
