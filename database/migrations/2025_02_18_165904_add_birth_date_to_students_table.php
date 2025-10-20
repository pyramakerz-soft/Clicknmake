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
        Schema::table('students', function (Blueprint $table) {
            $table->date('birth_date')->nullable(); // Add birth_date column as a nullable date
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('birth_date'); // Drop the birth_date column in case of rollback
        });
    }
};
