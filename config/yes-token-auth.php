<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Yes Token Auth
    |--------------------------------------------------------------------------
    |
    | Manage your token auth configurations
    |
    */
    'refresh_after' => 10, //60 * 30, // 30 mins
    'expiration' => 60 * 60 * 3, // 3 hours
    'verify_user_agent' => true, // whatever to cross check user agent
    'verify_ip_address' => false, // whatever to cross check ip address
    'token_registry' => [
        'enabled' => true,
        'schema' => [
            'jti' => '_uid',
            'jwt_token' => 'jwt_token',
            'uaid' => 'user_authorities__id',
            'ip_address' => 'ip_address',
            'expiry_at' => 'expiry_at'
        ]
    ],
    'routes_via_url' => [
    	// 'file_manager',
		// 'file_manager.file.download'
    ],
    'routes_via_input' => []
];
