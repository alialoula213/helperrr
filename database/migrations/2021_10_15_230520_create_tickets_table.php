<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins');
            $table->unsignedBigInteger('priority_id');
            $table->foreign('priority_id')->references('id')->on('ticket_priorities');
            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')->references('id')->on('ticket_statuses');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('ticket_categories');
            $table->boolean('read')->default(0);
            $table->boolean('admin_read')->default(0);
            $table->string('ticket_id')->unique();
            $table->string('title');
            $table->longText('message');
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
        Schema::dropIfExists('tickets');
    }
}
