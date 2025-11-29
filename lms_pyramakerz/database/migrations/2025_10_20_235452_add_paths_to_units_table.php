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
        Schema::table('units', function (Blueprint $table) {
            $table->string('ebook_path')->nullable()->after('image');
            $table->string('workshop_path')->nullable()->after('ebook_path');
            $table->string('video_path')->nullable()->after('workshop_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['ebook_path', 'workshop_path', 'video_path']);
        });
    }
};
