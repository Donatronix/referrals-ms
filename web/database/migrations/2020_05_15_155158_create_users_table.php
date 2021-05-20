<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table_name = 'users';

        Schema::create($table_name, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('referral_id')->nullable();
            $table->enum('level', ['0', '1', '2', '3'])->default('0')->comment('basic - 0, bronze - 1, silver = 2, gold - 3');

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
        Schema::dropIfExists('users');
    }
}
