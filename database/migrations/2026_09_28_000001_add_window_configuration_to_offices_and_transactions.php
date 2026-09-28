<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->unsignedSmallInteger('window_count')->default(1);
        });

        Schema::table('queue_transactions', function (Blueprint $table) {
            $table->unsignedSmallInteger('counter_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('queue_transactions', function (Blueprint $table) {
            $table->dropColumn('counter_number');
        });

        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('window_count');
        });
    }
};