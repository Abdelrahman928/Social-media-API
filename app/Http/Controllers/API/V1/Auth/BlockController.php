<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\api\v1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    public function create(User $user){
        $currentUser = Auth::user();

        if ($currentUser->id === $user->id) {
            return response()->json([
                'status' => 400,
            ], 400);
        }

        if($currentUser->follower()->where('follower_id', $user->id)->exists()){
            $currentUser->follower()->detach([$user->id]);
        }elseif($currentUser->following()->where('following_id', $user->id)){
            $currentUser->follower()->detach([$user->id]);
        }
        
        $currentUser->blocks()->syncWithoutDetaching([$user->id]);

        return response()->json([
            'status' => 200,
            'message' => "You have blocked {$user->username}."
        ], 200);
    }

    public function index(){
        $user = Auth::user();

        $blocks = $user->blocks()->get();

        return response()->json([
            'status' => 200,
            'blocks' => UserResource::collection($blocks)
        ]);
    }

    public function destroy(User $user){
        $currentUser = Auth::user();

        if(! $currentUser->blocks()->where('blocked_id', $user->id)->exists()){
            return response()->json([
                'status' => 400,
                'message' => 'unprocessable.'
            ], 400);
        }
        $currentUser->blocks()->detach([$user->id]);

        return response()->json([
            'status' => 200,
            'message' => "unblocked."
        ], 200);
    }
}
