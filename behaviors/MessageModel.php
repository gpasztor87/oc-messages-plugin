<?php namespace Autumn\Messages\Behaviors;

use System\Classes\ModelBehavior;
use Autumn\Messages\Models\Thread;
use Autumn\Messages\Models\Participant;

class MessageModel extends ModelBehavior
{
    /**
     * Constructor
     */
    public function __construct($model)
    {
        parent::__construct($model);

        $model->hasMany['messages'] = ['Autumn\Messages\Models\Message'];
        $model->belongsToMany['threads'] = [
            'Autumn\Messages\Models\Thread',
            'table' => 'autumn_messages_participants',
            'order' => 'updated_at desc'
        ];
    }

    /**
     * Returns the new messages count for user.
     *
     * @return int
     */
    public function newMessagesCount()
    {
        return count($this->threadsWithNewMessages());
    }

    /**
     * Returns all threads with new messages.
     *
     * @return array
     */
    public function threadsWithNewMessages()
    {
        $threadsWithNewMessages = [];

        $participants = Participant::where('user_id', $this->model->id)->lists('last_read', 'thread_id');

        if ($participants) {
            $threads = Thread::whereIn('id', array_keys($participants))->get();
            foreach ($threads as $thread) {
                if ($thread->updated_at > $participants[$thread->id]) {
                    $threadsWithNewMessages[] = $thread->id;
                }
            }
        }

        return $threadsWithNewMessages;
    }
}