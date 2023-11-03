<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCreditorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('creditors', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('level');
            $table->unsignedBigInteger('financial_institute_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('financial_institute_id')->references('id')->on('financial_institutes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        //Contrainte de vérification :
        // DB::statement('ALTER TABLE creditors ADD CONSTRAINT chk_creditor CHECK ( type IN (\'admin, approver\'))');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('creditors');
    }
}
