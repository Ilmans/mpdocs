    <!--[if lte IE 9]>
        <script src="//cdnjs.cloudflare.com/ajax/libs/Base64/0.3.0/base64.min.js"></script>
    <![endif]-->

    <!-- required for sweet alert shim -->
    <!--[if lte IE 11]>
    <script src="https://cdn.jsdelivr.net/npm/promise-polyfill@7.1.0/dist/promise.min.js"></script>
    <![endif]-->

    <!--[if gt IE 11]>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
	<![endif]-->

    @if(getConfigurationSettings('enable_login_attempt'))
    <script src="https://www.google.com/recaptcha/api.js"></script>
    @endif

    @stack('vendorScripts')
    <?= __yesset([
        'dist/libs/ckeditor/ckeditor.js',
        'dist/js/vendorlibs-jquery-ui.js',
        'dist/js/vendorlibs-angular.js',
        'dist/js/vendorlibs-datatable.js',
        'dist/js/vendorlibs-ngdialog.js',
        'dist/js/vendorlibs-selectize.js',
        'dist/js/vendorlibs-switchery.js',
        'dist/js/vendorlibs-other-common.js',
        'dist/js/vendorlibs-manage.js',

    ], true) ?>
    <?= __yesset('dist/js/common-files*.js', true) ?>

    @stack('appScripts')
    <?= __yesset([
        'dist/js/ngware-app*.js',
        'dist/js/app-support-app*.js',
    ], true) ?>
    <!-- container -->
    <script type="text/javascript">
        $(document).ready(function() {

            $('body').on('click', '.lw-prevent-default-action', function(e) {
                e.preventDefault();
            });

            $('html').removeClass('lw-has-disabled-block');

            $('html body').on('click', '.lw-show-process-action', function(e) {

                setTimeout(function() {

                    $('html').addClass('lw-has-disabled-block');

                    $('.lw-disabling-block').addClass('lw-disabled-block lw-has-processing-window');

                }, 3000);

            });

            $('.hide-till-load').removeClass('hide-till-load');
            $('.lw-show-till-loading').removeClass('lw-show-till-loading');
            $('.lw-main-loader').hide();


            $(window).scroll(function() {
                if ($(this).scrollTop() != 0) {
                    $('body').addClass('lw-scrolled-window')
                } else {
                    $('body').removeClass('lw-scrolled-window')
                }
            });

        });
    </script>