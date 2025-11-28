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
        Schema::table('observation_headers', function (Blueprint $table) {
            // Step 1: Add nullable column first
            $table->foreignId('observation_template_id')
                ->nullable()
                ->after('id')
                ->constrained('observation_templates')
                ->onDelete('cascade');
        });

        // Step 2: Assign existing headers to a default template
        // (We must run DB::table after adding the column)
        DB::table('observation_templates')->insert([
            'name' => 'Default Template',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $templateId = DB::table('observation_templates')->where('name', 'Default Template')->value('id');

        DB::table('observation_headers')->update([
            'observation_template_id' => $templateId
        ]);
    }


    public function down()
    {
        Schema::table('observation_headers', function (Blueprint $table) {
            $table->dropForeign(['observation_template_id']);
            $table->dropColumn('observation_template_id');
        });
    }
};
