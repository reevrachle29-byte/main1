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
        Schema::table('offices', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('is_active');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index('office_id');
            $table->index('is_active');
        });

        Schema::table('queue_requests', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('service_id');
            $table->index(['status', 'requested_at']);
            $table->index(['service_id', 'status', 'requested_at']);
        });

        Schema::table('queue_sessions', function (Blueprint $table) {
            $table->index('office_id');
            $table->index(['status', 'office_id']);
        });

        Schema::table('queue_transactions', function (Blueprint $table) {
            $table->index('request_id');
            $table->index('served_by');
            $table->index(['completed_at', 'called_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['office_id']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('queue_requests', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['service_id']);
            $table->dropIndex(['status', 'requested_at']);
            $table->dropIndex(['service_id', 'status', 'requested_at']);
        });

        Schema::table('queue_sessions', function (Blueprint $table) {
            $table->dropIndex(['office_id']);
            $table->dropIndex(['status', 'office_id']);
        });

        Schema::table('queue_transactions', function (Blueprint $table) {
            $table->dropIndex(['request_id']);
            $table->dropIndex(['served_by']);
            $table->dropIndex(['completed_at', 'called_at']);
        });
    }
};
