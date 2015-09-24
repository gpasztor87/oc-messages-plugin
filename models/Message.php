<?php namespace Autumn\Messages\Models;

use Model;

/**
 * Message model
 */
class Message extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'messages';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required'
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'originator' => ['RainLab\User\Models\User']
    ];

    public $belongsToMany = [
        'users' => ['RainLab\User\Models\User', 'table' => 'user_messages']
    ];

    public $hasMany = [
        'entries' => ['Autumn\Messages\Models\MessageEntry']
    ];

    /**
     * Returns the last message of this conversation
     */
    public function getLastEntry()
    {
        return MessageEntry::where('message_id', $this->id)->orderBy('created_at', 'desc')->first();
    }

    public function afterDelete()
    {
        $this->entries()->delete();
        $this->users()->detach();
    }

}