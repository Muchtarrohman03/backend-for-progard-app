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
        Schema::table('job_submissions', function (Blueprint $table) {
            //
            $table->renameColumn('image_path', 'before');
            $table->string('after')->nullable()->after('before');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_submissions', function (Blueprint $table) {
            //
            $table->renameColumn('before', 'image_path');
            $table->dropColumn('after');
        });
    }
};
