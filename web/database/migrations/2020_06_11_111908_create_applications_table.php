<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->bigIncrements('id');

            $table->string('referrer_code', 10)->nullable()->index();
            $table->string('package_name', 20)->index();
            $table->string('device_id')->index();
            $table->string('device_name')->index();
            $table->ipAddress('ip')->index();
            $table->json('metadata')->nullable();

            $table->boolean('is_registered')->default(0);
            $table->integer('user_id')->nullable(false);
            $table->integer('installed_status')->default(0);
            $table->integer('referrer_status')->default(0);
            $table->integer('referrer_id')->nullable(false);

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
        Schema::dropIfExists('applications');
    }
}
