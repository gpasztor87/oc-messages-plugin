<?php namespace Autumn\Messages\Models;

use Uuid;
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

    public function beforeCreate()
    {
        $this->slug = Uuid::generate();
    }

    /**
     * Sets the "url" attribute with a URL to this object
     *
     * @param string $pageName
     * @param \Cms\Classes\Controller $controller
     * @return string
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id'   => $this->id,
            'slug' => $this->slug,
        ];

        return $this->url = $controller->pageUrl($pageName, $params);
    }

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