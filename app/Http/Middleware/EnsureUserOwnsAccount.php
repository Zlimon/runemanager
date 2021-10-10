<?php

namespace App\Http\Middleware;

use App\Account;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $hasOwnership = Account::whereUserId(Auth::id())->where('username', $account->username)->first();

        if (!$hasOwnership) {
            $errors = [
                'message' => 'The application has encountered an error.',
                'errors' => [['Account "'.$account->username.'" is not authenticated to user "'.Auth::user()->name.'".',]
                ],
            ];

            return response($errors, 401);
        }

        return $next($request);
    }
}
