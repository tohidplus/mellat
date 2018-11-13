<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMellatLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mellat_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref_id');
            $table->integer('amount');
            $table->string('order_id');
            $table->string('payer_id');
            $table->string('sale_order_id')->nullable();
            $table->string('sale_reference_id')->nullable();
            $table->string('message')->nullable();
            $table->string('res_code')->nullable();
            $table->enum('status',['successful','unsuccessful','pending'])->default('pending');
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
        Schema::dropIfExists('mellat_logs');
    }
}
