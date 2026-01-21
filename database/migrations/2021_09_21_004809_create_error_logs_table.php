<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErrorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['deposit', 'withdrawal', 'others'])->default('others');
            $table->text('message');
            $table->unsignedBigInteger('deposit_id')->nullable();
            $table->foreign('deposit_id')->references('id')->on('deposits')->cascadeOnDelete();
            $table->unsignedBigInteger('withdrawal_id')->nullable();
            $table->foreign('withdrawal_id')->references('id')->on('withdrawals')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->text('response')->nullable();
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
        Schema::dropIfExists('error_logs');
    }
}
