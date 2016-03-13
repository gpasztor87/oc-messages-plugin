<?php namespace Autumn\Messages\Components;

use Auth;
use Cms\Classes\ComponentBase;
use ApplicationException;

class Threads extends ComponentBase
{

    /**
     * A collection of threads to display.
     *
     * @var \October\Rain\Database\Collection
     */
    public $threads;

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
            'name'        => 'Threads',
            'description' => 'Displays a list of user threads on the page.'
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
                'description' => 'The URL route parameter used for looking up the thread by its slug.',
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
        $this->threads = $this->page['threads'] = $this->loadThreads();
    }

    protected function loadThreads()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You should be logged in.');
        }

        $threads = $user->threads;

        $threads->each(function($thread) {
            $thread->setUrl($this->page->baseFileName, $this->controller);
        });

        return $threads;
    }

}