<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);

            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->restrictOnDelete();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);

            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->cascadeOnDelete();

            $table->dropSoftDeletes();
        });
    }
};
