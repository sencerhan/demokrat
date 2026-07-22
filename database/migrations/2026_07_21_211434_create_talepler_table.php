<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talepler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->string('status')->default('pending');
            $table->unsignedInteger('upvotes_count')->default(0);
            $table->unsignedInteger('downvotes_count')->default(0);
            $table->unsignedInteger('total_votes_count')->default(0);
            $table->integer('score')->default(0);
            $table->unsignedInteger('random_key');
            $table->timestamps();

            $table->index('status');
            $table->index(['score', 'id']);
            $table->index(['total_votes_count', 'id']);
            $table->index(['random_key', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talepler');
    }
};
