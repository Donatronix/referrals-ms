<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table_name = 'transactions';

        Schema::create($table_name, function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('user_plan')
                ->comment('User\'s current tariff plan at the time of accrual');

            $table->integer('reward')
                ->unsigned()
                ->default(0)
                ->comment('Rewarding the user');

            $table->string('currency', 5)
                ->default('$')
                ->comment('What currency will the accrual be in');

            $table->string('operation_name')
                ->comment('Operation name');

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE {$table_name} COMMENT 'Stores the history of the user\'s transactions for a specific operation'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
