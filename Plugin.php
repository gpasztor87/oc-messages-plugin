<?php namespace Autumn\Messages;

use System\Classes\PluginBase;

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