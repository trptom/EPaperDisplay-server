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
        Schema::create('displays', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('name', 64);
            $table->string('token', 36)->unique();
            $table->smallInteger('model')->default(0);
            $table->smallInteger('width')->default(480);
            $table->smallInteger('height')->default(800);
            $table->string('language', 5)->nullable()->default(null); // 639-1 language code and ISO 3166-1 country code, e.g. en-US
            $table->string('timezone', 5)->nullable()->default(null); // TimeZone name, e.g. Europe/Prague
            $table->decimal('latitude', 8, 5)->nullable()->default(null);
            $table->decimal('longitude', 8, 5)->nullable()->default(null);
            $table->boolean('ip_filter')->default(false);
            $table->integer('displayed')->default(0);
            $table->timestamps();

            $table->index('user_id');

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
        Schema::table('displays', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('displays');
    }
};
