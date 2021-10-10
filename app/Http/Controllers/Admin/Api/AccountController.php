<?php

namespace App\Http\Controllers\Admin\Api;

use App\Account;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
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
        $request->validate([
            'account_username' => ['required', 'string', 'max:13'],
            'user_name' => ['max:255'],
        ]);

        if (Account::where('username', $request->account_username)->first()) {
            $errors = [
                'message' => 'Could not reserve account.',
                'errors' => [
                    'account_username' => [
                        'Account "' . $request->account_username . '" has already been registered.'
                    ],
                ],
            ];

            return response($errors, 422);
        }

        if (!$request->user_name && Auth::check()) {
            $user = Auth::id();
        } elseif ($request->user_name) {
            $user = User::whereName($request->user_name)->orWhere('id', $request->user_name)->pluck('id')->first();

            if (!$user) {
                $errors = [
                    'message' => 'Could not reserve account.',
                    'errors' => [
                        'user_name' => [
                            'User "' . $request->user_name . '" does not exist.'
                        ],
                    ],
                ];

                return response($errors, 404);
            }
        } else {
            $errors = [
                'message' => 'Could not reserve account.',
                'errors' => [
                    'user_name' => [
                        'No user name was provided, and could not use the current logged in user.'
                    ],
                ],
            ];

            return response($errors, 500);
        }

        DB::beginTransaction();

        $account = Helper::createOrUpdateAccount($request->account_username, 'normal', $user);

        if (!$account instanceof Account) {
            $errors = [
                'message' => 'Could not reserve account.',
                'errors' => [
                    'account_username' => [
                        $account
                    ],
                ],
            ];

            return response($errors, 500);
        }

        DB::commit();

        return response($account, 202);
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
    public function update(Request $request, Account $account)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::whereName($request->name)->orWhere('id', $request->name)->pluck('id')->first();
        if (!$user) {
            $errors = [
                'message' => 'Could not transfer account.',
                'errors' => [
                    'search' => [
                        'This user could not be found.',
                    ]
                ],
            ];

            return response($errors, 404);
        }

        $account->user_id = $user;

        $account->save();

        return response($account->user, 202);
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

        $accounts = Account::with('user')->where('username', 'LIKE', '%' . $request->search . '%')->get();

        return response($accounts, 200);
    }
}
