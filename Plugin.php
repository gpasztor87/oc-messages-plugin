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
            $model->belongsToMany['messages'] = ['Autumn\Messages\Models\Message', 'table' => 'user_messages', 'order' => 'updated_at desc'];
            $model->hasMany['conversations'] = ['Autumn\Messages\Models\UserMessage', 'order' => 'updated_at asc'];
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
            'Autumn\Messages\Components\Messages'      => 'messages',
            'Autumn\Messages\Components\Message'       => 'message'
        ];
    }

}