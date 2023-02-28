<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use YesTokenAuth;
use YesAuthority;
use App\Yantrana\Components\User\Models\User;

class YesAuthorityCheckpostMiddleware
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
    public function __construct(
        Guard $auth
    ) {
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
        config([
            '__tech.auth_info' => [
                'authorized' => false,
                'reaction_code' => 9,
            ]
        ]);

        $isVerified = YesTokenAuth::verifyToken();
        // Please see AuthenticateMiddleware
        if ($isVerified['error'] === false) {
            $userInfo = User::where('_id', $isVerified['aud'])->first();
            if (!__isEmpty($userInfo) && $userInfo->status == 2 || $userInfo->status == 5) {
                return __apiResponse([
                    'message' => __tr('Your Account seems to Inactive or Deleted, Please contact Administrator.'),
                    'auth_info' => getUserAuthInfo(9),
                ], 9);
            } else if (!__isEmpty($userInfo) && $userInfo->status == 1) {
                $authority = YesAuthority::withDetails()->check();

                if (($authority->is_access() === false) || (Auth::user()->status !== 1)) {
                    if ($authority->response_code() === 403) { // Authentication Required
                        if ($request->ajax()) {
                            return __apiResponse([
                                'message' => __tr('Please login to complete request.'),
                                'auth_info' => getUserAuthInfo(9),
                            ], 9);
                        }
                        return redirect()->route('user.login')
                            ->with([
                                'error' => true,
                                'message' => __tr('Please login to complete request.'),
                            ]);
                    }

                    // When it loggedIn But not permission to access route
                    if ($authority->response_code() === 401) { // Unauthorized

                        if ($request->ajax()) {

                            return __apiResponse([
                                'message' => __tr('Unauthorized Access.'),
                                'auth_info' => getUserAuthInfo(11),
                            ], 11);
                        }
                    }

                    return redirect()->route('manage.app')
                        ->with([
                            'error' => true,
                            'message' => __tr('Unauthorized.'),
                        ]);
                }
                config([
                    'app.yestoken.jti' => $isVerified['jti']
                ]);
            }
        } else {
            processLogoutAction();
            if ($request->ajax()) {
                return __apiResponse([
                    'message' => __tr('Please login to complete request.'),
                    'auth_info' => getUserAuthInfo(9),
                ], 9);
            }

            return redirect()->route('manage.app')
                ->with([
                    'error' => true,
                    'message' => __tr('Unauthorized.'),
                ]);
            // }
        }

        return $next($request);
    }
}
