<?php

namespace EmailChangeVerification\Database;

use Illuminate\Database\Schema\Blueprint;

class MigrationHelper
{
    public static function defaultColumns(Blueprint $table)
    {
        $table->string('email')->index();
        $table->string('new_email');
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    }
}
