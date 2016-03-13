<?php namespace Autumn\Messages\Components;

use Auth;
use Redirect;
use Validator;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Autumn\Messages\Models\Message;
use Autumn\Messages\Models\Thread;
use Autumn\Messages\Models\Participant;
use ApplicationException;
use ValidationException;

/**
 * Messages component
 */
class Messages extends ComponentBase
{
    /**
     * The thread model used for display.
     *
     * @var \Autumn\Messages\Models\Thread
     */
    public $thread;

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
                'description' => 'The URL route parameter used for looking up the threads by its slug.',
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
        if ($thread = $this->getThread()) {
            $this->thread = $this->page['thread'] = $thread;
        }
    }

    protected function getThread()
    {
        $thread = Thread::whereSlug($this->property('slug'))->first();

        if ($thread != null) {
            $participant = Participant::where('user_id', $this->user()->id)
                ->where('thread_id', $thread->id)->first();

            if ($participant != null) {
                $participant->last_read = Carbon::now();
                $participant->save();

                return $thread;
            }
        }
        else {
            $thread = $this->user()->threads->first();
        }

        return $thread;
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

        $thread = $this->getThread();

        // Attach Message
        $message = new Message;
        $message->user = $user;
        $message->thread = $thread;
        $message->body = input('body');
        $message->save();

        $thread->updated_at = Carbon::now();
        $thread->save();

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

}