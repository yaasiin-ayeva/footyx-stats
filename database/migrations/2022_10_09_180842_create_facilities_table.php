<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('net_affordability');
            $table->string('amount');
            $table->string('type');
            $table->string('duration');
            $table->string('bank_name');
            $table->string('employee_number');
            $table->string('full_name');
            $table->string('org');
            $table->string('application_no');
            $table->string('mandate_no');



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
        Schema::dropIfExists('facilities');
    }
}
