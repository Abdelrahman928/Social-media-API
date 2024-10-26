<?php

namespace App\Http\Controllers\api\v1\auth\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\ReportActionRequest;
use App\Http\Resources\api\v1\ReportResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\ReportType;
use App\Models\User;
use App\Models\Warning;
use App\Notifications\WarningNotification;

class ReportController extends Controller
{
    public function index(ReportType $reportType){
        $reports = $reportType->report()->with('reporter', 'post', 'comment')->paginate(10);

        return response()->json([
            'status' => 200,
            'reports' => ReportResource::collection($reports)
        ], 200);
    }

    public function makeDecision(Report $report, ReportActionRequest $request){
        $action = $request->validated();
        $reportedEntity = $report->reportable;

        if ($action === 'ignore') {
            $report->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Report removed successfully.'
            ], 200);
        }

        if ($reportedEntity instanceof Post) {
            $warning = Warning::create(['user_id' => $reportedEntity->user->id,
                        'related_content' => $reportedEntity->content, 
                        'content_type' => 'post',
                        'media_path' => $reportedEntity->media->media_path,
                        'info' => 'your post has been deleted for violating our community standards and youraccount recieved awarning.'
            ]);

            $reportedEntity->delete(); 
            $this->incrementWarningCount($reportedEntity->user);

            $reportedEntity->user->notify(new WarningNotification($warning));
        }elseif ($reportedEntity instanceof Comment) {
            $warning = Warning::create(['user_id' => $reportedEntity->user->id,
                        'related_content' => $reportedEntity->body,
                        'media_path' => $reportedEntity->media->media_path,
                        'content_type' => 'comment',
                        'info' => 'your comment has been deleted for violating our community standards and youraccount recieved awarning.'
            ]);
            $reportedEntity->delete(); 
            $this->incrementWarningCount($reportedEntity->user);

            $reportedEntity->user->notify(new WarningNotification($warning));
        }elseif ($reportedEntity instanceof User) {
            $warning = Warning::create(['user_id' => $reportedEntity->user->id,
                        'media_path' => $reportedEntity->media->media_path,
            ]);
            // further action depending on the reason of the report and the process of validating the report call
        }

        $report->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Report handled successfully and the entity was deleted.'
        ], 200);
    }
}
