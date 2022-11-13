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
        Schema::create('whatpulse_pulses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('pulse_id');
            $table->date('date_id');
            $table->datetime('pulse_date');
            $table->integer('keystrokes');
            $table->integer('mouse_clicks');
            $table->integer('download_mb');
            $table->integer('upload_mb');
            $table->boolean('sent_to_exist')->default(false);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatpulse_pulses');
    }
};
