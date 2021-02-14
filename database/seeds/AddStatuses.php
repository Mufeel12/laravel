<?php

use Illuminate\Database\Seeder;

class AddStatuses extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('statuses')->delete();

        $statuses = [
            [
                'id'         => 1,
                'name'       => 'active',
            ],
            [
                'id'         => 2,
                'name'       => 'suspended',
            ],
            [
                'id'         => 3,
                'name'       => 'deleted',
            ],
            [
                'id'         => 4,
                'name'       => 'brand_new_not_activated',
            ],
            [
                'id'         => 5,
                'name'       => 'payment_on_hold',
            ],
        ];

        DB::table('statuses')->insert($statuses);
    }
}

