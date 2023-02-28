<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="user-scalable=1.0,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="description" property="og:description" content="@yield('description')">
<meta name="keywordDescription" property="og:keywordDescription" content="@yield('keywordDescription')">
<meta name="keywordName" property="og:keywordName" content="@yield('keywordName')">
<meta name="keyword" content="@yield('keyword')">
<meta name="title" content="@yield('page-title')">
<meta name="store" content="<?= getConfigurationSettings('name') ?>">

<script>
    window.appConfig = {
        'appBaseURL' : "<?= url('') ?>/"
    };
</script>

<?= __yesset([
    'dist/css/vendorlibs-public.css',
    'dist/css/public-assets-app*.css'
], true) ?>

<link rel="shortcut icon" type="image/x-icon" href="<?= getConfigurationSettings('favicon_image_url') ?>">

