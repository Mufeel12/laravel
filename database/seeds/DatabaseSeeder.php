<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		//$this->call(PermissionSeeder::class);
		//$this->call(AddSuperAdmin::class);
        $this->call(AddStatuses::class);
	}
}
