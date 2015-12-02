<?php namespace Autumn\Messages\Models;

use Model;

/**
 * Message model
 */
class Message extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'autumn_messages';

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['content', 'user'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'conversation' => ['Autumn\Messages\Models\Conversation'],
        'user'         => ['RainLab\User\Models\User']
    ];

}