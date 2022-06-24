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
        Schema::table('videos', function (Blueprint $table) {
            $table->string('hashed_id')->nullable();
        });
        Schema::table('conversions', function (Blueprint $table) {
            $table->string('hashed_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('hashed_id');
        });
        Schema::table('conversions', function (Blueprint $table) {
            $table->dropColumn('hashed_id');
        });
    }
};
