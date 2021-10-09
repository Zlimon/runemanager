<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
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
     * Show the user edit page.
     *
     * @param User $user
     * @return
     */
    public function edit(User $user)
    {
        $user = Auth::user();

        $randomIcons = [];

        for ($i = 0; count($randomIcons) < 5; $i++) {
            if ($icon = Helper::randomItemId(true)) {
                $randomIcons[] = $icon;
            }
        }

        return view('user.edit', compact('user', 'randomIcons'));
    }
}
