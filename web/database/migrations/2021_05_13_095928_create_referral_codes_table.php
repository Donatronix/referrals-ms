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
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('code', 8)->unique();
            $table->string('link', 35)->unique();

            $table->string('application_id', 50)
                ->nullable()
                ->comment('Application ID / Package Name');

            $table->boolean('is_default')
                ->default(false)
                ->comment('Default link');

            $table->string('note', 250)
                ->nullable()
                ->comment('The user can mark what he created the link for');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'code']);
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
