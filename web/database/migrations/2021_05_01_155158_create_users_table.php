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
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->index();

            $table->uuid('referrer_id')
                ->nullable()
                ->comment('The ID of the inviting user');

            $table->string('name')
                ->nullable()
                ->comment('The fullname of the invited user');
            $table->string('username')
                ->nullable()
                ->comment('The username of the invited user');

            $table->string('country')
                ->nullable()
                ->comment('The country of the invited user');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
