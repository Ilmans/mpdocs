<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e( getConfigurationSettings('name') ) ?> : <?= __tr('Manage') ?></title>
    @include('includes.head-content')

    <?= __yesset([
            'dist/css/vendorlibs-manage.css',
            'dist/css/application*.css'
        ], true) ?>
</head>

<body class="lw-has-disabled-block">

    <!-- Disabled loading block -->
    <div class="lw-disabling-block">
        <div class="lw-processing-window lw-hidden">
            <div class="loader"><?=  __tr('Loading...')  ?></div>
            <div><?= __tr( 'Please wait while we are processing your request...' ) ?></div>
        </div>
    </div>

    <!--/ Disabled loading block -->
    <div class="lw-main-loader lw-show-till-loading">
        <div class="loader"><?=  __tr('Loading...')  ?></div>
        <div><?=  __tr('Please wait a while, System is getting ready for you!!')  ?></div>
    </div>

    <div ng-app="ManageApp" ng-controller="ManageController as manageCtrl" ng-csp ng-cloak ng-strict-di>

        <!-- Show when javascript desable in browser -->
        <noscript>
            <style>
                .nojs-msg {
                    width: 50%;
                    margin: 20px auto
                }
            </style>
            <div class="custom-noscript">
                <div class="bs-callout bs-callout-danger nojs-msg">
                    <h4><?= __tr('Oh dear... we are sorry') ?></h4>
                    <em><strong><?= __tr('Javascript') ?></strong>
                        <?= __tr('is disabled in your browser, To use this application please enable javascript &amp; reload page again.') ?></em>
                </div>
            </div>
        </noscript>

        <!-- / Show when javascript disable in browser -->
        <div class="lw-smart-menu-container">

            <nav class="navbar fixed-top navbar-expand-lg navbar-light lw-main-navbar shadow">

                <div id="lwchangeHeaderColor">
                    <a class="lw-item-link" ng-show="manageCtrl.isLoggedIn()" ui-sref="project"><img src="<?=  getConfigurationSettings('logo_image_url')  ?>" alt=""></a>
                    <a class="lw-item-link" ng-show="!manageCtrl.isLoggedIn()" ui-sref="login"><img class="logo-image" src="<?=  getConfigurationSettings('logo_image_url')  ?>" alt=""></a>
                </div>

                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerMain"
                    aria-controls="navbarTogglerMain" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarTogglerMain">

                    <ul class="navbar-nav mr-auto">
                        <li></li>
                        <li class="nav-item">
                            <a class="nav-link" title="<?= __tr('Public Side') ?>" target="_blank" href="{{ route('public.app') }}"><i class="fas fa-external-link-alt"></i> </a>
                        </li>
                    </ul>

                    <ul class="navbar-nav mt-2 mt-lg-0">
                        <li ui-sref-active="active" class="nav-item" ng-show="!manageCtrl.isLoggedIn()">
                            <a class="nav-link" title="<?= __tr('Login') ?>" ui-sref="login"><i class="fas fa-sign-in-alt"></i> <?= __tr('Login') ?></a>
                        </li>
                        <li ui-sref-active="active" ng-if="canAccess('manage.project.read.list')" class="nav-item" ng-show="manageCtrl.isLoggedIn()">
                            <a class="nav-link" title="<?=  __tr('Projects')  ?>" ui-sref="project"><i class="fas fa-th-large"></i> <?= __tr('Projects') ?></a>
                        </li>
                    </ul>

                    <ul class="navbar-nav" ng-show="manageCtrl.isLoggedIn()">
                        <li class="nav-item dropdown"
                            ng-show="canAccess('manage.configuration.process') || canAccess('manage.activity_log.read.list') || canAccess('manage.user.role_permission.read.list') || canAccess('manage.user.read.datatable.list') || canAccess('manage.language.read.list')">
                            <a class="nav-link dropdown-toggle" href="" id="navbarDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-cogs" aria-hidden="true"></i> <?= __tr('Manage') ?>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a ui-sref="configuration_general" class="dropdown-item" ui-sref-active="active"
                                    ng-show="canAccess('manage.configuration.process')"
                                    title="<?=  __tr('General Settings')  ?>"><i class="fas fa-cog"></i>
                                    <?=  __tr('General Settings')  ?></a>

                                <a class="dropdown-item" ui-sref-active="active"
                                    ng-show="canAccess('manage.activity_log.read.list')" ui-sref="activity_log"
                                    title="<?=  __tr('Activity Log')  ?>"><i class="fa fa-history"
                                        aria-hidden="true"></i> <?=  __tr('Activity Log')  ?></a>
                                
                                <div class="dropdown-divider"></div> 

                        		<a class="dropdown-item" ui-sref-active="active"
                                    ng-show="canAccess('manage.tag.read.list')" ui-sref="tag"
                                    title="<?=  __tr('tags')  ?>">
                                    <i class="fa fa-tags" aria-hidden="true"></i>
                                    <?=  __tr('tags')  ?>
                                </a>

                                <a class="dropdown-item" ui-sref-active="active"
                                    ng-show="canAccess('manage.language.read.list')" ui-sref="language"
                                    title="<?=  __tr('Languages')  ?>">
                                    <i class="fa fa-language" aria-hidden="true"></i>
                                    <?=  __tr('Languages')  ?>
                                </a>

                                <a class="dropdown-item" ui-sref-active="active"
                                    ng-show="canAccess('manage.user.read.datatable.list')" ui-sref="users"
                                    title="<?=  __tr('Users')  ?>"><i class="fa fa-users" aria-hidden="true"></i>
                                    <?= __tr('Users') ?></a>

                                <a class="dropdown-item" ui-sref-active="active"
                                    ng-show="canAccess('manage.user.role_permission.read.list')"
                                    ui-sref="role_permission" title="<?=  __tr('User Roles')  ?>"><i
                                        class="fa fa-users"></i> <?=  __tr('User Roles')  ?></a>
                            </div>
                        </li>
                        <li class="nav-item dropdown" ng-show="manageCtrl.isLoggedIn()">
                            <a class="nav-link dropdown-toggle" href="" id="navbarDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-user" aria-hidden="true"></i>
                                <span ng-bind="manageCtrl.auth_info.profile.full_name"
                                    ng-if="!manageCtrl.userUpdateData"></span>
                                <span ng-bind="manageCtrl.userUpdateData" ng-if="manageCtrl.userUpdateData"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" ui-sref-active="active" ui-sref="profile"
                                    title="<?=  __tr('Profile')  ?>"><i
                                    class="fa fa-user-circle" aria-hidden="true"></i> <?=  __tr('Profile')  ?></a>
                                <a class="dropdown-item" ui-sref-active="active" ui-sref="changePassword"
                                    title="<?= __tr('Change Password') ?>"><i
                                    class="fa fa-key" aria-hidden="true"></i> <?= __tr('Change Password') ?></a>

                                <a class="dropdown-item" ui-sref-active="active"
                                    ng-if="manageCtrl.restrict_user_email_update == false && manageCtrl.auth_info.designation != 1"
                                    ui-sref="changeEmail"
                                    title="<?= __tr('Update Email') ?>"><i
                                    class="fa fa-pen-square" aria-hidden="true"></i> <?= __tr('Update Email') ?></a>

                                <a class="dropdown-item" ui-sref-active="active"
                                    ng-if="manageCtrl.auth_info.designation == 1" ui-sref="changeEmail"
                                    title="<?= __tr('Update Email') ?>"><i
                                    class="fa fa-pen-square" aria-hidden="true"></i> <?= __tr('Update Email') ?></a>

                                <a class="dropdown-item" href ng-click="manageCtrl.logoutUser()"
                                    title="<?= __tr('Logout') ?>"><i
                                        class="fa fa-sign-out-alt" aria-hidden="true"></i> <?= __tr('Logout') ?> </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
        <!-- Navbar -->
        
        <!-- main container -->
        <div class="container-fluid ui-view-container hide-till-load">
            <div class="lw-component-content mt-3 p-4" ui-view autoscroll="false"></div>
            <!--/ child page app-->
        </div>
        <!-- / main container -->

        @push("vendorScripts")
        <?= __yesset([
			'dist/js/vendorlibs-first.js',
			'dist/js/vendor-second.js',
        ], true) ?>
        @endpush

        @push("appScripts")
        <?= __yesset('dist/js/manage-app.*.js', true) ?>
        @endpush

        <!-- settings update reload button template -->
        <script type="text/ng-template" id="lw-settings-update-reload-button-template.html">
                <input type="hidden" id="lwReloadBtnText" data-message="<i class='fa fa-refresh' aria-hidden='true'></i> <?= __tr("Reload") ?>">
        </script>
        <!-- /settings update reload button template -->

        <footer class="lw-footer hide-till-load container-fluid">
            <span class="text-muted">
                <span class="pull-left">
                    <?= getConfigurationSettings('name'). ' - ' ?> &copy; <?= date("Y") ?>
                    <?= getConfigurationSettings('footer_text') ?>
                </span>
                @if(getConfigurationSettings('enable_credit_info') == true)
                <span class="pull-right">
                    <span> Powered by <strong>Docsyard <small><?= config('lwSystem.version') ?></small></strong> |
                        Designed &amp; Developed by <a href="http://livelyworks.net"
                            title="Design &amp; Developed by LivelyWorks" target="_blank">LivelyWorks</a></span>
                </span>
                @endif
            </span>
        </footer>

        @include('includes.javascript-content')
        @include('includes.form-template')
    </div>
    @include('includes.foot-content')
</body>

</html>