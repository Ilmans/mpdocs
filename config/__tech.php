<?php
// Response Codes & other global configurations
$techConfig = require app_path('Yantrana/__Laraware/Config/tech-config.php');

$techAppConfig = [

    /* Paths - required item will be replaced in custom tech config
    ------------------------------------------------------------------------- */
    'storage_paths' => [
        'public/media_storage' => [
            'other/no_thumb_image.jpg'	    => 'key@no_thumb',
            'other/no-user-thumb-icon.png'  => 'key@no_user_thumb',
            'users' => [
                '{_uid}' => [
                    'profile'      => 'key@user_photo',
                    'temp_uploads' => 'key@user_temp_uploads'
                ]
            ],
            'projects' => [
                '{_uid}' => [
                    'uploads'      => 'key@project_uploads'
                ]
            ],
            'logo' => 'key@logo',
            'favicon'   => 'key@favicon',
            'file-manager-assets/{userUid}/'			=> 'key@user_file_manager',
            'file-manager-assets/{userUid}/thumbnails/'	=> 'key@user_file_manager_thumb',
        ]
    ],
    // 'ignore_storage_paths' => '',

    /* Media extensions
    ------------------------------------------------------------------------- */
    'media' => [
        'extensions' => [
            1 => ["jpg", "png", "gif","jpeg"],
            2 => ["3gp","mp4", "flv"],
            3 => [],
            4 => ["mp3", "wav"],
            5 => ["pdf", "doc", "docx", "xls", "ppt", "txt"],
            6 => ["pdf", "jpg", "png", "gif","jpeg"],
            7 => ["png"], // for logo & profile
            8 => ["pdf", "doc", "docx", "xls", "ppt", "txt", "jpg", "png", "gif","jpeg"],
            9 => ["ico"],
            10 => ["png", "ico", 'svg']
        ]
    ],

	'login_url' => 'app#!/login',
	'reset_password_url' => 'app#!/reset-password/',

    /* if demo mode is on then set theme color
    ------------------------------------------------------------------------- */
    'theme_colors' => [
        'purple-white' => [
            'background' => '6900b9',
            'text' => 'ffffff'
        ],
        'blue-white' => [
            'background' => '005bb9',
            'text' => 'ffffff'
        ],
        'green-white' => [
            'background' => '008428',
            'text' => 'ffffff'
        ],
        'brown-white' => [
            'background' => '842500',
            'text' => 'ffffff'
        ],
        'gray-white' => [
            'background' => '424242',
            'text' => 'ffffff'
        ],
        'light-blue-white' => [
            'background' => '43a8ff',
            'text' => 'ffffff'
        ],
        'pink-white' => [
            'background' => 'e185e2',
            'text' => 'ffffff'
        ],
        'chocolate-white' => [
            'background' => 'a06000',
            'text' => 'ffffff'
        ],
    ],

	/* Status Code Multiple Uses
    ------------------------------------------------------------------------- */

    'status_codes' => [
        1 => 'Active',
        2 => 'Inactive',
        3 => 'Banned',
        4 => 'Never Activated',
        5 => 'Deleted',
        6 => 'Suspended',
        7 => 'On Hold',
        8 => 'Completed',
        9 => 'Invite'
    ],

    /* Durations Value
	------------------------------------------------------------------------*/
	'durations' => [
		1 => 'Current Month',
		2 => 'Last Month',
		3 => 'Current Week',
		4 => 'Last Week',
		5 => 'Today',
		6 => 'Yesterday',
		7 => 'Last Year',
		8 => 'Current Year',
		9 => 'Last 30 Days',
		10 => 'Custom'
	],

    /* Email Config
    ------------------------------------------------------------------------- */

    'mail_from'         =>  [
        env('MAIL_FROM_ADD', 'your@domain.com'),
        env('MAIL_FROM_NAME', 'E-Mail Service')
    ],

    'recaptcha'	=>  [
    	'site_key' => env('RECAPTCHA_PUBLIC_KEY',''),
        'secret_key' => env('RECAPTCHA_PRIVATE_KEY','')
    ],


    'client_urls'	=>  [
    	'login' => '/#!/login'
    ],


    /* Description String limit
    ------------------------------------------------------------------------- */
    'string_limit' => 30,

    'account_activation'  => (60*60*48),

    /* logo name & landing page image
    ------------------------------------------------------------------------- */

    'logoName'  => 'logo.png',

    /* favicon name
    ------------------------------------------------------------------------- */

    'faviconName'  => 'favicon.ico',

    /* Enable PDF Generation
    ------------------------------------------------------------------------- */
    'enable_pdf_generation' => env('ENABLE_PDF_GENERATION', false),

     /* Account related
    ------------------------------------------------------------------------- */

    'account' => [
        'activation_expiry'         => 24 * 2, // hours
        'change_email_expiry'       => 24 * 2, // hours
        'password_reminder_expiry'  => 24 * 2, // hours
    ],

    /* User
    ------------------------------------------------------------------------- */
    'user' => [
        'statuses' => [
            1,			// Active
            2,			// InActive
            5, 			// Deleted
            12			// Never Activated
        ],
        'roles' => [
            1 => 'Admin',
        ],
        'permission_status' => [
            1 => 'Role Inheritance',
            2 => 'Allow',
            3 => 'Deny'
        ]
    ],

    /* Projects
    ------------------------------------------------------------------------- */
    'project' => [
        'status' => [
            1,			// Active
            2,			// InActive
        ],

        'type' => [
            1 => 'Public',
            2 => 'Private'
        ],
        'logo_dimensions' => [
        	'height' => 200,
        	'width' => 50,
        ],
        'logoName' => 'logo'
    ],

    /* Articles
    ------------------------------------------------------------------------- */
    'article' => [

        'type' => [
            [
                'id'    => 1,
                'title' => 'Public'
            ],
            [
                'id'    => 2,
                'title' => 'Private '
            ]
        ],
        'status' => [
            [
                'id'    => 1,
                'title' => 'Published'
            ],
            [
                'id'    => 2,
                'title' => 'Draft'
            ],
            [
                'id'    => 3,
                'title' => 'Unpublished'
            ]
        ]

    ],

    /* Activity log status
    ------------------------------------------------------------------------- */
    'activity_log' => [
        'entity_type' => [
            1 => 'User',
            2 => 'Profile',
            3 => 'Role Permission',
            4 => 'User Login Log',
            5 => 'Password',
            6 => 'User Permission(s)',
            7 => 'Article',
            8 => 'Article Content',
            9 => 'Article Content History',
            10 => 'Article Vote(s)',
            11 => 'Article Comment(s)',
            12 => 'Article Tag(s)',
            13 => 'Project',
            14 => 'Language',
            15 => 'Comment',
            16 => 'Version',

        ],
        'action_type' => [
        	1 => 'Created',
        	2 => 'Updated',
        	3 => 'Deleted',
        	4 => 'Soft Deleted',
        	5 => 'Restored',
            6 => 'Logged in',
			7 => 'Logged Out',
            8 => 'Removed',
            9 => 'Added',
            10 => 'Stored',
        ]
    ],
    
    /* Technical Items Codes
    ------------------------------------------------------------------------- */

    'tech_items' => [
        1 	=> [
			'title'		=> 'Active',
			'id'		=> 1,
			'action'	=> 'Activate',
			'state'		=> 'Activated'
        ],
        2 	=> [
			'title'		=> 'Inactive',
			'id'		=> 2,
			'action'	=> 'Inactivate',
			'state'		=> 'Inactivated'
        ],
        3 	=> [
			'title'		=> 'Ban',
			'id'		=> 3,
			'action'	=> 'Ban',
			'state'		=> 'Banned'
        ],
        4 	=> [
			'title'		=> 'Unban',
			'id'		=> 4,
			'action'	=> 'Unban',
			'state'		=> 'Unbanned'
        ],
        5 	=> [
			'title'		=> 'Deleted',
			'id'		=> 5,
			'action'	=> 'Delete',
			'state'		=> 'Deleted'
        ],
        6 	=> [
			'title'		=> 'Suspend',
			'id'		=> 6,
			'action'	=> 'Suspend',
			'state'		=> 'Suspended'
        ],
        7 	=> [
			'title'		=> 'On Hold',
			'id'		=> 7,
			'action'	=> 'On Hold',
			'state'		=> 'On Hold'
        ],
        8 	=> [
			'title'		=> 'Complete',
			'id'		=> 8,
			'action'	=> 'Complete',
			'state'		=> 'Completed'
        ],
        9 	=> [
			'title'		=> 'Invite',
			'id'		=> 9,
			'action'	=> 'Invite',
			'state'		=> 'Invited'
        ],
        10 	=> [
			'title'		=> 'Publish',
			'id'		=> 10,
			'action'	=> 'Publish',
			'state'		=> 'Published'
        ],
        11 	=> [
			'title'		=> 'Unpublish',
			'id'		=> 11,
			'action'	=> 'Unpublish',
			'state'		=> 'Unpublished'
        ],
        12 	=> [
			'title'		=> 'Never Activate',
			'id'		=> 12,
			'action'	=> 'Never Activate',
			'state'		=> 'Never Activated'
        ],
        13 	=> [
			'title'		=> 'Awaiting moderation',
			'id'		=> 13,
			'action'	=> 'Awaiting moderation',
			'state'		=> 'Not Moderation'
        ],
        14 	=> [
			'title'		=> 'Approved',
			'id'		=> 14,
			'action'	=> 'Approve',
			'state'		=> 'Approved'
        ],
        15 	=> [
			'title'		=> 'Disapproved',
			'id'		=> 15,
			'action'	=> 'Disapprove',
			'state'		=> 'Disapproved'
        ],
    ],
    // WARNING!! Do not change, it may break application
    'entity_ownership_id' => '8a2dd8e4-655f-4acb-a3dd-a5a019032512',  
    'PRODUCT_VER' => env('PRODUCT_VER', '1.0.0.0'),

    /* Security configurations for encrypting/decrypting form values
     * one can generate these keys using like given in below example:

        $ openssl genrsa -out rsa_1024_priv.pem 1024
        $ openssl rsa -pubout -in rsa_1024_priv.pem -out rsa_1024_pub.pem

        ---------- OR ------------

        $ openssl genrsa -out rsa_aes256_priv.pem -aes256
        $ openssl rsa -pubout -in rsa_aes256_priv.pem -out rsa_aes256_pub.pem

    ------------------------------------------------------------------------- */
    'form_encryption' => [

        /* Passphrse for RSA Key
        --------------------------------------------------------------------- */
        'default_rsa_passphrase' => '0jVuVXNLudMoP4TnGBVY5V8AsADcrLyTcnLDKkPHLXY',

        /* Default Public RSA Key
        --------------------------------------------------------------------- */

        'default_rsa_public_key' => '-----BEGIN PUBLIC KEY-----
MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBANH4TV5bVWUoyd7pL3YKDjAe3LMD4Sl5
s6WB7OUrF5vMgJ0Zv81urTlZSaUssHhpGPObV3X331zAdOv/OEN3KRUCAwEAAQ==
-----END PUBLIC KEY-----',

        /* Default Private RSA Key
        --------------------------------------------------------------------- */

        'default_rsa_private_key' => '-----BEGIN RSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: AES-256-CBC,F0B5CDA2B9B44AF819B17D051F13D178

iJEe4XiPDyxRfFZSrRkogDKw0ApjPnRlodHM7LivDw7dQ7IlG2Uwpl18/5AJ2F5k
byVpf/gKWfWwt5mIqyWarFKsXsjUnncg70EezDHWaGYB/7Aj9YbYDBuCzzewK/de
T/m6gMb40N0iFzkhpNkFbxPQPK9wMEkpQ4MuJBpMcCe9/bl9tT4tTiCBcukq92Gj
YWyi48iLJmd13BAVsRsQ1ToNJIg3KF6qH29Cs7JJS1oRKdiH7YvDja0IFFLwTzlQ
Se/cZ7nJ23q8ozsgNiNXJ4gRmdVBmgUfB0ZIOzC+UiOmaql9BgJrqPtFE/4u+E8k
+6v5lDA0bHqoCFNnghnF5XuyMK5wivSmRqIfT4xYQxa+DzJtcLzVrOetg+Y8y+m9
8SJAmVhEumuysbwikcZuYfrY+XG6ut9H3orL0Uzhucg=
-----END RSA PRIVATE KEY-----',
    ],
];

$appTechConfig = [];
if (file_exists(base_path('user-tech-config.php'))) {
    $appTechConfig = require base_path('user-tech-config.php');
}
return array_merge( $techConfig, $techAppConfig, $appTechConfig);