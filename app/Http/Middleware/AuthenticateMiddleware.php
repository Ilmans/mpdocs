<?php

namespace App\Http\Middleware;

use Closure;
use YesTokenAuth;
use Auth;
use Illuminate\Contracts\Auth\Guard;
use App\Yantrana\Components\User\Models\User;

class AuthenticateMiddleware
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // verify the token if has
        $isVerified = YesTokenAuth::verifyToken();
        // if its verified
        if ($isVerified['error'] === false) {
            // check if token is refreshed
            $tokenRefreshed = array_get($isVerified, 'refreshed_token', null);
            // if refreshed
            if ($tokenRefreshed) {
                // set refreshed token
                setAuthToken($tokenRefreshed);
            }
            // Identify the user
            $userInfo = User::where('_id', $isVerified['aud'])->first();
            // check the user status
            if (!__isEmpty($userInfo) && $userInfo->status == 1) {
                // if all seems to be ok, logged him in
                Auth::loginUsingId($isVerified['aud']);
            }
        }
        // and continue the request
        return $next($request);
    }
}
