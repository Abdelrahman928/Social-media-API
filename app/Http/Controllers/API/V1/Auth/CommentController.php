<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\CommentRequest;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommentNotification;
use App\Notifications\LikeNotification;
use App\Notifications\TagNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function create(CommentRequest $request, Post $post){
        $user = Auth::user();
        $commentContent = $request->validated('content');
        $comment = Comment::create([
            'content' => $commentContent, 
            'user_id' => $user->id, 
            'post_id' => $post->id
        ]);

        preg_match_all('/@([a-zA-Z0-9_]+)/', $commentContent, $matches);
        $mentions = $matches[1];
        $mentionedUsers = User::whereIn('username', $mentions)->get();

        if($mentionedUsers->isEmpty()){
            return response()->json([
                'status' => 404,
                'message' => 'no such user name found.'
            ], 404);
        }

        foreach($mentionedUsers as $mUser){
            $mUser->notify(new TagNotification($user, $comment));
        }

        $validatedGifs = $request->validated('GIF');
       
        $path = $validatedGifs->store('media/images', 'public');
        Media::create(['media_path' => $path, 'comment_id' => $comment->id]);

        $post->user->notify(new CommentNotification($user, $post));

        return response()->json([
            'status' => 201,
            'message' => 'comment posted.'
        ], 201);
    }

    public function like(Post $post, Comment $comment){
        Gate::authorize('view', $post);
        $user = Auth::user();
        $like = $comment->like()->where(['user_id' => $user->id])->first();

        if(! $like){
            $like = $comment->like()->create(['user_id' => $user->id]);

            $comment->user->notify(new LikeNotification($like));

            return response()->json([
                'status' => 201,
                'message' => 'comment liked.'
            ], 201);
        }

        $like->delete();

        return response()->json([
            'status' => 201,
            'message' => 'comment unliked.'
        ], 201);
    }

    public function update(CommentRequest $request, Post $post, Comment $comment){
        Gate::authorize('view', $post);
        Gate::authorize('update', $comment);
        $comment->update($request->validated('content'));

        return response()->json([
            'status' => 200,
            'message' => 'comment updated successfully'
        ], 200);
    }

    public function destroy(Post $post, Comment $comment){
        Gate::authorize('view', $post);
        Gate::authorize('delete', $comment);
        $comment->delete();

        return response()->json([
            'status' => 200,
            'message' => 'comment deleted successfully'
        ], 200);
    }
}
