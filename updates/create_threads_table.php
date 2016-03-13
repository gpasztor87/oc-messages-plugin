<?php namespace Autumn\Messages\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autumn_messages_threads', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('subject');
            $table->string('slug')->index();
            $table->softDeletes();
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
        Schema::dropIfExists('autumn_messages_threads');
    }

}