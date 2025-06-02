<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    DB::statement('ALTER TABLE users MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
}

public function down()
{
    // No hace falta revertir esto normalmente
}

};
