<?php namespace Autumn\Messages\Components;

use Auth;
use Cms\Classes\ComponentBase;
use ApplicationException;

class Conversations extends ComponentBase
{

    /**
     * A collection of conversations to display.
     *
     * @var Collection
     */
    public $conversations;

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
            'name'        => 'Conversations',
            'description' => 'Displays a list of user conversations on the page.'
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
                'description' => 'The URL route parameter used for looking up the conversation by its slug.',
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
        $this->conversations = $this->page['conversations'] = $this->loadConversations();
    }

    protected function loadConversations()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        $conversations = $user->conversations;
        $conversations->each(function($conversation) {
            $conversation->setUrl($this->page->baseFileName, $this->controller);
        });

        return $conversations;
    }

}