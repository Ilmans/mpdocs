<?php
/**
 *  LivelyWorks EasyPHPInstall - PHP Application Installer
 *
 * @package    EasyPHPInstall - PHP Application Installer
 * @author     Vinod <livelyworks@gmail.com>
 * @copyright  2019 LivelyWorks. (http://livelyworks.net)
 * @link       http://livelyworks.net
 * @since      Version 1.0
 * @deprecated NA
 */
// Prevent if the installation successful
if(!file_exists('./../../install.php')) {
    header("HTTP/1.0 404 Not Found");
    die();
}

if(!$_POST['requirements_fulfilled'] and ($_POST['requirements_fulfilled'] != true)) {
    header("Location: index.php");
}

error_reporting(E_ALL & ~E_WARNING);
ini_set("display_errors", 1);  
require 'vendor/autoload.php';

$startSetupVerification = new LivelyWorks\Installation\Verification();
echo json_encode($startSetupVerification->verifyUserInputs($_POST));