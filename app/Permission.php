<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
	protected $table = 'permissions';
	
	protected $fillable = [
		'name', 'permission', 'parent_id', 'order_num'
	];
	
	protected $hidden = [
		'pivot', 'created_at', 'updated_at'
	];
	
	public static function getPermissionList()
	{
		$permissions = self::where('parent_id', '0')->orderBy('order_num')->get();
		$list = [];
		if ($permissions) {
			foreach ($permissions as $key => $permission) {
				$list[$key] = [
					'id'       => $permission->id,
					'label'    => $permission->name,
					'children' => []
				];
				
				$sub_perms = self::where('parent_id', $permission->id)->orderBy('order_num')->get();
				if ($sub_perms) {
					foreach ($sub_perms as $s_key => $sub_perm) {
						$list[$key]['children'][$s_key] = [
							'id'    => $sub_perm->id,
							'label' => $sub_perm->name,
						];
					}
				}
			}
		}
		
		return $list;
	}
}
