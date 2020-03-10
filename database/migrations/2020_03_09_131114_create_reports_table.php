<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id');
            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->onDelete('cascade');
            $table->string('url')->index();
            $table->integer('http_code', false, true)->default(200)->index();
            $table->longText('response')->nullable();
            $table->tinyInteger('is_cached', false, true)->default(0)->index();
            $table->tinyInteger('is_downloaded', false, true)->default(0)->index();
            $table->integer('attempts_count', false, true)->default(0)->index();
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
        Schema::dropIfExists('reports');
    }
}
