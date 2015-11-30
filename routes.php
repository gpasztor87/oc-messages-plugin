<?php

Route::group(['prefix' => 'api/messages'], function() {

    Route::get('count', ['as' => 'messages_count', 'uses' => 'Autumn\Messages\Api\MessagesController@count']);
    Route::get('list',  ['as' => 'messages_list',  'uses' => 'Autumn\Messages\Api\MessagesController@recent']);

});