<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\ReportRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Notifications\ReportNotification;
use App\Notifications\ReportUserProfileNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function reportPost(ReportRequest $request, Post $post){
        Gate::authorize('view', $post);
        $reporter = Auth::user();

        Report::create($request->validated(), ['reporter_id', $reporter->id, 'post_id', $post->id]);

        $reporter->notify(new ReportNotification($post));

        return response()->json([
            'status' => 204
        ], 204);
    }

    public function reportComment(ReportRequest $request, Post $post, Comment $comment){
        Gate::authorize('view', $post);
        $reporter = Auth::user();

        Report::create($request->validated(), ['reporter_id', $reporter->id, 'comment_id', $comment->id]);

        $reporter->notify(new ReportNotification($post));

        return response()->json([
            'status' => 204
        ], 204);
    }

    public function reportUser(ReportRequest $request, User $user){
        $reporter = Auth::user();

        Report::create($request->validated(), ['reporter_id', $reporter->id, 'user_id', $user->id]);

        $reporter->notify(new ReportUserProfileNotification($user));

        return response()->json([
            'status' => 204
        ], 204);
    }
}
