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
        Schema::table('positions', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->change();
            $table->decimal('longitude', 10, 7)->change();

            $table->float('accuracy')->nullable();
            $table->float('speed')->nullable();
            $table->float('heading')->nullable();

            $table->index('employee_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->double('latitude', 10, 7)->change();
            $table->double('longitude', 10, 7)->change();

            $table->dropColumn(['accuracy', 'speed', 'heading']);
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
