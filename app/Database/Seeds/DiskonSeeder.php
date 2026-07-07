<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
class DiskonSeeder extends Seeder
{
    public function run()
    {
        $data = [];

        $today = new \DateTime();

        for ($i = 0; $i < 10; $i++) {

            $tanggal = clone $today;
            $tanggal->modify("+{$i} day");

            $data[] = [
                'tanggal' => $tanggal->format('Y-m-d'),
                'nominal' => rand(5000, 50000),
                'created_at' => date('Y-m-d H:i:s'),
                'uploaded_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null,
            ];
        }

        $this->db->table('diskon')->insertBatch($data);
    }
}

