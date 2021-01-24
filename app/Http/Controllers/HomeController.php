<?php

namespace App\Http\Controllers;

use App\Account;
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
            return redirect(route('account-create'))->withErrors(
                ['You must link an Old School RuneScape account to access this feature!']
            );
        } else {
            return view('home', compact('user'));
        }
    }

    public function forceLogout($accountUsername)
    {
        $account = Account::where('user_id', auth()->user()->id)->where('username', $accountUsername)->first();

        if ($account) {
            $account->online = 0;

            $account->save();

            return redirect()->back()->with('message', 'Account successfully logged out!');
        } else {
            return response("This account is not authenticated with " . auth()->user()->name, 401);
        }
    }
}
