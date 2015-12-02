<?php namespace Autumn\Messages\Models;

use Model;

/**
 * ConversationUser model
 */
class ConversationUser extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'autumn_conversations_users';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['last_viewed'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'conversation' => ['Autumn\Messages\Models\Conversation']
    ];

    /**
     * Leaves a conversation
     *
     * If this is a two person conversation, the conversation will be deleted.
     * If there are more than two persons, we just leave.
     */
    public function leave()
    {
        $conversation = Conversation::find($this->conversation_id);

        if ($conversation->users->count() < 3) {
            $conversation->delete();
        }
        else {
            $this->delete();
        }
    }

}