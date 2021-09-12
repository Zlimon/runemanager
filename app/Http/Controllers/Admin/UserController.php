<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index() {
        $users = User::with('account')->orderByDesc('created_at')->get();

        $query = null;

        return view('admin.user.index', compact('users', 'query'));
    }

    public function search() {
        request()->validate([
            'search' => ['required', 'string', 'min:1', 'max:13'],
        ]);

        $query = request('search');

        $users = User::with('account')->where('name', 'LIKE', '%' . $query . '%')->get();

        if (!sizeof($users)) {
            return redirect(route('admin-user'))->withErrors(['No search results for "'.$query.'"!']);
        }

        return view('admin.user.index', compact('users', 'query'));
    }

    public function show(User $user) {
        return view('admin.user.show', compact('user'));
    }

    public function edit(User $user) {
        return view('admin.user.edit', compact('user'));
    }

    public function update(User $user, Request $request) {
        if (!Helper::verifyItem(request('icon_id'))) {
            return redirect(route('admin-edit-user', $user))->withErrors(['Invalid icon ID']);
        }

        $user->update(request()->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'private' => ['boolean'],
            'icon_id' => ['integer'],
        ]));

        $accountsList = $request->input('account');
        $accountId = $request->input('accountId');

        $found = [];
        $notFound = [];

        foreach ($accountsList as $key => $newOwner) {
            if (!$newOwner) continue;

            $account = Account::find($accountId[$key]);

            if (!$account) continue;

            $getNewOwner = User::where('name', $newOwner)->first();

            if ($getNewOwner) {
                $account->update([
                    'user_id' => $getNewOwner->id
                ]);

                array_push($found, $getNewOwner->name);
            } else {
                $getNewOwner = User::find($newOwner);

                if ($getNewOwner) {
                    $account->update([
                        'user_id' => $getNewOwner->id
                    ]);

                    array_push($found, $getNewOwner->name);
                } else {
                    array_push($notFound, $newOwner);
                }
            }
        }

        if (count($found) > 0) {
            if (count($notFound) > 0) {
                return redirect(route('admin-edit-user', $user))->with('message', ($user->wasChanged() ? 'Profile updated!' : '').'Accounts transferred to: '.implode(', ',$found))->withErrors(['Users not found: '.implode(',',$notFound)]);
            } else {
                return redirect(route('admin-edit-user', $user))->with('message', ($user->wasChanged() ? 'Profile updated!' : '').'Accounts transferred to: '.implode(', ',$found));
            }
        } elseif (count($notFound) > 0) {
                if ($user->wasChanged()) {
                    return redirect(route('admin-edit-user', $user))->with('message', 'Profile updated!')->withErrors(['Users not found: '.implode(',',$notFound)]);
                } else {
                    return redirect(route('admin-edit-user', $user))->withErrors(['Users not found: '.implode(',',$notFound)]);
                }
        } else {
            if ($user->wasChanged()) {
                return redirect(route('admin-edit-user', $user))->with('message', 'Profile updated!');
            } else {
                return redirect(route('admin-edit-user', $user));
            }
        }
    }
}
