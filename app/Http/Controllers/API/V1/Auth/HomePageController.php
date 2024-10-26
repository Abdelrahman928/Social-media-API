<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\api\v1\PostResource;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class HomePageController extends Controller
{
    public function __invoke(){
        $user = Auth::user();
        $followedUserIds = $user->following()->pluck('id');

        $posts = Post::with('media', 'originalPost.media')
            ->whereIn('user_id', $followedUserIds)
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(9);

        if($posts->isEmpty()){
            return response()->json([
                'status' => 404,
                'message' => 'follow people to see their posts.'
            ], 404);
        }
        
        return response()->json([
            'status' => 200,
            'posts' => PostResource::collection($posts)
        ], 200);
    }
}
