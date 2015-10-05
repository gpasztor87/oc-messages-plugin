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
        Schema::create('conversations', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('subject');
            $table->string('slug')->index();
            $table->integer('originator_id')->index();
            $table->timestamps();
        });

        Schema::create('conversations_users', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('conversation_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->boolean('is_originator')->default(false);
            $table->timestamp('last_viewed');
            $table->timestamps();
            $table->unique(['conversation_id', 'user_id']);
        });

        Schema::create('conversation_messages', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('conversation_id')->unsigned()->index();
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
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('conversations_users');
        Schema::dropIfExists('conversation_messages');
    }

}