<?php

namespace Autumn\Messages\Models;

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
    public $hasMany = [
        'messages' => [
            'Autumn\Messages\Models\Message',
            'delete' => true
        ],
        'participants' => [
            'Autumn\Messages\Models\Participant'
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
    public function creator()
    {
        return $this->messages()->oldest()->first()->user;
    }

    /**
     * Returns all threads by subject.
     *
     * @param $query
     *
     * @return mixed
     */
    public static function getBySubject($query)
    {
        return self::where('subject', 'like', $query)->get();
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
     * @param int $user_id
     */
    public function markAsRead($user_id)
    {
        try {
            $participant = $this->getParticipantFromUser($user_id);
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
     * @param int $user_id
     *
     * @return bool
     */
    public function isUnread($user_id)
    {
        try {
            $participant = $this->getParticipantFromUser($user_id);
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
     * @param int $user_id
     *
     * @return mixed
     */
    public function getParticipantFromUser($user_id)
    {
        return $this->participants()->where('user_id', $user_id)->firstOrFail();
    }

    /**
     * Restores all participants within a thread that has a new message.
     */
    public function activateAllParticipants()
    {
        $participants = $this->participants()->withTrashed()->get();
        foreach ($participants as $participant) {
            $participant->restore();
        }
    }

    /**
     * Checks to see if a user is a current participant of the thread.
     *
     * @param int $user_id
     *
     * @return bool
     */
    public function hasParticipant($user_id)
    {
        $participants = $this->participants()->where('user_id', '=', $user_id);
        if ($participants->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns array of unread messages in thread for given user.
     *
     * @param int $user_id
     *
     * @return \October\Rain\Database\Collection
     */
    public function userUnreadMessages($user_id)
    {
        $messages = $this->messages()->get();
        $participant = $this->getParticipantFromUser($user_id);

        if (!$participant) {
            return collect();
        }

        if (!$participant->last_read) {
            return collect($messages);
        }

        $unread = [];
        $i = count($messages) - 1;

        while ($i) {
            if ($messages[$i]->updated_at->gt($participant->last_read)) {
                array_push($unread, $messages[$i]);
            } else {
                break;
            }

            --$i;
        }

        return collect($unread);
    }

    /**
     * Returns count of unread messages in thread for given user.
     *
     * @param int $user_id
     *
     * @return int
     */
    public function userUnreadMessagesCount($user_id)
    {
        return $this->userUnreadMessages($user_id)->count();
    }

    /**
     * Sets the "url" attribute with a URL to this object
     *
     * @param string $pageName
     * @param \Cms\Classes\Controller $controller
     *
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
