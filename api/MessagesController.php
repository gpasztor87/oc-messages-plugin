<?php namespace Autumn\Messages\Api;

use Db;
use Auth;
use View;
use Response;
use Cms\Classes\Page;
use Illuminate\Routing\Controller;
use Autumn\Messages\Models\ConversationUser;

class MessagesController extends Controller
{

    public function count()
    {
        if ($user = Auth::getUser()) {

            $query = Db::select(Db::raw("
            SELECT COUNT(autumn_conversations_users.conversation_id) as newMessages
            FROM autumn_conversations_users
            LEFT JOIN autumn_conversations ON autumn_conversations.id = autumn_conversations_users.conversation_id
            WHERE autumn_conversations_users.user_id = :user_id AND autumn_conversations.updated_at > autumn_conversations_users.last_viewed"), ['user_id' => $user->id]);

            return Response::json($query[0]);
        }
    }

    public function recent()
    {
        if ($user = Auth::getUser()) {
            $userMessages = ConversationUser::where('user_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();

            $messagePage = Page::withComponent('userConversations')->first();
            $messagePage = Page::url($messagePage->baseFileName);

            return View::make('autumn.messages::notifications', [
                'userMessages' => $userMessages,
                'messagePage'  => $messagePage
            ]);
        }
    }

}