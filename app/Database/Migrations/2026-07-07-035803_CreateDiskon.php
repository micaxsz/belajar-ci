<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDiskon extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_diskon' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'persen' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
            ],
        ]);

        $this->forge->addKey('id_diskon', true);

        // Agar tidak ada tanggal yang sama
        $this->forge->addUniqueKey('tanggal');

        $this->forge->createTable('diskon');
    }

    public function down()
    {
        $this->forge->dropTable('diskon');
    }
}
