<?php namespace Autumn\Messages\Components;

use Auth;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Autumn\Messages\Models\UserMessage;
use ApplicationException;

class Messages extends ComponentBase
{

    /**
     * A collection of messages to display.
     *
     * @var Collection
     */
    public $messages;

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
            'name'        => 'Messages',
            'description' => 'Displays a list of user conversation on the page.'
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

    public function getRedirectOptions()
    {
        return ['' => '- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Executed when this component is bound to a page or layout, part of
     * the page life cycle.
     */
    public function onRun()
    {
        $this->messages = $this->page['messages'] = $this->loadMessages();
    }

    protected function loadMessages()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        $messages = UserMessage::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')->get();

        return $messages;
    }

}