<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\FollowNotification;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggleFollow(User $user){
        $currentUser = Auth::user();
        $result = $currentUser->follow($user);
        
        if ($result === 'followed') {
            $user->notify(new FollowNotification($currentUser));
            return response()->json([
                'status' => 201,
                'message' => 'Following.'
            ], 201);
        }elseif($result === 'unfollowed'){
            return response()->json([
                'status' => 200,
                'message' => 'unfollowing.'
            ], 200);
        }
    
        return response()->json([
            'status' => 400,
            'message' => 'Action could not be completed.'
        ], 400);
    }
}
