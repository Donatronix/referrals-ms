<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandingpagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landingpages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("user_id")->default(0);
            $table->bigInteger("template_id")->default(0);
            $table->longText("json");
            $table->timestamps();

            $table->foreign("user_id")->references("id")->on("users");
            $table->foreign("template_id")->references("id")->on("templates");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('landingpages');
    }
}
