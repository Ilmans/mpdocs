<?php
// Prevent direct access
if (!defined('LW_INSTALLER')) {
    header("HTTP/1.0 404 Not Found");
    die();
}

return [
    'project_name' => 'Docsyard',
    'project_path' => './../',
    'sql_file' => '../../app/Yantrana/Support/install/docsyard.sql',
    'env_file' => '../../.env',
    'success_redirect' => '../../app-configuration',
    'envItems' => [
        'DB_HOST' => [
            'type' => 'text',
            'item_type' => 'database_host',
            'required' => true,
            'value' => 'localhost',
            'note' => 'in most cases 127.0.0.1 or localhost'
        ],
        'DB_PORT' => [
            'type' => 'number',
            'item_type' => 'database_port',
            'required' => true,
            'value' => 3306
        ],
        'DB_DATABASE' => [
            'type' => 'text',
            'item_type' => 'database_name',
            'required' => true,
        ],
        'DB_USERNAME' => [
            'type' => 'text',
            'item_type' => 'database_username',
            'required' => true,
        ],
        'DB_PASSWORD' => [
            'type' => 'password',
            'item_type' => 'database_password',
            'required' => false,
        ],
    ],
    'requirements' => [
        'min_php_version' => '7.2.5',
        'mysql_version' => '5.7.0',
        'extensions' => [
            'Fileinfo' => '*',
            'OpenSSL' => '*',
            'PDO' => '*',
            'Mbstring' => '*',
            'Tokenizer' => '*',
            'GD' => '*',
            'JSON' => '*',
            'XML' => '*',
            'ctype' => '*',
            'zip' => '*',
            'Curl' => '*',
            'BCMath' => '*',
            'Curl' => '*',
        ],
        'pecl_classes' => [
            // 'ZipArchive' => '*' // required for auto update
        ],
        'is_writable' => [
            '../storage/framework/sessions',
            '../storage/framework/views',
            '../storage/framework/cache',
            'media_storage',
            '../config/__tech.php',
            '../.env',
        ]
    ]
];
