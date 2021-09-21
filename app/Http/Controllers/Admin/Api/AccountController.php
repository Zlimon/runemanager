<?php

namespace App\Http\Controllers\Admin\Api;

use App\Account;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

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
    public function update(Request $request, Account $account)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::whereName($request->name)->orWhere('id', $request->name)->pluck('id')->first();

        if (!$user) {
            return response(['errors' => ['name' => ['This user could not be found.']]], 422);
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
