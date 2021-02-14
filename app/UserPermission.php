<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
	protected $table = 'user_permissions';
	
	protected $fillable = ['user_id', 'permission_id', 'permission'];
	
	public static function createDefaultUserPermissions($user, $role)
	{
		$permissions = Permission::where('parent_id', '<>', '0')->get();
		
		if ($permissions) {
			foreach ($permissions as $row) {
				if (!$row->parent_id) {
					continue;
				} else {
					$user_permission = new UserPermission();
					$user_permission->user_id = $user->id;
					$user_permission->permission_id = $row->id;
					if ($role == 'owner') {
						$user_permission->permission = 1;
					} else {
						$sub_user_perms = ['create-project', 'delete-project', 'edit-stage', 'view-analytics', 'view-contacts', 'export-contacts'];
						if (array_search($row->permission, $sub_user_perms) !== false) {
							$user_permission->permission = 1;
						} else {
							$user_permission->permission = 0;
						}
					}
					
					$user_permission->save();
				}
			}
		}
	}
}
