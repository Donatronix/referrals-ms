<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTotalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table_name = 'totals';

        Schema::create($table_name, function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->integer('amount')
                ->unsigned()
                ->default(0)
                ->comment('sum of invited users');

            $table->float('reward', 7, 2)
                ->unsigned()
                ->default(0)
                ->comment('the number of invitees by this user');

            $table->foreignUuid('user_id')
                ->constrained()
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE {$table_name} COMMENT 'Responsible for storing general data on the number of invitees and the amount of accrual for referrals'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('total');
    }
}
