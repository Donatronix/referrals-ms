<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();

        Schema::create('referral_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');

            $table->string('package_name', 30)->nullable();
            $table->string('referral_link', 35)->unique();
            $table->string('code', 8);

            $table->unique(['user_id', 'package_name', 'code']);

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
        Schema::dropIfExists('referral_codes');
    }
}
