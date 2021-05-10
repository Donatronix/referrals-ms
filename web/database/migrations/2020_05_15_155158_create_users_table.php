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
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->unsignedBigInteger('id')->primary()->unique();
            $table->enum('tier', ['basic', 'bronze', 'silver', 'gold'])->default('basic');
            $table->enum('regstate', ['new','registered','kyc'])->default('new');
            $table->boolean('isbustedtime')->default(false);
            $table->boolean('isbustedmoney')->default(false);
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
