<?php namespace Autumn\Messages\Components;

use Auth;
use Redirect;
use Validator;
use Carbon\Carbon;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User as UserModel;
use Autumn\Messages\Models\Message;
use Autumn\Messages\Models\ConversationUser;
use Autumn\Messages\Models\Conversation;
use ApplicationException;
use ValidationException;

/**
 * Notifications component
 */
class Notifications extends ComponentBase
{

    /**
     * Reference to the page name for linking to messages.
     *
     * @var string
     */
    public $messagesPage;

    /**
     * Returns information about this component, including name and description.
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Mail Notifications',
            'description' => 'Display a mail notifications widget.'
        ];
    }

    /**
     * Defines the properties used by this class.
     */
    public function defineProperties()
    {
        return [
            'messagesPage' => [
                'title'       => 'Messages page',
                'description' => 'Name of the messages page file for the messages link.',
                'type'        => 'dropdown'
            ],
        ];
    }

    public function getMessagesPageOptions()
    {
        return Page::withComponent('messages')->sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Executed when this component is bound to a page or layout, part of
     * the page life cycle.
     */
    public function onRun()
    {
        $this->messagesPage = $this->page['messagesPage'] = $this->property('messagesPage');
        $this->page['users'] = UserModel::where('id', '!=', Auth::getUser()->id)->get();
    }

    /**
     * Creates a new Message
     */
    public function onAddMessage()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        $data = input();

        $rules = [
            'recipients' => 'required',
            'subject'    => 'required',
            'message'    => 'required'
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        // Create new Conversation
        $conversation = new Conversation;
        $conversation->subject = input('subject');
        $conversation->originator_id = $user->id;
        $conversation->save();

        // Attach Message
        $message = new Message;
        $message->conversation_id = $conversation->id;
        $message->user_id = $user->id;
        $message->body = input('body');
        $message->save();

        // Attach also Recipients
        foreach(input('recipients') as $recipient) {
            ConversationUser::create([
                'user_id' => $recipient,
                'conversation_id' => $conversation->id
            ]);
        }

        // Attach User Message
        $conversationUser = new ConversationUser;
        $conversationUser->conversation_id = $conversation->id;
        $conversationUser->user_id = $user->id;
        $conversationUser->is_originator = 1;
        $conversationUser->last_viewed = Carbon::now();
        $conversationUser->save();

        return Redirect::back();
    }

    public function onNewMessagesCount()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        $newMessages = ConversationUser::leftJoin('autumn_conversations', function($join) {
            $join->on('autumn_conversations_users.conversation_id', '=', 'autumn_conversations.id');
        })
            ->where('user_id', $user->id)
            ->whereRaw('last_viewed < autumn_conversations.updated_at')
            ->count();

        return ['count' => $newMessages];
    }

    public function onRecent()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        $userMessages = ConversationUser::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return [
            '#dropdown-messages' => $this->renderPartial('@recent', [
                'userMessages' => $userMessages,
                'messagesPage' => $this->property('messagesPage')
            ])
        ];
    }

}