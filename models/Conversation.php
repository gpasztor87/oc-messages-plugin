<?php namespace Autumn\Messages\Models;

use Uuid;
use Model;

/**
 * Conversation model
 */
class Conversation extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'conversations';

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
        'users' => ['RainLab\User\Models\User', 'table' => 'conversations_users']
    ];

    public $hasMany = [
        'messages' => ['Autumn\Messages\Models\Message']
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
    public function getLastMessage()
    {
        return Message::where('conversation_id', $this->id)->orderBy('created_at', 'desc')->first();
    }

    public function afterDelete()
    {
        $this->messages()->delete();
        $this->users()->detach();
    }

}