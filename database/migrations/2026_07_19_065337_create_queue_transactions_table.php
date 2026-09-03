<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_transactions', function (Blueprint $table) {
            $table->id('transaction_id');

            $table->foreignId('request_id')->constrained('queue_requests', 'request_id')->onDelete('cascade');
            $table->foreignId('served_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();

            $table->timestamp('called_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('wait_minutes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_transactions');
    }
};