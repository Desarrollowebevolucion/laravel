<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableEmpresa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->id();
            $table->text('razonsocial')->nullable();
            $table->text('nombre')->nullable();
            $table->text('email')->nullable();
            $table->text('telefonoContacto')->nullable();
       
            $table->text('rfc')->nullable();
            $table->integer('activo')->nullable();
            $table->text('pais')->nullable();
            $table->text('direccion')->nullable();
            $table->text('calle')->nullable();
            $table->text('cp')->nullable();
            $table->text('colonia')->nullable();
            $table->text('municipio')->nullable();
            $table->text('estado')->nullable();
            $table->text('numero_int')->nullable();
            $table->text('numero_ext')->nullable();
            $table->text('referencias')->nullable();
            $table->text('regimen')->nullable();




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
        Schema::dropIfExists('empresa');
    }
}
