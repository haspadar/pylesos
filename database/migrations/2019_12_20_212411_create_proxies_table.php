<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProxiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proxies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('address', 50);
            $table->enum('protocol', ['http','https','socks4','socks5'])
                ->default('https');
            $table->string('domain',100)->default('')->index();
            $table->string('adapter',100)->default('')->index();
            $table->timestamps();
            $table->unique(['address']);
            $table->index(['created_at']);
            $table->index(['updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proxies');
    }
}
