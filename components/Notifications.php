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
        $this->addJs('assets/js/notification.js');
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
            'title'      => 'required',
            'message'    => 'required'
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        // Create new Conversation
        $conversation = new Conversation;
        $conversation->title = input('title');
        $conversation->originator_id = $user->id;
        $conversation->save();

        // Attach Message
        $message = new Message;
        $message->conversation_id = $conversation->id;
        $message->user_id = $user->id;
        $message->content = input('message');
        $message->save();

        // Attach also Recipients
        $conversation->users()->sync(input('recipients'));

        // Attach User Message
        $conversationUser = new ConversationUser;
        $conversationUser->conversation_id = $conversation->id;
        $conversationUser->user_id = $user->id;
        $conversationUser->is_originator = 1;
        $conversationUser->last_viewed = Carbon::now();
        $conversationUser->save();

        return Redirect::back();
    }

}