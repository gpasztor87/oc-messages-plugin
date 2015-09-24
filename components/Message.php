<?php namespace Autumn\Messages\Components;

use Auth;
use Redirect;
use Validator;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Autumn\Messages\Models\Message as MessageModel;
use Autumn\Messages\Models\MessageEntry;
use Autumn\Messages\Models\UserMessage;
use ApplicationException;
use ValidationException;

/**
 * Messages component
 */
class Message extends ComponentBase
{
    /**
     * The message model used for display.
     *
     * @var \Autumn\Messages\Models\Message
     */
    public $message;

    /**
     * Returns information about this component, including name and description.
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Message',
            'description' => 'Display a list of message entries on the page.'
        ];
    }

    /**
     * Defines the properties used by this class.
     */
    public function defineProperties()
    {
        return [
            'slug'         => [
                'title'       => 'Slug param name',
                'description' => 'The URL route parameter used for looking up the message by its slug.',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ]
        ];
    }

    /**
     * Executed when this component is bound to a page or layout, part of
     * the page life cycle.
     */
    public function onRun()
    {
        return $this->prepareEntryList();
    }

    /**
     * Returns the logged in user, if available
     */
    public function user()
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::getUser();
    }

    protected function prepareEntryList()
    {
        if ($message = $this->getMessage()) {
            $this->message = $this->page['message'] = $message;
        }
    }

    protected function getMessage()
    {
        $slug = $this->property('slug');

        $message = MessageModel::whereSlug($slug)->first();
        if ($message != null) {
            $userMessage = UserMessage::where('user_id', $this->user()->id)
                ->where('message_id', $message->id)->first();

            if ($userMessage != null) {
                $userMessage->last_viewed = Carbon::now();
                $userMessage->save();

                return $message;
            }
        }
        else {
            $message = $this->user()->messages->first();
        }

        return $message;
    }

    public function onReplyMessage()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        $rules = [
            'content' => 'required'
        ];

        $validation = Validator::make(input(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $message = $this->getMessage();

        // Attach Message Entry
        $messageEntry = new MessageEntry;
        $messageEntry->user = $user;
        $messageEntry->message = $message;
        $messageEntry->content = input('content');
        $messageEntry->save();

        $message->updated_at = Carbon::now();
        $message->save();

        $this->prepareEntryList();
    }

}