<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Helpers\Helper;
use App\Account;

use App\Http\Resources\AccountResource;

class AccountController extends Controller
{
    /**
     * Show all the application accounts.
     *
     * @return
     */
    public function index() {
        return AccountResource::collection(Account::with('user')->inRandomOrder()->get());
    }


    /**
     * Show a specific account and skills data from a URL request.
     *
     * @param  string  $username
     * @return
     */
    public function show($account) {
        return (new AccountResource(Account::findOrFail($account)));
    }
}
