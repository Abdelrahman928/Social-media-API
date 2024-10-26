<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\SearchRequest;
use App\Http\Resources\api\v1\PostResource;
use App\Http\Resources\api\v1\UserResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function postSearch(SearchRequest $request){
        $user = Auth::user();
        $search = $request->validated();
        $followedUserIds = $user->following()->pluck('id');
        $blockedUserIds = $user->blocks()->pluck('id');
        $blockedByUserIds = $user->blockedBy()->pluck('id');

        $posts = Post::with(['originalPost.media', 'share', 'media', 'likes', 'comments'])
            ->whereNotIn('user_id', $blockedUserIds)
            ->whereNotIn('user_id', $blockedByUserIds)
            ->whereRaw("MATCH(body) AGAINST(? IN NATURAL LANGUAGE MODE)", [$search])
            ->withCount(['likes', 'comments', 'shares'])
            ->orderByRaw('CASE WHEN user_id IN (' . $followedUserIds->implode(',') . ') THEN 1 ELSE 0 END DESC') 
            ->orderByRaw('(likes_count + comments_count + shares_count) DESC') 
            ->orderBy('created_at', 'desc') 
            ->cursorPaginate(10);

        if($posts->isEmpty()){
            return response()->json([
                'status' => 404,
                'message' => "no results found for {$search}"
            ], 404);
        }else{
            return response()->json([
                'status' => 200,
                'search_results' => PostResource::collection($posts)
            ], 200);
        }
    }

    public function mostRecentFilter(SearchRequest $request){
        $user = Auth::user();
        $blockedUserIds = $user->blockedUsers()->pluck('id');
        $blockedByUserIds = $user->blockedBy()->pluck('id');
        $search = $request->validated();

        $result = Post::with('media', 'original_post.media')
            ->whereNotIn('user_id', $blockedUserIds)
            ->whereNotIn('user_id', $blockedByUserIds)
            ->whereRaw("MATCH(body) AGAINST(? IN NATURAL LANGUAGE MODE)", [$search])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        if($result->isEmpty()){
            return response()->json([
                'status' => 404,
                'message' => "no results found for {$search}"
            ], 404);
        }else{
            return response()->json([
                'status' => 200,
                'search_results' => PostResource::collection($result)
            ], 200);
        }
    }

    public function peopleSearch(SearchRequest $request){
        $user = Auth::user();
        $search = $request->validated();
        $blockedUserIds = $user->blockedUsers()->pluck('id');
        $blockedByUserIds = $user->blockedBy()->pluck('id');

        $users = User::with('media')
            ->whereNotIn('id', $blockedUserIds)
            ->whereNotIn('id', $blockedByUserIds)
            ->whereRaw("MATCH(first_name, last_name, username) AGAINST(? IN NATURAL LANGUAGE MODE)", [$search])
            ->paginate(10);

        if($users->isEmpty()){
            return response()->json([
                'status' => 404,
                'message' => "no results found for {$search}"
            ], 404);
        }else{
            return response()->json([
                'status' => 200,
                'search_results' => UserResource::collection($users)
            ], 200);
        }
    }
}
