<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration untuk tabel app_links
 * Menyimpan tautan aplikasi terkait OPD di daerah
 */
class CreateAppLinks extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('app_links')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Nama aplikasi/instansi',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Deskripsi singkat aplikasi',
            ],
            'logo_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => 'Path ke file logo',
            ],
            'url' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'comment' => 'URL tujuan aplikasi',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Status aktif',
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Urutan tampil',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['is_active', 'sort_order']);
        $this->forge->createTable('app_links', true);
    }

    public function down()
    {
        $this->forge->dropTable('app_links', true);
    }
}
