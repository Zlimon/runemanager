<?php

namespace App\Http\Middleware;

use App\Account;
use Closure;
use Illuminate\Http\Request;

class EnsureUserOwnsAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $account = $request->route('account');

        $hasOwnership = Account::where('user_id', auth()->user()->id)->where('username', $account->username)->first();

        if (!$hasOwnership) {
            return response($account->username." is not authenticated with ".auth()->user()->name, 401);
        }

        return $next($request);
    }
}
