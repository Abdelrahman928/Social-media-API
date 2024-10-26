<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\ProfilePictureRequest;
use App\Http\Resources\api\v1\PostResource;
use App\Http\Resources\api\v1\UserProfileResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function show(User $user){
        $currentUser = Auth::user();
        if($currentUser->blocks()->where('blocked_id', $user->id)->exists()){
            return response()->json([
                'status' => 400,
                'message' => "you have blocked {$user->username}."
            ], 400);
        }elseif($currentUser->blockedBy()->where('blocker_id', $user->id)->exists()){
            return response()->json([
                'status' => 400,
                'message' => "{$user->username} has blocked you."
            ], 400);
        }
        $userWithMedia = $user->load('media');
        $profile = new UserProfileResource($userWithMedia);
        $posts = Post::with('media', 'originalPost.media')->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(10);

        return response()->json([
            'status' => 200,
            'user_info' => $profile,
            'posts' => PostResource::collection($posts)
        ], 200);
    }

    public function addProfilePicture(ProfilePictureRequest $request){
        $user = Auth::user();
        $newProfilePicture = $request->validated();
        $file = $newProfilePicture->file('profile_picture');

        $filename = time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('media/profile_pictures', $filename, 'public');
        $user->media()->updateOrCreate(
            ['media_type' => 'profile_picture'],
            ['file_path' => $path]
        );

        return response()->json([
            'status' => 200,
            'message' => 'Profile picture uploaded successfully.',
            'data' => [
                'file_path' => $path,
            ],
        ], 200);
    }

    public function removeProfilePicture(){
        $user = Auth::user();

        $profilePicture = $user->media()->where('media_type', 'profile_picture')->first();

        if ($profilePicture) {
            $profilePicture->delete();
            
            $user->defaultProfilepic();
            
            return response()->json([
                'status' => 200,
                'message' => 'Profile picture removed successfully.',
            ], 200);
        }

        return response()->json([
            'status' => 404,
            'message' => 'No profile picture found to remove.',
        ], 404);
    }
}
