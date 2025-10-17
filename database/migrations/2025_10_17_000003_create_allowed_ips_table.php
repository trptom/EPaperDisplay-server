<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('allowed_ips', function (Blueprint $table) {
            $table->unsignedBigInteger('display_id');
            $table->string('ip', 15);

            $table->primary(['display_id', 'ip']);

            $table->index('display_id');

            $table->foreign('display_id')
                ->references('id')
                ->on('displays')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('allowed_ips', function (Blueprint $table) {
            $table->dropForeign(['display_id']);
        });
        Schema::dropIfExists('allowed_ips');
    }
};
