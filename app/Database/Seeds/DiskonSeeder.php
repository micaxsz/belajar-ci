<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
class DiskonSeeder extends Seeder
{
    public function run()
    {
        $data = [];

        $today = new \DateTime();

        // Let's seed today with exactly 100,000 discount
        $data[] = [
            'tanggal' => $today->format('Y-m-d'),
            'nominal' => 100000,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        for ($i = 1; $i < 10; $i++) {

            $tanggal = clone $today;
            $tanggal->modify("+{$i} day");

            $data[] = [
                'tanggal' => $tanggal->format('Y-m-d'),
                'nominal' => rand(5000, 50000),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null,
            ];
        }

        $this->db->table('discount')->insertBatch($data);
    }
}

