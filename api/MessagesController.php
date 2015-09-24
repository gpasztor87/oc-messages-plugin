<?php namespace Autumn\Messages\Api;

use Db;
use Auth;
use Response;
use Illuminate\Routing\Controller;

class MessagesController extends Controller
{

    public function count()
    {
        if ($user = Auth::getUser()) {

            $query = Db::select(Db::raw("SELECT COUNT(user_messages.message_id) as newMessages
            FROM user_messages
            LEFT JOIN messages ON messages.id = user_messages.message_id
            WHERE user_messages.user_id = :user_id AND messages.updated_at > user_messages.last_viewed"), ['user_id' => $user->id]);

            return Response::json($query[0]);
        }
    }

}