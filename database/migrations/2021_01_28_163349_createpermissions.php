<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Createpermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions_user_user', function (Blueprint $table) {
            $table->id();
            $table->integer('padre_id')->nullable();
            $table->integer('hijo_id')->nullable();
            $table->integer('permiso_id')->nullable();
            $table->integer('id_model')->nullable();
            $table->text('Model')->nullable();
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
        Schema::dropIfExists('permissions_user_user');
    }
}
