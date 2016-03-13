<?php namespace Autumn\Messages\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autumn_messages_participants', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('thread_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamp('last_read')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['thread_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autumn_messages_participants');
    }
}