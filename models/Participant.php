<?php namespace Autumn\Messages\Models;

use Model;

/**
 * Participant model
 */
class Participant extends Model
{
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'autumn_messages_participants';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['last_read'];

    /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $fillable = ['thread_id', 'user_id', 'last_read'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'thread' => ['Autumn\Messages\Models\Thread'],
        'user'   => ['RainLab\User\Models\User']
    ];

}