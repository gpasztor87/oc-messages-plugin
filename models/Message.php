<?php namespace Autumn\Messages\Models;

use Model;

/**
 * Message model
 */
class Message extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'autumn_messages';

    /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $fillable = ['body', 'user_id', 'conversation_id'];

    /**
     * Relations
     *
     * @var array
     */
    public $belongsTo = [
        'conversation' => ['Autumn\Messages\Models\Conversation'],
        'user'         => ['RainLab\User\Models\User']
    ];

}