<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use RuneManager\User;
use RuneManager\Helpers\Helper;

class UsersController extends Controller
{
    /**
     * Show the user edit page.
     *
     * @param  User  $user
     * @return
     */
    public function edit(User $user) {
        $user = Auth::user();

        if ($user) {
            $randomIcons = [];

            for ($i=0; count($randomIcons) < 12; $i++) {
                if ($icon_id = Helper::randomItemId()) {
                    array_push($randomIcons, $icon_id);
                }
            }

            return view('user.edit', compact('user', 'randomIcons'));
        } else {
            return redirect()->back();
        }
    }

    /**
     * Updates user after a valid request.
     *
     * @param  User  $user
     * @return
     */
    public function update(User $user) {
        $user = Auth::user();

        if ($user) {
            $iconId = request('icon_id');

            if ($iconId == null) {
                $iconId = 0;
            }

            if (Helper::verifyItem($iconId)) {
                $user->update(request()->validate([
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 
                        Rule::unique('users')->ignore($user->id),
                    ],
                    'private' => ['boolean'],
                    'icon_id' => ['nullable', 'integer']
                ]));

                return redirect(route('home'))->with('message', 'Profile updated!');
            } else {
                return redirect()->back()->withErrors('Not a valid icon ID!');
            }
        } else {
            return redirect()->back();
        }
    }
}
