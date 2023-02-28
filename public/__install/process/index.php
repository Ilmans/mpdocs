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
error_reporting(E_ALL);
ini_set("display_errors", 1); 
require 'vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Installation Wizard</title>
    <link rel="stylesheet" href="static-assets/libs/bootstrap/css/bootstrap.min.css" >
    <link rel="stylesheet" href="static-assets/libs/mdi/css/materialdesignicons.min.css" >
    <link rel="stylesheet" href="static-assets/css/styles.css" >
    <script src="static-assets/libs/jquery.min.js"></script>
    <script src="static-assets/libs/bootstrap/js/bootstrap.min.js" ></script>
    <script src="static-assets/libs/jquery-validation/dist/jquery.validate.min.js"></script>
</head>
<body>
<div class="lw-container">
<h2 class="lw-header">Installation Wizard</h2>
<div class="lw-container-box">
    <?php
    $startSetupVerification = new LivelyWorks\Installation\Verification();
    $startSetupVerification->verify();
    ?>
</div>
</div>
 <script>
    $(document).ready(function() {
        $('#nextBtnDb').on('click', function (e) {
            e.preventDefault();
            $.post( "./user-inputs.php", {
                'requirements_fulfilled' : true
            }, function( data ) {
                $( ".lw-container-box" ).html( data );
                
                var isProcessing = false;
            $("#dbInfoForm").validate();
            
    $('#dbInfoForm').on('submit', function (e) {
        e.preventDefault();

        if(isProcessing) {
            return false;
        }

        isProcessing = true;
        var $thisButton = $('#dbInfoSubmit');
        $thisButton.attr('disabled', 'disabled');

        if(!$("#dbInfoForm").valid()) {
            $thisButton.removeAttr('disabled');
            return false;
        }

        $.post( "./user-input-process.php", $( "#dbInfoForm" ).serialize(),
        function( data ) {
            var pageData = data;
            try {
               data = JSON.parse(data);
               if(!data.onPage) {
                   $( "#dynamicPageContent" ).html( data.page );
                   $('#dynamicPageContentModal').modal().on('hidden.bs.modal', function (e) {
                    isProcessing = false;
                    });
               } else {
                   $('#otherDynamicPageContent').html( data.onPage );
               }

                if(data.isDatabaseConnectionSucceed == true) {
                    $("#dbInfoForm").hide();
                } else {
                    $thisButton.removeAttr('disabled');
                    isProcessing = false;
                }

            } catch(err) {
                $( "#dynamicPageContent" ).html( data ); 
                $('#dynamicPageContentModal').modal().on('hidden.bs.modal', function (e) {
                    $thisButton.removeAttr('disabled');
                    isProcessing = false;
                });
            }
        });
    });

            });
        });
    });
 </script>
 <script>
    
</script>
</body>
</html>