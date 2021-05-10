<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandingPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id")->default(0);
            $table->unsignedBigInteger("template_id")->default(0);
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
        Schema::dropIfExists('landing_pages');
    }
}
