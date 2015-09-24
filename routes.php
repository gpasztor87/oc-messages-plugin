<?php

Route::group(['prefix' => 'api/messages'], function() {

    Route::get('count', 'Autumn\Messages\Api\MessagesController@count');
    Route::get('list',  'Autumn\Messages\Api\MessagesController@recent');

});