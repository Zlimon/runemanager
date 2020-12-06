<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->account == null || count($user->account) <= 0) {
            return redirect(route('account-create'))->withErrors(['You must link an Old School RuneScape account to access this feature!']);
        } else {
            return view('home', compact('user'));
        }
    }
}
