<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id('office_id'); 
            
            // Explicitly point to 'user_id' as the primary key column on the 'users' table
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->onDelete('set null'); 
            
            $table->string('name');
            $table->boolean('is_active')->default(true); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};