<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('permissions')->delete();

		$permissions = [
			[
				'id'         => 1,
				'name'       => 'Project',
				'permission' => 'project-all',
				'parent_id'  => 0,
				'order_num'  => 0,
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'id'         => 2,
				'name'       => 'Create Project',
				'permission' => 'create-project',
				'parent_id'  => 1,
				'order_num'  => 0,
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'id'         => 3,
				'name'       => 'Delete Project',
				'permission' => 'delete-project',
				'parent_id'  => 1,
				'order_num'  => 1,
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'id'         => 4,
				'name'       => 'Upload Video',
				'permission' => 'upload-video',
				'parent_id'  => 1,
				'order_num'  => 2,
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'id'         => 5,
				'name'       => 'Customize Video',
				'permission' => 'customize-video',
				'parent_id'  => 1,
				'order_num'  => 3,
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'id'         => 6,
				'name'       => 'Delete Video',
				'permission' => 'delete-video',
				'parent_id'  => 1,
				'order_num'  => 4,
				'created_at' => now(),
				'updated_at' => now(),
			],
			
			[
				'id'         => 7,
				'name'       => 'Stage',
				'permission' => 'stage-all',
				'parent_id'  => 0,
				'order_num'  => 1,
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'id'         => 8,
				'name'       => 'Edit Stage',
				'permission' => 'edit-stage',
				'parent_id'  => 7,
				'order_num'  => 0,
				'created_at' => now(),
				'updated_at' => now(),
			],
			
			[
				'id'         => 9,
				'name'       => 'Analytics',
				'permission' => 'analytics-all',
				'parent_id'  => 0,
				'order_num'  => 2,
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'id'         => 10,
				'name'       => 'View Analytics',
				'permission' => 'view-analytics',
				'parent_id'  => 9,
				'order_num'  => 0,
				'created_at' => now(),
				'updated_at' => now(),
			],
			
			[
				'id'         => 11,
				'name'       => 'Contacts',
				'permission' => 'contacts-all',
				'parent_id'  => 0,
				'order_num'  => 3,
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'id'         => 12,
				'name'       => 'View Contacts',
				'permission' => 'view-contacts',
				'parent_id'  => 11,
				'order_num'  => 0,
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'id'         => 13,
				'name'       => 'Export Contacts',
				'permission' => 'export-contacts',
				'parent_id'  => 11,
				'order_num'  => 1,
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'id'         => 14,
				'name'       => 'Edit Contacts',
				'permission' => 'edit-contacts',
				'parent_id'  => 11,
				'order_num'  => 2,
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'id'         => 15,
				'name'       => 'Delete Contacts',
				'permission' => 'delete-contacts',
				'parent_id'  => 11,
				'order_num'  => 3,
				'created_at' => now(),
				'updated_at' => now(),
			],
		];
		
		DB::table('permissions')->insert($permissions);
	}
}
