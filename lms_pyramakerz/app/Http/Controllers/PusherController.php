<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Pusher\Pusher;

class PusherController extends Controller
{
    public function auth(Request $request)
    {
        if (Auth::check()) {
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'useTLS' => true,
                ]
            );

            $channelName = $request->input('channel_name');
            $socketId = $request->input('socket_id');

            $auth = $pusher->authorizeChannel($channelName, $socketId);

            return response($auth, 200);
        } else {
            return response('Unauthorized', 403);
        }
    }
}
