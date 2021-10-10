<?php

namespace App\Http\Middleware;

use App\AccountAuthStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserOwnAccountAuthStatus
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
        $accountAuthStatus = $request->route('accountAuthStatus');

        $hasOwnership = AccountAuthStatus::whereUserId(Auth::id())->find($accountAuthStatus->id);

        if (!$hasOwnership) {
            $errors = [
                'message' => 'The application has encountered an error.',
                'errors' => [['Account "'.$accountAuthStatus->username.'" is not linked to user "'.Auth::user()->name.'".',]
                ],
            ];

            return response($errors, 401);
        }

        return $next($request);
    }
}
