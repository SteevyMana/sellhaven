<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSequences extends Command
{
    protected $signature = 'db:sync-sequences';

    protected $description = 'Resync every table\'s auto-increment sequence with its current MAX(id) — fixes "duplicate key" errors after seeding with explicit IDs';

    public function handle(): int
    {
        $tables = DB::select("
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
        ");

        foreach ($tables as $table) {
            $tableName = $table->table_name;

            // Saltar tablas que no tienen columna "id" (ej. password_reset_tokens,
            // que usa "email" como llave primaria)
            $hasIdColumn = DB::selectOne("
                SELECT 1
                FROM information_schema.columns
                WHERE table_schema = 'public' AND table_name = ? AND column_name = 'id'
            ", [$tableName]);

            if (!$hasIdColumn) {
                continue;
            }

            $sequence = DB::selectOne("SELECT pg_get_serial_sequence('\"{$tableName}\"', 'id') as seq");

            // Saltar tablas cuya "id" no es serial/bigserial (no tiene secuencia)
            if (!$sequence || !$sequence->seq) {
                continue;
            }

            DB::statement("
                SELECT setval('{$sequence->seq}', COALESCE((SELECT MAX(id) FROM \"{$tableName}\"), 1))
            ");

            $this->line("Synced: {$tableName}");
        }

        $this->info('All sequences synced.');

        return self::SUCCESS;
    }
}