<?php namespace Autumn\Messages\Models;

use Model;

/**
 * UserMessage model
 */
class UserMessage extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'user_messages';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['last_viewed'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'message' => ['Autumn\Messages\Models\Message']
    ];

    /**
     * Leaves a conversation
     *
     * If this is a two person conversation, the conversation will be deleted.
     * If there are more than two persons, we just leave.
     */
    public function leave()
    {
        $message = Message::find($this->message_id);

        if ($message->users->count() < 3) {
            $message->delete();
        }
        else {
            $this->delete();
        }
    }

}