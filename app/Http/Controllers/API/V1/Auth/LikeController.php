<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\api\v1\LikeResource;
use App\Models\Like;
use App\Models\Post;
use App\Notifications\LikeNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LikeController extends Controller
{
    public function create(Post $post){
        Gate::authorize('view', $post);
        $user = Auth::user();
        $like = $post->like()->where(['user_id' => $user->id])->first();

        if(! $like){
            $like = $post->like()->create(['user_id' => $user->id]);

            $post->user->notify(new LikeNotification($like));
            
            return response()->json([
                'status' => 201,
                'message' => 'post liked.'
            ], 201);
        }

        $like->delete();

        return response()->json([
            'status' => 204,
            'message' => 'post unliked.'
        ], 204);
    }

    public function index(Post $post){
        Gate::authorize('view', $post);
        $likes = Like::with('user')->where('post_id', $post->id)->cursorePaginate(20);

        if($likes->isEmpty()){
            return response()->json([
                'status' => 404,
                'message' => 'no likes found for this post.'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'likes' => LikeResource::collection($likes)
        ]);
    }
}
