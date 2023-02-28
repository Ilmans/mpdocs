<?php
// WARNING!! DO NOT CHANGE HERE
$lwSystemConfig = [
    'product_name' => 'Docsyard',
    'product_uid' => '4f0615ab-cb5e-4caf-a309-79eeecbbe238',
    'your_email' => '',
    'registration_id' => '',
    'name' => 'MPDocs',
    "version" => "3.0.0",
    'app_update_url' => env('APP_UPDATE_URL', 'https://product-central.livelyworks.net')
];
$versionInfo = [];

if (file_exists(config_path('.lw_registration.php'))) {
    $versionInfo = require config_path('.lw_registration.php');
}

return array_merge($lwSystemConfig, $versionInfo);
