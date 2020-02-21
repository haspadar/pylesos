<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitesUsersAgents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites_users_agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id');
            $table->unsignedBigInteger('user_agent_id');
            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->onDelete('cascade');;
            $table->foreign('user_agent_id')
                ->references('id')
                ->on('users_agents')
                ->onDelete('cascade');;
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
        Schema::dropIfExists('sites_users_agents');
    }
}
