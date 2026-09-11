<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_transactions', function (Blueprint $table) {
            $table->unique('request_id', 'queue_transactions_request_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('queue_transactions', function (Blueprint $table) {
            $table->dropUnique('queue_transactions_request_id_unique');
        });
    }
};