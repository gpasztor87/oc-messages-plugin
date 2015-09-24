<?php namespace Autumn\Messages\Api;

use Db;
use Auth;
use View;
use Response;
use Cms\Classes\Page;
use Illuminate\Routing\Controller;

class MessagesController extends Controller
{

    public function count()
    {
        if ($user = Auth::getUser()) {

            $query = Db::select(Db::raw("SELECT COUNT(conversations_users.conversation_id) as newMessages
            FROM conversations_users
            LEFT JOIN conversations ON conversations.id = conversations_users.conversation_id
            WHERE conversations_users.user_id = :user_id AND conversations.updated_at > conversations_users.last_viewed"), ['user_id' => $user->id]);

            return Response::json($query[0]);
        }
    }

    public function recent()
    {
        if ($user = Auth::getUser()) {
            $conversations = $user->conversations()->take(5)->get();

            $messagePage = Page::withComponent('userConversations')->first();
            $messagePage = Page::url($messagePage->baseFileName);

            return View::make('autumn.messages::notifications', [
                'conversations' => $conversations,
                'messagePage'   => $messagePage
            ]);
        }
    }

}