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
        // contact and role columns are already created in the base create_users_table migration.
        // No additional columns needed.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};