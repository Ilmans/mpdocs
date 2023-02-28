<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

use YesAuthority;
use URL;
use Session;
use Route;
use YesTokenAuth;
use Subscription;

class AccountCheckpostMiddleware
{
	
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $isVerified = YesTokenAuth::verifyToken();

        /* $isVerified = YesTokenAuth::verifyToken();
        $accountId = $isVerified['aid'];
        $userId = $isVerified['aud'];

        // Fetch the account information from accounts table with related subscription
        $account = AccountModel::with('subscription')
                            ->where('_id', $accountId)
                            ->first();

        if(__isEmpty($account)) {
            return __apiResponse([
                'message' => __tr('Invalid or Account not exists'),
                'auth_info' => getUserAuthInfo(11),
            ], 18);
        }

        config([
            '__tech.accountInfo' => $account->toArray()
        ]); */

		// __pr(Subscription::inTrial(), 'test');

		return $next($request);
    }
}