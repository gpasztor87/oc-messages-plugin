<?php namespace Autumn\Messages\Components;

use Auth;
use Redirect;
use Validator;
use Carbon\Carbon;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User as UserModel;
use Autumn\Messages\Models\Message;
use Autumn\Messages\Models\Participant;
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
            'body'       => 'required'
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        // Create new Conversation
        $conversation = new Conversation;
        $conversation->subject = input('subject');
        $conversation->save();

        // Attach Message
        $message = new Message;
        $message->conversation_id = $conversation->id;
        $message->user_id = $user->id;
        $message->body = input('body');
        $message->save();

        // Attach also Recipients
        foreach(input('recipients') as $recipient) {
            Participant::create([
                'user_id' => $recipient,
                'conversation_id' => $conversation->id
            ]);
        }

        // Attach User Message
        $participant = new Participant;
        $participant->conversation_id = $conversation->id;
        $participant->user_id = $user->id;
        $participant->is_originator = 1;
        $participant->last_read = Carbon::now();
        $participant->save();

        return Redirect::back();
    }

    public function onNewMessagesCount()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        $newMessages = Participant::leftJoin('autumn_messages_conversations', function($join) {
            $join->on('autumn_messages_participants.conversation_id', '=', 'autumn_messages_conversations.id');
        })
            ->where('user_id', $user->id)
            ->whereRaw('last_read < autumn_messages_conversations.updated_at')
            ->count();

        return ['count' => $newMessages];
    }

    public function onRecent()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        $userMessages = Participant::where('user_id', $user->id)
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