<?php

return [

    /* Configuration setting data-types id
    ------------------------------------------------------------------------- */
    'datatypes'  => [
        'string' => 1,
        'bool'   => 2,
        'int'    => 3,
        'json'   => 4
    ],

    /* Technical Configuration Codes
    ------------------------------------------------------------------------- */
    'items' => [
        // General Tab
        'name' => [
            'key'           => 'name',
            'data_type'     => 1,    // string,
            'placeholder'   => 'Your Website Name',
            'default'       => 'Docsyard'
        ],
        'logo_image' => [
            'key'           => 'logo_image',
            'data_type'     => 1,    // string
            'placeholder'   => 'Select Logo',
            'default'       => 'logo.png'
        ],
        'favicon_image' => [
            'key'           => 'favicon_image',
            'data_type'     => 1,    // string
            'placeholder'   => 'Select Favicon',
            'default'       => 'favicon.ico'
        ],
        'header_background_color' => [
            'key'           => 'header_background_color',
            'data_type'     => 1,    // string
            'placeholder'   => 'Set the background color of logo',
            'default'       => 'ffffff' // light blue
        ],
        'header_text_link_color' => [
            'key'           => 'header_text_link_color',
            'data_type'     => 1,    // string
            'placeholder'   => 'Set the primary color',
            'default'       => '000000' // blue
        ],
        'business_email' => [
            'key'           => 'business_email',
            'data_type'     => 1,    // string
            'placeholder'   => 'your-email-address@example.com',
            'default'       => ''
        ],
        'contact_email' => [
            'key'           => 'contact_email',
            'data_type'     => 1,    // string
            'placeholder'   => 'your-email-address@example.com',
            'default'       => ''
        ],
        'show_captcha' => [
            'key'           => 'show_captcha',
            'data_type'     => 3,       // integer
            'default'       => 5
        ],
        'footer_text'       => [
            'key'           => 'footer_text',
            'data_type'     => 1,     // string
            'placeholder'   => 'Set footer text.',
            'default'       => ''
        ],
        'enable_credit_info' => [
            'key'           => 'enable_credit_info',
            'data_type'     => 2,    // boolean
            'default'       => true
        ],
        'recaptcha_site_key'   => [
            'key'           => 'recaptcha_site_key',
            'data_type'     => 1,    // string
            'default'       => ''
        ],
        'recaptcha_secret_key'   => [
            'key'           => 'recaptcha_secret_key',
            'data_type'     => 1,    // string
            'default'       => ''
        ],
		'enable_login_attempt'   => [
            'key'           => 'enable_login_attempt',
            'data_type'     => 2,    // string
            'default'       => false
        ],
		'restrict_user_email_update'   => [
            'key'           => 'restrict_user_email_update',
            'data_type'     => 2,    // string
            'default'       => true
        ]
    ]
];