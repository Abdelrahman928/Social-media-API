<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\PostRequest;
use App\Http\Resources\api\v1\PostResource;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Notifications\TagNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use PhpParser\Node\Expr\Empty_;

class PostController extends Controller
{
    public function create(PostRequest $request){
        $user = Auth::user();
        $postContent = $request->validated('body');
        $post = Post::create([
            'content' => $postContent, 
            'user_id' => $user->id, 
        ]);

        preg_match_all('/@([a-zA-Z0-9_]+)/', $postContent, $matches);
        $mentions = $matches[1];
        $mentionedUsers = User::whereIn('username', $mentions)->get();

        if($mentionedUsers->isEmpty()){
            return response()->json([
                'status' => 404,
                'message' => 'no such user name found.'
            ], 404);
        }

        foreach($mentionedUsers as $mUser){
            $mUser->notify(new TagNotification($user, $post));
        }

        $images = $request->validated('images');
        if (!empty($images)) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('media/images', 'public');
    
                Media::create(['media_path' => $path, 'post_id' => $post->id]);
            }
        }

        $videos = $request->validated('videos');
        if (! empty($videos)) {
            foreach ($request->file('videos') as $video) {
                $path = $video->store('media/videos', 'public');
    
                Media::create(['media_path' => $path, 'post_id' => $post->id]);
            }
        }

        $pdf = $request->validated('pdf');
        if (!empty($pdf)) {
            $path = $pdf->store('media/pdfs', 'public');

            Media::create(['media_path' => $path, 'post_id' => $post->id]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'posted successfully.',
            'post' => $post
        ], 201);
    }

    public function show(Post $post){
        Gate::authorize('view', $post);
        $post->load('media', 'comment');

        return response()->json([
            'status' => 200,
            'post' => new PostResource($post)
        ]);
    }

    public function showShares(Post $post){
        Gate::authorize('view', $post);
        $user = Auth::user();
        $blockedUsers = $user->blocks()->pluck('id');

        $shares = Post::with('media')
            ->where('original_post_id', $post->id)
            ->whereNotIn('user_id', $blockedUsers)
            ->paginate(10);

        if($shares->isEmpty()){
            return response()->json([
                'status' => 404,
                'message' => 'no shares for this post.'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'shares' => PostResource::collection($shares)
        ], 200);
    }

    public function update(PostRequest $request, Post $post){
        Gate::authorize('view', $post);

        Gate::authorize('update', $post);
        $post->update($request->validated('body'));

        return response()->json([
            'status' => 200,
            'message' => 'post updated successfully.',
        ], 200);
    }

    public function destroy(Post $post){
        Gate::authorize('view', $post);
        Gate::authorize('update', $post);
        $post->delete();

        return response()->json([
            'status' => 200,
            'message' => 'post deleted successfully.'
        ], 200);
    }
}
