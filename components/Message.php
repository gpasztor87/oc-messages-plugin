<?php namespace Autumn\Messages\Components;

use Auth;
use Redirect;
use Validator;
use Carbon\Carbon;
use Cms\Classes\Page;
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
            'name'        => 'Messages',
            'description' => 'Display a list of message entries on the page.'
        ];
    }

    /**
     * Executed when this component is bound to a page or layout, part of
     * the page life cycle.
     */
    public function onRun()
    {

    }

}