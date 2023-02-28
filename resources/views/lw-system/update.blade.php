<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= config('lwSystem.app_name') ?> - Installation Updater</title>
    <link rel="stylesheet" href="<?= __yesset('__install/process/static-assets/libs/bootstrap/css/bootstrap.min.css') ?>" >
    <link rel="stylesheet" href="<?= __yesset('__install/process/static-assets/libs/mdi/css/materialdesignicons.min.css') ?>" >
    <link rel="stylesheet" href="<?= config('lwSystem.app_update_url') ?>/static-assets/client/styles.min.css?ver-1.0.0" >
    <script src="<?= __yesset('__install/process/static-assets/libs/jquery.min.js') ?>"></script>
    <script src="<?= __yesset('__install/process/static-assets/libs/bootstrap/js/bootstrap.min.js') ?>" ></script>
    <script src="<?= __yesset('__install/process/static-assets/libs/jquery-validation/dist/jquery.validate.min.js') ?>"></script>
</head>
<body>
<div class="lw-container">
<h2 class="lw-header"><?= config('lwSystem.name') ?> - Installation Updater</h2>
<div class="lw-container-box"></div>
</div>
 <script>
             // Get third party Url from config and customer uid from store setting table
    var appUrl = "<?= config('lwSystem.app_update_url') ?>/api/app-update",
        registrationId = "<?= config('lwSystem.registration_id') ?>",
        version = "<?= config('lwSystem.version') ?>",
        productUid = "<?= config('lwSystem.product_uid') ?>",
        csrfToken = "<?= csrf_token() ?>",
        localRegistrationRoute = "<?= route('installation.version.create.registration') ?>",
        localDownloadRoute = "<?= route('installation.version.update.download') ?>",
        localPerformUpdateRoute = "<?= route('installation.version.update.perform') ?>";
        console.log(appUrl);
 </script>
 <script src="<?= config('lwSystem.app_update_url') ?>/static-assets/client/update-client.min.js?ver-1.0.0" ></script>
</body>
</html>