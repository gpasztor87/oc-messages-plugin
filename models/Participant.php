<?php namespace Autumn\Messages\Models;

use Model;

/**
 * Participant model
 */
class Participant extends Model
{
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'autumn_messages_participants';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['last_read'];

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