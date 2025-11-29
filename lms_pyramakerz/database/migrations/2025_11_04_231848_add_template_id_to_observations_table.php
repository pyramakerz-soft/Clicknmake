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
        Schema::table('observations', function (Blueprint $table) {
            $table->foreignId('observation_template_id')
                ->after('id')
                ->nullable()
                ->constrained('observation_templates')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropForeign(['observation_template_id']);
            $table->dropColumn('observation_template_id');
        });
    }
};
