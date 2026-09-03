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
        Schema::table('queue_requests', function (Blueprint $table) {
            $table->enum('category', ['pwd', 'senior', 'regular'])->default('regular')->after('status');
        });

        Schema::table('queue_requests', function (Blueprint $table) {
            $table->index(['category', 'status', 'requested_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_requests', function (Blueprint $table) {
            $table->dropIndex(['category', 'status', 'requested_at']);
            $table->dropColumn('category');
        });
    }
};
