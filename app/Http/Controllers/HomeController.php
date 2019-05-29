<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use RuneManager\Account;
use RuneManager\User;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the profile dashboard.
     *
     * @return \RuneManager\Account
     */
    public function index(User $user)
    {

//return auth()->user()->getAllPermissions();
//$role = Role::create(['guard_name' => 'admin', 'name' => 'superadmin']);

 auth()->user()->assignRole('Super Admin');

//return $users = User::role('admin')->get();
dd (auth()->user()->hasRole('Super Admin'));
//return $roles = $user->getRoleNames();

        $member = Auth::user()->member->first();

        if ($member == null) {
            return redirect(route('create-member'))->withErrors(['You must link an Old School RuneScape account to access this feature!']);
        } else {
            $skills = ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

            $stats = [];

            foreach ($skills as $skillName) {
                array_push($stats, DB::table($skillName)->where('account_id', $member->user_id)->get());
            }

            return view('home', compact('member', 'stats', 'skills'));
        }
    }
}
