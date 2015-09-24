<?php namespace Autumn\Messages\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title');
            $table->string('slug')->index();
            $table->integer('originator_id')->index();
            $table->timestamps();
        });

        Schema::create('user_messages', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('message_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->boolean('is_originator')->default(false);
            $table->timestamp('last_viewed');
            $table->timestamps();
            $table->unique(['message_id', 'user_id']);
        });

        Schema::create('message_entry', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('message_id')->unsigned()->index();
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('user_messages');
        Schema::dropIfExists('message_entry');
    }

}