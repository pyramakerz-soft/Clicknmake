<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('flag')->default(false); // Adds a boolean 'flag' column with a default value of false
        });
    }

    public function down()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('flag'); // Drop the flag column if rollback is called
        });
    }
};
