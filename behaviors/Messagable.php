<?php namespace Autumn\Messages\Behaviors;

use System\Classes\ModelBehavior;
use Autumn\Messages\Models\Conversation;
use Autumn\Messages\Models\Participant;

class Messagable extends ModelBehavior
{
    /**
     * Constructor
     */
    public function __construct($model)
    {
        parent::__construct($model);

        $model->hasMany['messages'] = ['Autumn\Messages\Models\Message'];
        $model->belongsToMany['conversations'] = [
            'Autumn\Messages\Models\Conversation',
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
        return count($this->conversationsWithNewMessages());
    }

    /**
     * Returns all conversations with new messages.
     *
     * @return array
     */
    public function conversationsWithNewMessages()
    {
        $conversationsWithNewMessages = [];

        $participants = Participant::where('user_id', $this->model->id)->lists('last_read', 'conversation_id');

        if ($participants) {
            $conversations = Conversation::whereIn('id', array_keys($participants))->get();
            foreach ($conversations as $conversation) {
                if ($conversation->updated_at > $participants[$conversation->id]) {
                    $conversationsWithNewMessages[] = $conversation->id;
                }
            }
        }

        return $conversationsWithNewMessages;
    }
}