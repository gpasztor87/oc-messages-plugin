<?php namespace Autumn\Messages\Models;

use Uuid;
use Model;

/**
 * Conversation model
 */
class Conversation extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'autumn_conversations';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'subject' => 'required'
    ];

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'users' => ['RainLab\User\Models\User',
            'table' => 'autumn_conversations_users'
        ]
    ];

    public $hasMany = [
        'messages' => [
            'Autumn\Messages\Models\Message',
            'delete' => true
        ]
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
     *
     * @return \Autumn\Messages\Models\Message
     */
    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Returns the user object that created the conversation.
     *
     * @return mixed
     */
    public function getCreatorAttribute()
    {
        return $this->messages()->oldest()->first()->user;
    }

    public function afterDelete()
    {
        foreach ($this->users as $user) {
            $user->detach();
        }
    }

}