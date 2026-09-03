<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_requests', function (Blueprint $table) {
            $table->id('request_id');
            
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->foreignId('service_id')->constrained('services', 'service_id')->onDelete('cascade');
            
            $table->integer('queue_number');
            $table->string('tracking_code', 20)->unique();
            $table->string('status')->default('waiting');
            $table->timestamp('requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_requests');
    }
};