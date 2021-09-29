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
            return response(['errors' => ['account_username' => ['This account has already been registered.']]], 422);
        }

        if (!$request->user_name && Auth::check()) {
            $user = Auth::id();
        } elseif ($request->user_name) {
            $user = User::whereName($request->user_name)->orWhere('id', $request->user_name)->pluck('id')->first();

            if (!$user) {
                return response(['errors' => ['user_name' => ['This user could not be found.']]], 422);
            }
        } else {
            return response(['errors' => ['user_name' => ['This user could not be found.']]], 422);
        }

        $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player=' . str_replace(
                ' ',
                '%20',
                $request->account_username
            );

        /* Get the $playerDataUrl file content. */
        $playerData = Helper::getPlayerData($playerDataUrl);

        if (!$playerData) {
            return response(['errors' => ['account_username' => ['Could not fetch player data from hiscores.']]], 422);
        }

        DB::beginTransaction();

        try {
            $account = new Account();

            $account->user_id = $user;
            $account->account_type = 'normal';
            $account->username = $request->account_username;
            $account->rank = $playerData[0][0];
            $account->level = $playerData[0][1];
            $account->xp = $playerData[0][2];

            $account->save();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        try {
            Helper::createOrUpdateAccountHiscores($account, $playerData);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
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
