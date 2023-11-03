<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->double('affordability_amount');
            $table->string('type_of_facility');
            $table->string('duration');
            $table->float('interest_rate');
            $table->string('id_image');
            $table->unsignedBigInteger('staff_id');
            // $table->unsignedBigInteger('staff_id')->unique();
            $table->unsignedBigInteger('financial_institute_id');
            $table->foreign('financial_institute_id')->references('id')->on('financial_institutes')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('staffs')->onDelete('cascade');
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
        Schema::dropIfExists('applications');
    }
}
