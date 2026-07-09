<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('poll_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->timestamps();

            $table->unique(['vote_id', 'position']);
            $table->unique(['vote_id', 'poll_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vote_items');
    }
};
