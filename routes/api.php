<?php

use App\Http\Controllers\api\v1\auth\BlockController;
use App\Http\Controllers\api\v1\auth\HomePageController;
use App\Http\Controllers\api\v1\auth\NotificationController;
use App\Http\Controllers\api\v1\auth\SearchController;
use App\Http\Controllers\api\v1\auth\SettingController;
use App\Http\Controllers\api\v1\auth\UserProfileController;
use App\Http\Controllers\api\v1\auth\VerifyUserController;
use App\Http\Controllers\api\v1\ForgotPasswordController;
use App\Http\Controllers\api\v1\LoginController;
use App\Http\Controllers\api\v1\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function(){

    Route::middleware('throttle:login')->group(function(){
        Route::controller(LoginController::class)->group(function(){
            Route::post('/email-login',  'emailLogin');
            Route::post('/Phone-number-login', 'phoneLogin');
        });
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetPasswordRequest']);
    });

    Route::middleware('throttle:login')->group(function(){
        Route::controller(RegisterController::class)->group(function(){
            Route::post('/register', 'register');
        });
    });
    
    Route::middleware(['auth:sanctum', 'isVerified'])->group(function(){
        Route::prefix('forgot-password')
        ->group(function(){
            Route::controller(ForgotPasswordController::class)
            ->middleware('throttle:forgot_password')
            ->group(function(){
                Route::post('/forgot-password/verify', 'verify');
                Route::post('/forgot-password/reset-password', 'resetPassword');
            });

            Route::controller(VerifyUserController::class)
            ->middleware('throttle:resend_code')
            ->group(function(){
                Route::post('/forgot-password/verify/resend_code_email', 'resendVerificationOtpToEmail');
                Route::post('/forgot-password/verify/resend_code_sms', 'resendVerificationOtpToPhone');
            });
        });
        
        Route::middleware('throttle:api')->group(function(){
            Route::controller(VerifyUserController::class)->group(function(){
                Route::post('/verify-email', 'verifyEmail');
                Route::post('/verify-Phone-number', 'verifyPhone');
            });
        });

        Route::prefix('settings')->group(function(){
            Route::controller(SettingController::class)->group(function(){
                Route::post('/settings/email', 'changeEmail')->middleware('throttle:api');
                Route::post('/settings/password', 'changePassword')->middleware('throttle:api');
                Route::post('/settings/phone_number', 'verifyDeletePhone')->middleware('throttle:api');
                Route::post('/settings/phone_number_delete', 'deletePhone')->middleware('throttle:api');
                Route::post('/settings/add_phone_number', 'addPhone')->middleware('throttle:api');
                Route::patch('/settings/cancel_phone_verification', 'cancelPhoneVerification')->middleware('throttle:api');
                Route::post('/settings/username', 'changeUsername')->middleware('throttle:api');
                Route::get('/settings/logins/manage_devices', 'manageLogins');
                Route::delete('/settings/logins/manage/{tokenId}/logout_a_device', 'revokeToken');
            });
            Route::post('/settings/verify_phone_number', [VerifyUserController::class, 'verifyPhone'])->middleware('throttle:forgot_password');
            Route::get('settings/block_list', [BlockController::class, 'index']);
            Route::get('settings/block_list/{user}/unblock', [BlockController::class, 'destroy']);
        });

        Route::prefix('user_profiles')->group(function(){
            Route::controller(UserProfileController::class)->group(function(){
                Route::get('/{user}/profile', 'show');
                Route::post('/profile/picture', 'addProfilePicture');
                Route::delete('/profile/picture', 'removeProfilePicture');
            });
            Route::post('/{user}/profile/block_user', [BlockController::class, 'create']);
            Route::post('/{user}/profile/unblock_user', [BlockController::class, 'destroy']);
        });

        Route::get('/', HomePageController::class);

        Route::prefix('search')->controller(SearchController::class)->group(function(){
            Route::get('/search/posts', 'postSearch');
            Route::get('/search/posts/most-recent', 'mostRecentFilter');
            Route::get('/search/users', 'peopleSearch');
        });

        Route::prefix('notifications')->controller(NotificationController::class)->group(function(){
            Route::get('/notifications', 'index');
            Route::delete('/notifications', 'destroy');
        });
    });
});
