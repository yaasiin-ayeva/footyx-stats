<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class NotificationController extends BaseController
{
    public function index(Request $request)
    {
        $data['notifications'] = Notification::where('user_id', Auth::user()->id)
        ->orderBy('id', 'DESC')->paginate(20);

        return view('notifications.index', $data);
    }

    public function show(Request $request, $id)
    {
        // Validate the notification

        if(!($not = Notification::find($id))) {
            abort(404);
        }

        // Validate the user

        if(Auth::user()->id != $not->user_id) {
            abort(405);
        }

        // Update the seen_at

        if($not->seen_at === null) {
            $not->update(['seen_at' => DB::raw('CURRENT_TIMESTAMP()')]);
        }

        return redirect($not->link);
    }

    public static function broadcast($title, $content, $link, $users)
    {
        $receivers = [];

        foreach ($users as $user) {
            $receivers[] = [
                'user_id' => $user->id,
                'title' => $title,
                'content' => $content,
                'link' => $link,
            ];
        }

        Notification::insert($receivers);
    }

    public static function sms($to, $message)
    {
        $env = config('app.env');

        if($env == 'production') {
            $sid = config('twilio.account_sid');
            $token = config('twilio.auth_token');

            $twilio = new Client($sid, $token);

            $message = $twilio->messages->create($to,
                [
                    'body' => $message,
                    'from' => config('twilio.from_number')
                ]
            );
        }
        else if($env == 'local') {
            Log::debug([
                'sms_recipient' => $to,
                'sms_content' => $message
            ]);
        }
    }
}
