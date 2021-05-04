<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTariffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table_name = "tariffs";

        Schema::create($table_name, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->bigIncrements('id');
            $table->string('tariff_name', 50)->comment('Tariff package name');
            $table->float('reward', 8,2)->unsigned()->comment('Referral program participant reward');
            $table->float('threshold', 8,2)->unsigned()->comment('Referral thresholds for withdrawal or transfers');
            $table->float('buy', 8,2)->nullable()->unsigned()->comment('Purchase of a tariff');
            $table->smallInteger('fee')->nullable()->unsigned()->comment('fee for withdrawal or transfers');
            $table->tinyInteger('payment_get')->unsigned()->comment('How much a participant needs to receive using the give-get system');
            $table->tinyInteger('payment_give')->unsigned()->comment('How much a participant needs to give using the give-get system');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE {$table_name} COMMENT = 'Table with tariff packages for members of the referral program.'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tariff');
    }
}
