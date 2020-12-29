<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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

        for ($i = 0; count($randomIcons) < 12; $i++) {
            if ($icon_id = Helper::randomItemId()) {
                array_push($randomIcons, $icon_id);
            }
        }

        return view('user.edit', compact('user', 'randomIcons'));
    }

    /**
     * Updates user after a valid request.
     *
     * @param User $user
     * @return
     */
    public function update(User $user)
    {
        if ((int)request('icon_id') > 0 && Helper::verifyItem(request('icon_id'))) {
            Auth::user()->update(request()->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore(Auth::user()->id),
                ],
                'private' => ['boolean'],
                'icon_id' => ['integer']
            ]));

            return redirect(route('home'))->with('message', 'Profile updated!');
        } else {
            return redirect()->back()->withErrors('Invalid icon ID! Try an unnoted item.');
        }
    }
}
