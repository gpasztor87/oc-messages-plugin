<?php namespace Autumn\Messages\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autumn_conversations', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('subject');
            $table->string('slug')->index();
            $table->integer('originator_id')->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('autumn_conversations_users', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('conversation_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->boolean('is_originator')->default(false);
            $table->timestamp('last_viewed');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['conversation_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autumn_conversations');
        Schema::dropIfExists('autumn_conversations_users');
    }

}