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
        Schema::connection('ragagent')->create('chat_history', function (Blueprint $table) {
            $table->id();
            $table->string('thread_id', 255);
            $table->longText('messages');
            $table->timestamps();
            
            // Add unique constraint on thread_id
            $table->unique('thread_id', 'uk_thread_id');
            
            // Add index on thread_id for faster lookups
            $table->index('thread_id', 'idx_thread_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('ragagent')->dropIfExists('chat_history');
    }
};
