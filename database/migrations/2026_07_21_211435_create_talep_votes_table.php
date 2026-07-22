<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talep_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('talep_id')->constrained('talepler')->cascadeOnDelete();
            $table->string('direction');
            $table->timestamps();

            $table->unique(['user_id', 'talep_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talep_votes');
    }
};
