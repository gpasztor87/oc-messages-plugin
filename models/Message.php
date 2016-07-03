<?php

namespace Autumn\Messages\Models;

use Model;

/**
 * Message model
 */
class Message extends Model
{
    use \October\Rain\Database\Traits\Validation;

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
    protected $fillable = ['body', 'user_id', 'thread_id'];

    /**
     * Validation rules.
     *
     * @var array
     */
    protected $rules = [
        'body' => 'required',
    ];

    /**
     * Relations
     *
     * @var array
     */
    public $belongsTo = [
        'thread'       => ['Autumn\Messages\Models\Thread'],
        'participants' => ['Autumn\Messages\Models\Participant'],
        'user'         => ['RainLab\User\Models\User']
    ];
}
