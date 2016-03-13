<?php namespace Autumn\Messages\Models;

use Uuid;
use Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Thread model
 */
class Thread extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'autumn_messages_threads';

    /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $fillable = ['subject'];

    /**
     * Validation rules
     *
     * @var array
     */
    public $rules = [
        'subject' => 'required'
    ];

    /**
     * Relations
     *
     * @var array
     */
    public $belongsToMany = [
        'users' => ['RainLab\User\Models\User',
            'table' => 'autumn_messages_participants',
            'delete' => true
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
     * Returns the last message of this thread.
     *
     * @return \Autumn\Messages\Models\Message
     */
    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Returns the user object that created the thread.
     *
     * @return mixed
     */
    public function getCreatorAttribute()
    {
        return $this->messages()->oldest()->first()->user;
    }

    /**
     * Adds users to this thread.
     *
     * @param array $participants list of all participants
     */
    public function addParticipants(array $participants)
    {
        if (count($participants)) {
            foreach ($participants as $user_id) {
                Participant::firstOrCreate([
                    'user_id' => $user_id,
                    'thread_id' => $this->id,
                ]);
            }
        }
    }

    /**
     * Mark a thread as read for a user.
     *
     * @param int $userId
     */
    public function markAsRead($userId)
    {
        try {
            $participant = $this->getParticipantFromUser($userId);
            $participant->last_read = new Carbon();
            $participant->save();
        }
        catch (ModelNotFoundException $ex) {
            // do nothing
        }
    }

    /**
     * See if the current thread is unread by the user.
     *
     * @param int $userId
     * @return bool
     */
    public function isUnread($userId)
    {
        try {
            $participant = $this->getParticipantFromUser($userId);
            if ($this->updated_at > $participant->last_read) {
                return true;
            }
        }
        catch (ModelNotFoundException $ex) {
            // do nothing
        }

        return false;
    }

    /**
     * Finds the participant record from a user id.
     *
     * @param $userId
     * @return mixed
     */
    public function getParticipantFromUser($userId)
    {
        return $this->participants()->where('user_id', $userId)->firstOrFail();
    }

    /**
     * Checks to see if a user is a current participant of the thread.
     *
     * @param $userId
     * @return bool
     */
    public function hasParticipant($userId)
    {
        $participants = $this->participants()->where('user_id', '=', $userId);
        if ($participants->count() > 0) {
            return true;
        }

        return false;
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

}