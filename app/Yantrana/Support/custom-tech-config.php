<?php

// default time zone
$timeZone = 'UTC';

date_default_timezone_set($timeZone);

// set configuration items
config([
    'app.timezone' => $timeZone,
    'mail.from.address' => env('MAIL_FROM_ADD') ?: getConfigurationSettings('business_email'),
    'mail.from.name' => getConfigurationSettings('name'),
    '__tech.mail_from' => [
        env('MAIL_FROM_ADD', getConfigurationSettings('business_email')),
        env('MAIL_FROM_NAME', getConfigurationSettings('name')),
    ],
    '__tech.recaptcha'	=>  [
    	'site_key' =>  env('RECAPTCHA_PUBLIC_KEY', getConfigurationSettings('recaptcha_site_key')),
        'secret_key' => env('RECAPTCHA_PRIVATE_KEY', getConfigurationSettings('recaptcha_secret_key')),
    ],
    '__tech.storage_paths.media_storage' => 
        [
              entityOwnershipId() => [
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
                'attachment' => [
                    'issue/{issue_uid}' => 'key@issue_attachment',
                    'requirement/{requirement_uid}' => 'key@requirement_attachment'
                ],
                'logo' => 'key@logo',
                'favicon'   => 'key@favicon',
                'invoice_logo' => 'key@invoice_logo',
                'file-manager-assets/{userUid}/'			=> 'key@user_file_manager',
                'file-manager-assets/{userUid}/thumbnails/'	=> 'key@user_file_manager_thumb',
            ]
    ],
]);