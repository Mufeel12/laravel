<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Permission;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$permissions_tree = Permission::getPermissionList();
		
		return response()->json($permissions_tree);
	}
	
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}
	
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		//
	}
	
	/**
	 * Display the specified resource.
	 *
	 * @param \App\Permission $permission
	 * @return \Illuminate\Http\Response
	 */
	public function show(Permission $permission)
	{
		//
	}
	
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param \App\Permission $permission
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Permission $permission)
	{
		//
	}
	
	/**
	 * Update the specified resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \App\Permission $permission
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Permission $permission)
	{
		//
	}
	
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param \App\Permission $permission
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Permission $permission)
	{
		//
	}
}
