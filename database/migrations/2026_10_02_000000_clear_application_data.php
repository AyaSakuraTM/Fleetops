<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Application data tables to clear, ordered so that dependent tables
     * are emptied before the tables they reference.
     *
     * The users table is intentionally excluded so the admin account is preserved.
     *
     * @var list<string>
     */
    private array $tables = [
        'dispatch_logs',
        'dispatches',
        'trip_records',
        'fuel_logs',
        'location_logs',
        'reservations',
        'drivers',
        'vehicles',
        'alerts',
        'maintenance_records',
        'trusted_devices',
        'sessions',
        'password_reset_tokens',
        'personal_access_tokens',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            foreach ($this->tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                }
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        // Deleted data cannot be restored. Intentionally a no-op.
    }
};
