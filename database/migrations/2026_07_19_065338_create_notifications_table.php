<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notif_id'); 
            $table->foreignId('transaction_id')->nullable()->constrained('queue_transactions', 'transaction_id')->onDelete('cascade'); 
            $table->enum('type', ['audio', 'display']); 
            $table->text('message');
            $table->timestamp('sent_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};