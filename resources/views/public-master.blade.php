<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(getConfigurationSettings('name')) ?></title>
    <!-- LOADING STYLESHEETS -->
    @include('includes.public-head-content')
    <?= __yesset(['dist/css/vendorlibs-jquery-typeahead.css'], true) ?>

    <style>
        .lw-main-navbar {
  background-color: #100e0e;
}

.top-horizontal-menu .nav-item .nav-link {
  font-size: 18px;
  padding: 10px;
}

.top-horizontal-menu .navbar-brand img {
  height: 50px;
}

.top-horizontal-menu .navbar-brand {
  font-size: 20px;
  margin-right: 10px;
}

.top-horizontal-menu .nav-item .nav-link:hover {
  background-color: #F5F5F5;
}

.lw-main-page-container {
  margin-top: 80px;
  padding: 20px;
}

    </style>
</head>

<body class="d-flex flex-column h-100">

    <div class="container-fluid">
        <!-- NAVIGATION BAR -->
        <nav class="navbar fixed-top navbar-expand-lg navbar-light lw-main-navbar shadow bg-white top-horizontal-menu">
            <a class="navbar-brand" href="<?= route('public.app') ?>">
                <img src="<?= getConfigurationSettings('logo_image_url') ?>" alt="">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample09"
                aria-controls="navbarsExample09" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarsExample09">
                <ul class="navbar-nav ml-auto">
                    @if (!__isEmpty(getConfigurationSettings('contact_email')))
                        <li class="nav-item">
                            <a class="nav-link" href="<?= route('public.contact.view') ?>"><span
                                    class="fas fa-envelope"></span> <?= __tr('Contact Us') ?></a>
                        </li>
                    @endif
                    @if (!isLoggedIn())
                        <li class="nav-item">
                            <a class="nav-link" href="<?= route('manage.app') ?>"><span
                                    class="fa fa-sign-in-alt"></span> <?= __tr('Login') ?></a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="<?= route('manage.app') ?>"><span class="fa fa-cog"></span>
                                <?= __tr('Manage') ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= route('user.process_logout') ?>"><span
                                    class="fa fa-sign-out-alt"></span> <?= __tr('Logout') ?></a>
                        </li>
                    @endif
                </ul>
            </div>
        </nav>
        <!-- / NAVIGATION BAR -->

        <div class="lw-main-page-container">
            <!-- MAIN CONTAINER -->
            <main role="main lw-main-container" class="lw-main-container col-sm-12">
                @if (isset($pageRequested))
                    {!! $pageRequested !!}
                @endif
            </main>
            <!-- / MAIN CONTAINER -->
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="lw-footer ">
        <div>
            <span>
                <?= getConfigurationSettings('name') . ' - ' ?> &copy; <?= date('Y') ?>
                <?= getConfigurationSettings('footer_text') ?>

                @if (getConfigurationSettings('enable_credit_info') == true)
                    &nbsp;
                    <span>
                        <span> Powered by <strong>M Pedia <small><?= config('lwSystem.version') ?></small></strong> |

                        </span>
                    </span>
                @endif
            </span>
        </div>
    </footer>
    <!-- /FOOTER -->
    <!-- LOADING MAIN JAVASCRIPT -->
    <?= __yesset(['dist/js/vendorlibs-public.js'], true) ?>

    @include('includes.public-foot-content')
    <!-- END LOADING MAIN JAVASCRIPT -->


</body>

</html>
