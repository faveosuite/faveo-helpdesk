<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('filename');
            $table->string('db_name');
            $table->string('file_path');
            $table->string('db_path');
            $table->string('version');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('backups');
    }
};
