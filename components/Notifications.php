<?php namespace Autumn\Messages\Components;

use Auth;
use Redirect;
use Validator;
use Carbon\Carbon;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User as UserModel;
use Autumn\Messages\Models\Message as MessageModel;
use Autumn\Messages\Models\MessageEntry;
use Autumn\Messages\Models\UserMessage;
use ApplicationException;
use ValidationException;

/**
 * Notifications component
 */
class Notifications extends ComponentBase {

    /**
     * Reference to the page name for linking to messages.
     *
     * @var string
     */
    public $messagesPage;

    /**
     * Returns information about this component, including name and description.
     */
    public function componentDetails() {
        return [
            'name'        => 'Mail Notifications',
            'description' => 'Display a mail notifications widget.'
        ];
    }

    /**
     * Defines the properties used by this class.
     */
    public function defineProperties() {
        return [
            'messagesPage' => [
                'title'       => 'Messages page',
                'description' => 'Name of the messages page file for the messages link.',
                'type'        => 'dropdown'
            ],
        ];
    }

    public function getMessagesPageOptions() {
        return Page::withComponent('messages')->sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Executed when this component is bound to a page or layout, part of
     * the page life cycle.
     */
    public function onRun() {
        $this->messagesPage = $this->page['messagesPage'] = $this->property('messagesPage');
        $this->page['users'] = UserModel::where('id', '!=', Auth::getUser()->id)->get();
        $this->addJs('assets/js/notification.js');
    }

    /**
     * Creates a new Message
     */
    public function onAddMessage() {

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

        // Create new Message
        $message = new MessageModel;
        $message->title = input('title');
        $message->originator_id = $user->id;
        $message->save();

        // Attach Message Entry
        $messageEntry = new MessageEntry;
        $messageEntry->message_id = $message->id;
        $messageEntry->user_id = $user->id;
        $messageEntry->content = input('message');
        $messageEntry->save();

        // Attach also Recipients
        $message->users()->sync(input('recipients'));

        // Attach User Message
        $userMessage = new UserMessage;
        $userMessage->message_id = $message->id;
        $userMessage->user_id = $user->id;
        $userMessage->is_originator = 1;
        $userMessage->last_viewed = Carbon::now();
        $userMessage->save();

        return Redirect::back();
    }

}