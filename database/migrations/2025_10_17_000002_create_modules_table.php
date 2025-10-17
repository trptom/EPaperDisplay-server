<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->unsignedBigInteger('display_id');
            $table->integer('position');
            $table->smallInteger('type');
            $table->smallInteger('x');
            $table->smallInteger('y');
            $table->smallInteger('width');
            $table->smallInteger('height');
            $table->text('data')->nullable()->default(null);
            $table->timestamp('created_at')->nullable();

            $table->primary(['display_id', 'position']);

            $table->index('display_id');

            $table->foreign('display_id')
                ->references('id')
                ->on('displays')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropForeign(['display_id']);
        });
        Schema::dropIfExists('modules');
    }
};
