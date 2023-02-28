<?php

namespace App\Yantrana\Services\YesTokenAuth;

use App\Yantrana\Services\YesTokenAuth\TokenRegistry\Repositories\TokenRegistryRepository;
use \Firebase\JWT\JWT as FirebaseJwt;
use \Firebase\JWT\ExpiredException;
use Exception;
use YesSecurity;
use Cookie;
use Route;

/**
 * This YesTokenAuth class.
 *---------------------------------------------------------------- */
class YesTokenAuth
{
    /**
     * $key - encryption key
     *-----------------------------------------------------------------------*/
    private $useTokenRegistry = true;

    /**
     * $key - encryption key
     *-----------------------------------------------------------------------*/
    private $key = null;

    /**
     * $token - token key
     *-----------------------------------------------------------------------*/
    private $token = null;

    /**
     * $token - token key
     *-----------------------------------------------------------------------*/
    private $refreshedToken = false;

    /**
     * $ipAddress - record ip address
     *-----------------------------------------------------------------------*/
    private $ipAddress = false;

    /**
     * $userAgent - record ip address
     *-----------------------------------------------------------------------*/
    private $userAgent = true;

    /**
     * $expirationPeriod - default 5 days
     *-----------------------------------------------------------------------*/
    private $expirationPeriod; // = 60 * 60 * 24 * 5;    

    /**
     * $refreshTokenAfter - refresh token auto after
     *-----------------------------------------------------------------------*/
    private $refreshTokenAfter; // = 60 * 60;// * 24 * 4;

    /**
     * Token Registry Repository.
     *
     *-----------------------------------------*/
    protected $tokenRegistryRepository;

    /**
     * Route via URl.
     *
     *-----------------------------------------*/
    private $routeViaURL;

    /**
     * Route via Input
     *
     *-----------------------------------------*/
    private $routeViaInput;

    /**
     * __construct	
     * @param TokenRegistryRepository    $tokenRegistryRepository     - Token Registry Repository      
     *-----------------------------------------------------------------------*/
    public function __construct()
    {
        if (!$this->key) {
            $this->key  = config('app.key');
        }

        // FirebaseJwt::$leeway = 60;

        $this->userAgent = !config('app.debug');

        $this->initialize();

        if ($this->useTokenRegistry == true) {
            $this->tokenRegistryRepository = new TokenRegistryRepository();
        }
    }

    /**
     * initialization
     *-----------------------------------------------------------------------*/
    public function initialize()
    {
        $yesTokenConfig = config('yes-token-auth', []);
        $this->useTokenRegistry = array_get($yesTokenConfig, 'token_registry.enabled', false);
        $this->key = array_get($yesTokenConfig, 'encryption_key', config('app.key'));
        $this->userAgent = array_get($yesTokenConfig, 'verify_user_agent', true);
        $this->ipAddress = array_get($yesTokenConfig, 'verify_ip_address', true);
        $this->expirationPeriod = array_get($yesTokenConfig, 'expiration', (60 * 60 * 5)); // 5 hours
        $this->refreshTokenAfter = array_get($yesTokenConfig, 'refresh_after', (60 * 30));  // 30 mins
        $this->routeViaURL = array_get($yesTokenConfig, 'routes_via_url', []);
        $this->routeViaInput = array_get($yesTokenConfig, 'routes_via_input', []);

        config([
            'yes-token-auth.is_processed' => null
        ]);
    }

    /**
     * set Expiration
     *-----------------------------------------------------------------------*/
    public function setExpiration(int $expirationTime)
    {
        if ($expirationTime <= 0) {
            throw new Exception("YesTokenAuth: Expiration time can not be lower than 1");
        }

        $time = time();
        $this->expirationPeriod  = $expirationTime;
    }

    /**
     * Generate Token
     *
     * @param  $tokenItems
     * @return void
     *------------------------------------------------------------------------ */
    public function issueToken($tokenItems = [], $registryId = null)
    {
        $this->initialize();

        $time = time();
        $tokenData = array_merge(array(
            "typ" => "JWT",
            "alg" => "HS256",
            // The issuer of the token
            "iss" => config('app.name'),
            // session uid
            "suid" => YesSecurity::generateUid(),
            // The audience of the token
            "aud" => config('app.name'),
            // The subject of the token
            "sub" => 'auth token',
            // The time the JWT was issued. Can be used to determine the age of the JWT
            "iat" => $time,
            // Defines the time before which the JWT MUST NOT be accepted for processing
            "nbf" => $time,
            // This will probably be the registered claim most often used. 
            // This will define the expiration in NumericDate value. 
            // The expiration MUST be after the current date/time
            "exp" => $time + $this->expirationPeriod,
            "rta" => $time + $this->refreshTokenAfter, // Refresh token after this time
            // Unique identifier for the JWT. Can be used to prevent the JWT from being replayed. This is helpful for a one time use token
            "jti" => YesSecurity::generateUid(),
            // The audience of the token
            "uaid" => config('app.name'),
        ), $tokenItems);

        if(!$registryId) {
            $registryId = $tokenData['suid'];
        }

        $tokenData['uai'] = $_SERVER['HTTP_USER_AGENT']; // USER AGENT info
        $tokenData['cip'] = request()->getClientIp();

        $token = FirebaseJwt::encode($tokenData, $this->key);

        config([
            'app.yestoken.jti' => $tokenData['jti']
        ]);

        // make entry for db
        if ($this->useTokenRegistry == true) {

            $this->registryEntry($tokenData, $token, $registryId);
        }

        unset($time);
        return encrypt($token);
    }

    /**
     * Record data into the db
     *
     * @param  $tokenData
     * @param  $token
     * 
     * @return array
     *------------------------------------------------------------------------ */
    public function registryEntry(array $tokenData, $token, $registryId = null)
    {
        $schema = config('yes-token-auth.token_registry.schema', [
            'jti' => '_uid',
            'jwt_token' => 'jwt_token',
            'uaid' => 'user_authorities__id',
            'ip_address' => 'ip_address',
            'expiry_at' => 'expiry_at'
        ]);

        $tokenRegistryData = [];
        foreach ($schema as $schemaKey => $schemaValue) {
            if ($schemaKey !== 'jwt_token') {
                if ($schemaValue == 'ip_address') {

                    if (!isset($tokenData['ip_address'])) {
                        $tokenRegistryData['ip_address'] = request()->getClientIp();
                    }
                } else if ($schemaValue == 'expiry_at') {

                    if (!isset($tokenData['expiry_at'])) {
                        $tokenRegistryData['expiry_at'] = $this->expirationPeriod;
                    }
                } else {
                    $tokenRegistryData[$schemaValue] = $tokenData[$schemaKey];
                }
            } else {
                $tokenRegistryData[$schemaValue] = $token;
            }
        }

        if ($registryId) {
            $tokenRegistryData['predecessor_token_id'] = $registryId;
            // only delete old 
            $this->tokenRegistryRepository->delete($registryId, true);
        }

        return $this->tokenRegistryRepository->storeTokenRegistry($tokenRegistryData);
    }

    /**
     * Verify Token
     *
     * @param  $encryptedToken
     * @return array
     *------------------------------------------------------------------------ */
    public function verifyToken($encryptedToken = null)
    {
        if(config('yes-token-auth.is_processed')) {
            return config('yes-token-auth.is_processed');
        }

        if (!$encryptedToken) {

            $currentRoute = Route::currentRouteName();
            if (in_array($currentRoute, $this->routeViaURL)) {

                $encryptedToken = request()->get('auth_token');

                //check length of token if not greater than 36, then fetch from DB
                if (!(strlen($encryptedToken) > 36)) {

                    $tokenRegistryData = $this->tokenRegistryRepository->fetch($encryptedToken);

                    if (!__isEmpty($tokenRegistryData)) {
                        $encryptedToken = encrypt($tokenRegistryData->jwt_token);
                    }

                    if (__isEmpty($encryptedToken)) {
                        return [
                            'error' => 'token not registered'
                        ];
                    }
                }
            } else if (in_array($currentRoute, $this->routeViaInput)) {

                $encryptedToken = request()->input('yes_access_token');

                //check length of token if not greater than 36, then fetch from DB
                if (!(strlen($encryptedToken) > 36)) {

                    $tokenRegistryData = $this->tokenRegistryRepository->fetch($encryptedToken);

                    if (!__isEmpty($tokenRegistryData)) {
                        $encryptedToken = encrypt($tokenRegistryData->jwt_token);
                    }

                    if (__isEmpty($encryptedToken)) {
                        return [
                            'error' => 'token not registered'
                        ];
                    }
                }
            } else {
                //$encryptedToken = request()->header('authorization');
                $encryptedToken = request()->cookie('auth_access_token');
            }
        }

        $encryptedToken = str_replace('Bearer ', '', $encryptedToken);
        $decryptedToken = null;

        try {

            $decryptedToken = decrypt($encryptedToken);

            $decoded = FirebaseJwt::decode($decryptedToken, $this->key, array('HS256'));
            $this->refreshedToken = false;
            $time = time();

            if ($this->userAgent and ($decoded->uai != $_SERVER['HTTP_USER_AGENT'])) {
                return [
                    'error' => 'user agent mismatch'
                ];
            }

            if ($this->ipAddress) {
                if ($decoded->cip != request()->getClientIp()) {
                    return [
                        'error' => 'client ip mismatch'
                    ];
                }
            }

            // fetch required entry
            if ($this->useTokenRegistry == true) {

                $isRegistered = $this->tokenRegistryRepository->fetch($decoded->jti);

                // dd($isRegistered, $decoded->jti);

                if (__isEmpty($isRegistered)) {
                    return [
                        'error' => 'token not registered'
                    ];
                }

                if ($decryptedToken !== $isRegistered->{config('yes-token-auth.token_registry.schema.jwt_token', 'jwt_token')}) {
                    return [
                        'error' => 'token mismatched'
                    ];
                    // if($decoded->jti !== $isRegistered->predecessor_token_id) {
                    //     return [
                    //         'error' => 'token mismatched'
                    //     ];
                    // }
                }
            }

            // cleanup registry by deleting token not in use or expired
            $this->tokenRegistryRepository->cleanRegistry();

            if (($decoded->rta < $time) and ($decoded->exp > $time)) {
                $this->refreshedToken = $this->issueToken([
                    "aud" => $decoded->aud,
                    "jti" => YesSecurity::generateUid(),
                    "uaid" => $decoded->uaid,
                    // session uid 
                    "suid" => $decoded->suid,
                    "iat" => $time,
                    // Defines the time before which the JWT MUST NOT be accepted for processing
                    "nbf" => $time,
                    // This will probably be the registered claim most often used. 
                    // This will define the expiration in NumericDate value. 
                    // The expiration MUST be after the current date/time
                    "exp" => $time + $this->expirationPeriod,
                    "rta" => $time + $this->refreshTokenAfter, // Refresh token after this time
                ], $decoded->suid);

                $decoded->refreshed_token = $this->refreshedToken;
            }

            $decoded = (array) $decoded;
            $decoded['error'] = false;

            config([
                'yes-token-auth.is_processed' => $decoded
            ]);

            return $decoded;
        } catch (Exception $e) {
            $this->revokeAccessByToken($decryptedToken);

            return [
                'error' => $e->getMessage()
            ];
        }

        return false;
    }

    /**
     * Verify Token
     *
     * @return mixed
     *------------------------------------------------------------------------ */
    public function getRefreshed()
    {
        return $this->refreshedToken;
    }

    /**
     * Remove token access from db
     *
     * @return mixed
     *------------------------------------------------------------------------ */
    public function revokeAccessByToken($token)
    {
        return $this->tokenRegistryRepository->deleteByToken($token);
    }

    /**
     * Remove token access from db
     *
     * @return mixed
     *------------------------------------------------------------------------ */
    public function destroyTokenSession()
    {
        $tokenProcessed = config('yes-token-auth.is_processed');
        config([
            'yes-token-auth.is_processed' => null
        ]);
        $suid = array_get($tokenProcessed, 'suid');
        if($suid) {
            $this->tokenRegistryRepository->deleteTokenSession($suid);
        }
        $tokenProcessed = $suid = null;
        unset($tokenProcessed, $suid);
        return true;
    }

    /**
     * Get Expiration time period
     *
     * @return void
     */
    public function getExpirationPeriod()
    {
        return $this->expirationPeriod;
    }
}
