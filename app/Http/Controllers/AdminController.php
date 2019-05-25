<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use RuneManager\User;
use RuneManager\Account;

class AdminController extends Controller
{
    public function index() {
    	$users = User::count();

        return view('admin.index', compact('users'));
    }
}
