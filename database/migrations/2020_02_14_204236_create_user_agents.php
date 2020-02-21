<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAgents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_agent')->unique();
            $table->tinyInteger('is_mobile')->default(0);
            $table->index('is_mobile');
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_agents');
    }
}
