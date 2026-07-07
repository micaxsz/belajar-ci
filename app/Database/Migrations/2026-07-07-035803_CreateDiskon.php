<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDiskon extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'nominal' => [
                'type' => 'DOUBLE',
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addUniqueKey('tanggal');

        $this->forge->createTable('discount');
    }

    public function down()
    {
        $this->forge->dropTable('discount');
    }
}

