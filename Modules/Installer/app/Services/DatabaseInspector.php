<?php

namespace Modules\Installer\Services;

use PDO;
use PDOException;

class DatabaseInspector
{
    public function inspect(array $data): array
    {
        try {
            $pdo = $this->connect($data);
            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            $partial = $this->isFLOWPartialInstall($pdo, $tables);

            return [
                'ok' => true,
                'empty' => count($tables) === 0,
                'partial' => $partial,
                'table_count' => count($tables),
                'message' => count($tables) === 0
                    ? 'Database connection succeeded and the database is safe to initialize.'
                    : ($partial
                        ? 'A previous FLOW installation was detected in this database. Continuing will DROP those tables and install again from scratch — any data already entered will be lost.'
                        : 'The selected database contains tables that are not FLOW\'s. FLOW will not modify an existing database.'),
            ];
        } catch (PDOException $e) {
            return ['ok' => false, 'empty' => false, 'partial' => false, 'table_count' => 0, 'message' => 'Could not connect to the database. Verify the host, port, database, and credentials.'];
        }
    }

    private function isFLOWPartialInstall(PDO $pdo, array $tables): bool
    {
        if (! in_array('migrations', $tables, true)) {
            return false;
        }
        $migrations = $pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
        if ($migrations === []) {
            return false;
        }
        $known = collect(array_merge(
            glob(database_path('migrations/*.php')) ?: [],
            glob(base_path('Modules/*/database/migrations/*.php')) ?: [],
        ))
            ->map(fn ($path) => pathinfo($path, PATHINFO_FILENAME))->all();

        return collect($migrations)->every(fn ($migration) => in_array($migration, $known, true));
    }

    private function connect(array $data): PDO
    {
        $host = str_replace([';', "\0"], '', (string) $data['db_host']);
        $port = (int) $data['db_port'];
        $database = str_replace('`', '', (string) $data['db_database']);

        return new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", (string) $data['db_username'], (string) ($data['db_password'] ?? ''), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
    }
}
