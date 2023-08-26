<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('email_changes', function (\Illuminate\Database\Schema\Blueprint $table) {
            \EmailChangeVerification\Database\MigrationHelper::defaultColumns($table);
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_changes');
    }
};
