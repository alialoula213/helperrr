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
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('uuid')->unique();
            $table->string('wallet')->unique();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->decimal('balance', 30, 15)->default('0.000000000000000');
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
            $table->text('banned_message')->nullable();
            $table->boolean('allow_withdrawal')->default(false);
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->foreign('ref_id')->references('id')->on('users')->nullOnDelete();
            $table->integer('ref_hits')->default(0);
            $table->ipAddress('ip');
            $table->text('comments')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
