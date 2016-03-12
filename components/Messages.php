<?php namespace Autumn\Messages\Components;

use Auth;
use Redirect;
use Validator;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Autumn\Messages\Models\Message;
use Autumn\Messages\Models\Conversation;
use Autumn\Messages\Models\Participant;
use ApplicationException;
use ValidationException;

/**
 * Messages component
 */
class Messages extends ComponentBase
{
    /**
     * The conversation model used for display.
     *
     * @var \Autumn\Messages\Models\Conversation
     */
    public $conversation;

    /**
     * Returns information about this component, including name and description.
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Messages',
            'description' => 'Display a list of messages on the page.'
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
                'description' => 'The URL route parameter used for looking up the conversations by its slug.',
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
        return $this->prepareMessageList();
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

    protected function prepareMessageList()
    {
        if ($conversation = $this->getConversation()) {
            $this->conversation = $this->page['conversation'] = $conversation;
        }
    }

    protected function getConversation()
    {
        $slug = $this->property('slug');
        $conversation = Conversation::whereSlug($slug)->first();

        if ($conversation != null) {
            $participant = Participant::where('user_id', $this->user()->id)
                ->where('conversation_id', $conversation->id)->first();

            if ($participant != null) {
                $participant->last_read = Carbon::now();
                $participant->save();

                return $conversation;
            }
        }
        else {
            $conversation = $this->user()->conversations->first();
        }

        return $conversation;
    }

    public function onReplyMessage()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        $rules = [
            'body' => 'required'
        ];

        $validation = Validator::make(input(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $conversation = $this->getConversation();

        // Attach Message
        $message = new Message;
        $message->user = $user;
        $message->conversation = $conversation;
        $message->body = input('body');
        $message->save();

        $conversation->updated_at = Carbon::now();
        $conversation->save();

        $this->prepareMessageList();
    }

    public function onDeleteMessage()
    {
        $message = Message::find(input('message_id'));
        if ($message->user_id == $this->user()->id) {
            $message->delete();

            $this->prepareMessageList();
        }
    }

    public function onLeaveConversation()
    {
        $conversation = $this->getConversation(input('conversation_id'));
        if ($conversation == null) {
            throw new ApplicationException('Could not find conversation!');
        }

        if ($conversation->users->count() < 3) {
            throw new ApplicationException('Could not leave conversation, needs at least 2 persons!');
        }

        if ($conversation->creator->id == Auth::getUser()->id) {
            throw new ApplicationException('Originator could not leave his conversation!');
        }

        $participant = Participant::where('user_id', $this->user()->id)
            ->where('conversation_id', $conversation->id)->first();

        $participant->leave();

        return Redirect::to($this->pageUrl($this->page->baseFileName, [
            $this->propertyName('slug') => null
        ]));
    }

}