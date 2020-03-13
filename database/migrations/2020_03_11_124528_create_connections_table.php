<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('report_id');
            $table->foreign('report_id')
                ->references('id')
                ->on('reports')
                ->onDelete('cascade');
            $table->string('url', 1024)->index();
            $table->string('domain')->index();
            $table->string('proxy', 50)->default('')->index();
            $table->string('user_agent', 200)->default('')->index();
            $table->integer('http_code', false, true)
                ->default(0)
                ->index();
            $table->longText('response')->nullable();
            $table->tinyInteger('is_skipped', false, true)
                ->default(0)
                ->index();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('connections');
    }
}
