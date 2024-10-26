<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\ShareRequest;
use App\Http\Resources\api\v1\PostShowResource;
use App\Models\Post;
use App\Notifications\ShareNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ShareController extends Controller
{
    public function create(ShareRequest $request, Post $post){
        Gate::authorize('view', $post);
        $user = Auth::user();

        $user->post()->create(array_merge( $request->validated(), ['original_post_id' => $post->id]));

        $post->user->notify(new ShareNotification($user, $post));

        return response()->json([
            'status' => 201,
            'message' => 'post shared to your profile successfully.'
        ], 201);
    }

    public function show(Post $post){
        Gate::authorize('view', $post);
        $shares = $post->load('originalPost.media', 'comment');

        if($shares->isEmpty()){
            return response()->json([
                'status' => 404,
                'message' => 'post not found'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'post' => new PostShowResource($post)
        ], 200);
    }
}
