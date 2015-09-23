<?php namespace Autumn\Messages\Models;

use Model;

/**
 * MessageEntry model
 */
class MessageEntry extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'message_entry';

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['content', 'user'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'message' => ['Autumn\Messages\Models\Message'],
        'user'    => ['RainLab\User\Models\User']
    ];

}