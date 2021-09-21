<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Rules\ValidateIconId;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'private' => ['boolean'],
            'icon_id' => ['integer', new ValidateIconId()],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->private = $request->private;
        $user->icon_id = $request->icon_id;

        $user->save();

        return response($user, 202);


//        $accountsList = $request->input('account');
//        $accountId = $request->input('accountId');
//
//        $found = [];
//        $notFound = [];
//
//        foreach ($accountsList as $key => $newOwner) {
//            if (!$newOwner) continue;
//
//            $account = Account::find($accountId[$key]);
//
//            if (!$account) continue;
//
//            $getNewOwner = User::where('name', $newOwner)->first();
//
//            if ($getNewOwner) {
//                $account->update([
//                    'user_id' => $getNewOwner->id
//                ]);
//
//                array_push($found, $getNewOwner->name);
//            } else {
//                $getNewOwner = User::find($newOwner);
//
//                if ($getNewOwner) {
//                    $account->update([
//                        'user_id' => $getNewOwner->id
//                    ]);
//
//                    array_push($found, $getNewOwner->name);
//                } else {
//                    array_push($notFound, $newOwner);
//                }
//            }
//        }
//
//        if (count($found) > 0) {
//            if (count($notFound) > 0) {
//                return redirect(route('admin-edit-user', $user))->with('message', ($user->wasChanged() ? 'Profile updated!' : '').'Accounts transferred to: '.implode(', ',$found))->withErrors(['Users not found: '.implode(',',$notFound)]);
//            } else {
//                return redirect(route('admin-edit-user', $user))->with('message', ($user->wasChanged() ? 'Profile updated!' : '').'Accounts transferred to: '.implode(', ',$found));
//            }
//        } elseif (count($notFound) > 0) {
//                if ($user->wasChanged()) {
//                    return redirect(route('admin-edit-user', $user))->with('message', 'Profile updated!')->withErrors(['Users not found: '.implode(',',$notFound)]);
//                } else {
//                    return redirect(route('admin-edit-user', $user))->withErrors(['Users not found: '.implode(',',$notFound)]);
//                }
//        } else {
//            if ($user->wasChanged()) {
//                return redirect(route('admin-edit-user', $user))->with('message', 'Profile updated!');
//            } else {
//                return redirect(route('admin-edit-user', $user));
//            }
//        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Search for a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request) {
        $this->validate($request, [
            'search' => ['required', 'max:13'],
        ]);

        $users = User::with('account')->where('name', 'LIKE', '%' . $request->search . '%')->get();

        return response($users, 200);
    }
}
