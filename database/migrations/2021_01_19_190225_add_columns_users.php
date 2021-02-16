<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('photo')->nullable();
            $table->date('f_nacimiento')->nullable();
            $table->text('calle')->nullable();
            $table->text('cp')->nullable();
            $table->text('colonia')->nullable();
            $table->text('municipio')->nullable();
            $table->text('estado')->nullable();
            $table->text('n_int')->nullable();
            $table->text('n_ext')->nullable();
            $table->text('referencias')->nullable();
            $table->text('telefono')->nullable();
            $table->text('nickname')->nullable();
            $table->text('update')->nullable();

       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('photo');
            $table->dropColumn('f_nacimiento');
            $table->dropColumn('calle');
            $table->dropColumn('cp');
            $table->dropColumn('colonia');
            $table->dropColumn('municipio');
            $table->dropColumn('estado');
            $table->dropColumn('n_int');
            $table->dropColumn('n_ext');
            $table->dropColumn('referencias');
            $table->dropColumn('nickname');
            $table->dropColumn('telefono');
            $table->dropColumn('firstlog');
            $table->dropColumn('update');

            
        });
    }
}
