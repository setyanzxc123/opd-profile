<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Throwable;

class InitDatabase extends BaseCommand
{
    protected $group       = 'OPD';
    protected $name        = 'opd:init';
    protected $description = 'Create the configured database if missing, then run migrations and seeders.';

    public function run(array $params)
    {
        $group = $params['group'] ?? CLI::getOption('group') ?? 'default';

        $config = config('Database');
        if (! isset($config->$group)) {
            CLI::error("Database group '{$group}' tidak ditemukan di konfigurasi.");
            return;
        }

        $dbConfig = (array) $config->$group;
        $database = $dbConfig['database'] ?? '';

        if ($database === '') {
            CLI::error('Nama database belum diset di konfigurasi. Periksa .env atau app/Config/Database.php.');
            return;
        }

        CLI::write("Menyiapkan database '{$database}'...", 'yellow');

        $serverConfig = $dbConfig;
        $serverConfig['database'] = null;

        try {
            $db   = Database::connect($serverConfig, true);
            $forge = Database::forge($db);

            if ($forge->createDatabase($database, true)) {
                CLI::write("Database '{$database}' tersedia.", 'green');
            }

            $db->close();
        } catch (Throwable $e) {
            CLI::error('Gagal memastikan database: ' . $e->getMessage());
            return;
        }

        CLI::write('Menjalankan migrate --all ...', 'yellow');
        $this->call('migrate', ['--all' => null, '--group' => $group]);

        CLI::write('Menjalankan db:seed CoreSeeder ...', 'yellow');
        $this->call('db:seed', ['CoreSeeder']);

        CLI::write('Selesai.', 'green');
    }
}
