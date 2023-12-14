<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('merchant_id');
            // TODO: Replace me with a brief explanation of why floats aren't the correct data type, and replace with the correct data type.
            // $table->float('commission_rate');
            //Floats aren't the ideal data type for precise decimal values due to their limited precision, which can result in rounding errors in calculations.

            $table->decimal('commission_rate', 10, 2); // 10 total digits with 2 after the decimal point
            $table->string('discount_code');
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
        Schema::dropIfExists('affiliates');
    }
};
