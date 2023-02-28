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
 if(!file_exists('./../install.php')) {
    header("HTTP/1.0 404 Not Found");
    die();
}
error_reporting(E_ALL);
ini_set("display_errors", 1); 
header("Location: ./process/index.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Installation Wizard</title>
</head>
<body style="margin:0">
Please wait while we are redirecting you to installation
</body>
</html>