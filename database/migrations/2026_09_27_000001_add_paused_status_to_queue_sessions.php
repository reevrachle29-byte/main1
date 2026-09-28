<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE TABLE IF NOT EXISTS queue_sessions_new (
                session_id INTEGER PRIMARY KEY AUTOINCREMENT,
                office_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                status TEXT NOT NULL DEFAULT "open" CHECK(status IN ("open", "paused", "closed")),
                opened_at TEXT NULL,
                closed_at TEXT NULL,
                created_at TEXT NULL,
                updated_at TEXT NULL,
                FOREIGN KEY(office_id) REFERENCES offices(office_id) ON DELETE CASCADE,
                FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE
            )');

            DB::statement('INSERT INTO queue_sessions_new (session_id, office_id, user_id, status, opened_at, closed_at, created_at, updated_at)
                SELECT session_id, office_id, user_id, status, opened_at, closed_at, created_at, updated_at FROM queue_sessions');

            DB::statement('DROP TABLE queue_sessions');
            DB::statement('ALTER TABLE queue_sessions_new RENAME TO queue_sessions');

            return;
        }

        Schema::table('queue_sessions', function (Blueprint $table) {
            $table->enum('status', ['open', 'paused', 'closed'])->default('open')->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE TABLE IF NOT EXISTS queue_sessions_old (
                session_id INTEGER PRIMARY KEY AUTOINCREMENT,
                office_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                status TEXT NOT NULL DEFAULT "open" CHECK(status IN ("open", "closed")),
                opened_at TEXT NULL,
                closed_at TEXT NULL,
                created_at TEXT NULL,
                updated_at TEXT NULL,
                FOREIGN KEY(office_id) REFERENCES offices(office_id) ON DELETE CASCADE,
                FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE
            )');

            DB::statement('INSERT INTO queue_sessions_old (session_id, office_id, user_id, status, opened_at, closed_at, created_at, updated_at)
                SELECT session_id, office_id, user_id, status, opened_at, closed_at, created_at, updated_at FROM queue_sessions');

            DB::statement('DROP TABLE queue_sessions');
            DB::statement('ALTER TABLE queue_sessions_old RENAME TO queue_sessions');

            return;
        }

        Schema::table('queue_sessions', function (Blueprint $table) {
            $table->enum('status', ['open', 'closed'])->default('open')->change();
        });
    }
};
