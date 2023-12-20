<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::create('backup_paths', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('backup_path');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('backup_paths');
    }
};
