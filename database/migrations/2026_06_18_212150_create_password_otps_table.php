<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_otps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('otp', 255);

            $table->timestamp('expires_at');

            $table->boolean('used')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'otp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_otps');
    }
};
