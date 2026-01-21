<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('invoice');
            $table->timestamp('invoice_expire_date')->nullable();
            $table->decimal('power', 15, 2);
            $table->decimal('amount', 30, 15);
            $table->decimal('paid_amount', 30, 15)->nullable();
            $table->string('tx_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'paid', 'canceled'])->default('pending');
            $table->text('cancel_reason')->nullable();
            $table->text('response')->nullable();
            $table->text('qrcode')->nullable();
            $table->text('comments')->nullable();
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
        Schema::dropIfExists('deposits');
    }
}
