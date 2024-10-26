<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\api\v1\NotificationResource;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(){
        $user = Auth::user();
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(5);

        if($notifications->isEmpty()){
            return response()->json([
                'status' => 404,
                'message' => "you don't have any notifications"
            ], 404);
        }

        foreach ($user->unreadNotifications as $notification) {
            $notification->markAsRead();
        }
        return response()->json([
            'status' => 200,
            'notifications' => NotificationResource::collection($notifications)
        ], 200);
    }

    public function destroy($id){
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json([
            'status' => 200,
            'message' => 'notification removed successfully.'
        ], 200);
    }
}
