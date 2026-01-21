<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateWithdrawalFeeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("ALTER TABLE `withdrawals` CHANGE `fees` `fees` DECIMAL(30,15) NULL DEFAULT '0.000000000000000';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("ALTER TABLE `withdrawals` CHANGE `fees` `fees` DECIMAL(30,15) NOT NULL DEFAULT '0.000000000000000';");
    }
}
