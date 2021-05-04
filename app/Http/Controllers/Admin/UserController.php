<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

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

        $users = User::with('member')->where('name', 'LIKE', '%' . $query . '%')->paginate(10);

        if (count($users) === 0) {
            return redirect(route('admin-user'))->withErrors(['No search results for "'.$query.'"!']);
        } else {
            return view('admin.user.index', compact('users', 'query'));
        }
    }

    public function show($id) {
        $user = User::findOrFail($id);

        $accounts = Account::where('user_id', $id)->get();

        return view('admin.user.show', compact('user', 'accounts'));
    }

    public function edit($id) {
        $user = User::with('member')->findOrFail($id);

        return view('admin.user.edit', compact('user'));
    }

    public function update(User $id, Request $request) {
        $accountsList = $request->input('account');
        $accountId = $request->input('accountId');

        if (request('icon_id') == null || request('icon_id') == 0 || Helper::verifyItem(request('icon_id'))) {
            $id->update(request()->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255',
                    Rule::unique('users')->ignore($id),
                ],
                'private' => ['boolean'],
                'icon_id' => ['nullable', 'integer'],
            ]));

            $found = [];
            $notFound = [];

            foreach ($accountsList as $key => $newOwner) {
                if ($newOwner != null) {
                    $account = Account::find($accountId[$key]);

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
            }

            if (count($found) > 0) {
                if (count($notFound) > 0) {
                    return redirect(route('admin-edit-user', $id))->with('message', ($id->wasChanged() ? 'Profile updated!' : '').'Accounts transferred to: '.implode(', ',$found))->withErrors(['Users not found: '.implode(',',$notFound)]);
                } else {
                    return redirect(route('admin-edit-user', $id))->with('message', ($id->wasChanged() ? 'Profile updated!' : '').'Accounts transferred to: '.implode(', ',$found));
                }
            } elseif (count($notFound) > 0) {
                    if ($id->wasChanged()) {
                        return redirect(route('admin-edit-user', $id))->with('message', 'Profile updated!')->withErrors(['Users not found: '.implode(',',$notFound)]);
                    } else {
                        return redirect(route('admin-edit-user', $id))->withErrors(['Users not found: '.implode(',',$notFound)]);
                    }
            } else {
                if ($id->wasChanged()) {
                    return redirect(route('admin-edit-user', $id))->with('message', 'Profile updated!');
                } else {
                    return redirect(route('admin-edit-user', $id));
                }
            }
        } else {
            return redirect(route('admin-edit-user', $id))->withErrors(['Invalid icon ID']);
        }
    }
}
