<?php namespace Autumn\Messages;

use RainLab\User\Models\User;
use System\Classes\PluginBase;
use Illuminate\Foundation\AliasLoader;

/**
 * Messages Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['RainLab.User'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails() {
        return [
            'name'        => 'Messages',
            'description' => 'User communication platform.',
            'author'      => 'Autumn',
            'icon'        => 'icon-comments'
        ];
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot()
    {
        $alias = AliasLoader::getInstance();
        $alias->alias('Uuid', 'Webpatser\Uuid\Uuid');

        User::extend(function($model) {
            $model->hasMany['messages'] = ['Autumn\Messages\Models\Message'];
            $model->belongsToMany['conversations'] = [
                'Autumn\Messages\Models\Conversation',
                'table' => 'autumn_conversations_users',
                'order' => 'updated_at desc'
            ];
        });
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Autumn\Messages\Components\Notifications' => 'messageNotifications',
            'Autumn\Messages\Components\Conversations' => 'userConversations',
            'Autumn\Messages\Components\Messages'      => 'userMessages'
        ];
    }

}