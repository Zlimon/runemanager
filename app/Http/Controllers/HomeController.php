<?php

namespace App\Http\Controllers;

use App\Account;
use App\Helpers\Helper;
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

        $accounts = [];

        // TODO probably better ways to do this
        foreach ($user->account as $account) {
            $accounts[] = $account::with('user')->where('id', $account->id)->first();
        }

        if ($user->account == null || count($user->account) <= 0) {
            return redirect(route('account-create'))->withErrors(
                ['You must link an Old School RuneScape account to access this feature!']
            );
        } else {
            return view('home', compact('user', 'accounts'));
        }
    }

    public function forceLogout($accountUsername)
    {
        $account = Helper::checkIfUserOwnsAccount($accountUsername);

        $account->online = 0;

        $account->save();

        return redirect()->back()->with('message', 'Account successfully logged out!');
    }
}
