<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    public function index() {
    	$roles = Role::get();

    	dd($roles);

    	return view('admin.rank.index', compact('roles'));
    }
}
