@include('home.php_scripts')
<!doctype html>
@if (isset($rtlLanguages) and array_key_exists($lang, $rtlLanguages))
    <html dir="rtl" lang="<?= $lang ?>">
@else
    <html lang="<?= $lang ?>">
@endif

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
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
    <title><?= $projectName ?></title>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">

    @if ($faviconUrl)
        <link rel="shortcut icon" type="image/x-icon" href="<?= $faviconUrl ?>">
    @endif

    <?= __yesset(['dist/libs/document-editor/document-editor.css', 'dist/css/vendorlibs-public.css', 'dist/css/vendorlibs-jquery-typeahead.css', 'dist/css/public-assets-app*.css', 'dist/libs/ckeditor/plugins/codesnippet/lib/highlight/styles/default.css'], true) ?>
    <script>
        window.appConfig = {
            'appBaseURL': "<?= url('') ?>/"
        };
    </script>

</head>

<body class="pt-4" onresize="changePanelHeight()">
    <!--  NAVIGATION BAR -->
    <nav class="navbar navbar-expand lw-nav-bg navbar-light fixed-top lw-main-navbar shadow">
        <button class="navbar-toggler p-0 border-0 lw-sidebar-toggler d-block d-lg-none" type="button">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand"
            href="{{ route('doc.view', [
                'projectSlug' => $projectSlug,
                'versionSlug?' => isset($versionSlug) ? $versionSlug : '',
                'articleSlug?' => isset($articleSlug) ? $articleSlug : '',
            ]) }}">

            @if (!__isEmpty($logoUrl))
                <img class="lw-project-logo" src="<?= $logoUrl ?>" alt="<?= $projectName ?>">
            @else
                <?= $projectName ?>
            @endif
        </a> <small class="lw-hidden-sm">
            <a class="btn btn-link btn-sm" href="<?= route('public.app') ?>"><span class="fas fa-home"></span>
                <?= __tr('Docs Home') ?>
            </a>
            @if (!__isEmpty(getConfigurationSettings('contact_email')))
                <a class="btn btn-link btn-sm" href="<?= route('public.contact.view') ?>"><span
                        class="fas fa-envelope"></span> <?= __tr('Contact Us') ?>
                </a>
            @endif
        </small>
        <div class="lw-project-version-language-menu">
            <ul class="navbar-nav my-2 my-md-0 lw-hidden-sm">
                @if (!__isEmpty($projectVersions) and !__isEmpty($allArticleContents))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false"> <?= $articleVersionSLug ?></a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown01">
                            @foreach ($projectVersions as $projectVersion)
                                <a class="dropdown-item "
                                    href="{{ route('doc.view', [
                                        'projectSlug' => $projectSlug,
                                        'versionSlug' => $projectVersion['slug'],
                                        'articleSlug' => $projectVersion['article_slug'],
                                    ]) }}?lang=<?= $lang ?>"><?= $projectVersion['version'] ?></a>
                            @endforeach
                        </div>
                    </li>
                @endif
                @if (isset($projectLanguages) and !__isEmpty($projectLanguages))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle " href id="langDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            @foreach ($projectLanguages as $projectLanguage => $projectLanguageName)
                                @if ($projectLanguage == $lang)
                                    <?= $projectLanguageName ?>
                                @endif
                            @endforeach
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="langDropdown">
                            @foreach ($projectLanguages as $projectLanguage => $projectLanguageName)
                                <a class=" dropdown-item"
                                    href="{{ route('doc.view', [
                                        'projectSlug' => $projectSlug,
                                        'versionSlug' => $versionSlug,
                                        'articleSlug' => $articleSlug,
                                    ]) }}?lang=<?= $projectLanguage ?>"><?= $projectLanguageName ?></a>
                            @endforeach
                        </div>
                    </li>
                @endif
                @if (isLoggedIn())
                    <li class="nav-item">
                        <a class="nav-link" href="<?= route('manage.app') ?>"><span class="fa fa-cog"></span>
                            <?= __tr('Manage') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= route('user.process_logout') ?>"><span
                                class="fa fa-sign-out-alt"></span> <?= __tr('Logout') ?>
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="<?= route('manage.app') ?>"><span class="fa fa-sign-in-alt"></span>
                            <?= __tr('Login') ?>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </nav>

    @if (isset($projectVersions) and
            !__isEmpty($projectVersions) and
            isset($allArticleContents) and
            !__isEmpty($allArticleContents))
        <!-- NAVIGATION BAR -->
        <div class="container-fluid lw-document">
            <div class="row">
                <!-- SIDEBAR SECTIONS -->
                <nav class="col-lg-3 navbar-light lw-document-sidebar shadow">
                    <div class="offset-lg-2">
                        <div class="lw-hidden-lg lw-sidebar-versions">
                            <ul class="navbar-nav my-2 my-md-0">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= route('public.app') ?>"><span
                                            class="fas fa-home"></span> <?= __tr('Docs Home') ?>
                                    </a>
                                </li>

                                @if (!__isEmpty(getConfigurationSettings('contact_email')))
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?= route('public.contact.view') ?>"><span
                                                class="fas fa-envelope"></span> <?= __tr('Contact Us') ?>
                                        </a>
                                    </li>
                                @endif
                                @if (!__isEmpty($projectVersions))
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false"> <?= $articleVersionSLug ?></a>
                                        <div class="dropdown-menu" aria-labelledby="dropdown01">
                                            @foreach ($projectVersions as $projectVersion)
                                                <a class="dropdown-item "
                                                    href="{{ route('doc.view', [
                                                        'projectSlug' => $projectSlug,
                                                        'versionSlug' => $projectVersion['slug'],
                                                        'articleSlug' => $projectVersion['article_slug'],
                                                    ]) }}?lang=<?= $lang ?>"><?= $projectVersion['version'] ?></a>
                                            @endforeach
                                        </div>
                                    </li>
                                @endif
                                @if (!__isEmpty($projectLanguages))
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle " href id="langDropdown" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            @foreach ($projectLanguages as $projectLanguage => $projectLanguageName)
                                                @if ($projectLanguage == $lang)
                                                    <?= $projectLanguageName ?>
                                                @endif
                                            @endforeach
                                        </a>

                                        <div class="dropdown-menu" aria-labelledby="langDropdown">
                                            @foreach ($projectLanguages as $projectLanguage => $projectLanguageName)
                                                <a class=" dropdown-item"
                                                    href="{{ route('doc.view', [
                                                        'projectSlug' => $projectSlug,
                                                        'versionSlug' => $versionSlug,
                                                        'articleSlug' => $articleSlug,
                                                    ]) }}?lang=<?= $projectLanguage ?>"><?= $projectLanguageName ?></a>
                                            @endforeach
                                        </div>
                                    </li>
                                @endif
                                @if (!isLoggedIn())
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?= route('manage.app') ?>"><span
                                                class="fa fa-sign-in-alt"></span> <?= __tr('Login') ?>
                                        </a>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?= route('manage.app') ?>"><span
                                                class="fa fa-cog"></span> <?= __tr('Manage') ?>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?= route('user.process_logout') ?>"><span
                                                class="fa fa-sign-out-alt"></span> <?= __tr('Logout') ?>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                            <hr>
                        </div>
                        <div class="lw-article-search-container mb-3">
                            <form id="form-search_articles" class="form-inline mr-auto d-block"
                                name="form-search_articles">
                                <div class="typeahead__container">
                                    <div class="typeahead__field">
                                        <div class="typeahead__query">
                                            <input class="lw-search_articles form-control" id="lwSearchArticles"
                                                name="search_articles[query]" type="search"
                                                placeholder="Search.... " autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="lw-side-links">
                            <h4><?= __tr('Index') ?></h4>
                            @if (!__isEmpty($allArticleContents))
                                <?= buildSidebarContents($allArticleContents, $projectSlug, $versionSlug, $lang, $articleSlug) ?>
                            @endif
                        </div>
                    </div>
                </nav>
                <!-- /SIDEBAR SECTIONS -->
                <!-- CONTENT SECTIONS -->
                <main role="main" class="col-lg-9 offset-lg-3 lw-document-body">
                    <div class="lw-cover-page">
                        @if (!__isEmpty($logoUrl))
                            <img class="lw-project-logo-print d-none d-print-block" src="<?= $logoUrl ?>"
                                alt="">
                        @endif
                        <h2 class="lw-project-title d-none d-print-block">
                            <?= $projectName ?>
                        </h2>
                        <!-- show info when project is inactive -->
                        {{-- get first key in $articleContenst --}}
                        @if (!__isEmpty($articleContents))
                            @php
                                $firstKey = array_key_first($articleContents);
                            @endphp
                        @endif
                        @if (
                            $projectStatus != 1 or
                                !__isEmpty($articleContents) and array_get($articleContents, $firstKey . '.article_status') == 2 or
                                array_get($articleContents, $firstKey . '.article_status') == 3)
                            <div class="alert alert-warning">
                                <?= __tr("This page of article may not published yet so it won't display to the publicly") ?>
                            </div>
                        @endif





                        <!-- /show info when project is inactive -->
                        @if (!__isEmpty($articleContents))
                            <h1 class="lw-article-title">
                                @foreach ($articleContents as $content)
                                    @if ($content['languages__id'] == $lang)
                                        <?= $content['title'] ?>
                                    @endif
                                @endforeach
                            </h1>
                        @endif

                        <hr class="mb-4">
                    </div>
                    @if (!__isEmpty($articleContents) and array_get($articleContents, $firstKey . '.article_type') == 2 and !Auth::user())
                        <div class="alert alert-warning" role="alert">
                            Sorry, this article is not available for public. Please login using your credentials to
                            view.
                        </div>
                    @else
                        <div class="lw-page-break"></div>
                        <div class="lw-table-of-content" style="display: none">
                            <nav role='navigation' class='table-of-contents'>
                                <h3><?= __tr('Table of Contents') ?></h3>
                                <hr>
                                <ul class="lw-toc-index">
                                    <div class='lw-index-loader-spinner'></div>
                                </ul>
                            </nav>
                        </div>
                        <div class="lw-page-break"></div>
                        <div id="lwIndexContent">
                            <!-- TABLE OF CONTENT RESULT -->
                            @if (!__isEmpty($articleContents))
                                <?= strip_selected_tags(buildArticleContent($articleContents, $lang), ['html', 'body']) ?>
                            @else
                                <h5 class="lw-document-content-empty-msg">
                                    <?= __tr('No content available') ?>
                                </h5>
                            @endif
                            <!-- / TABLE OF CONTENT RESULT -->
                        </div>
                    @endif
                </main>
                <!-- /CONTENT SECTIONS -->

            </div>

        </div>

        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <?= __yesset(['dist/js/vendorlibs-public.js', 'dist/js/vendorlibs-jquery-typeahead.js', 'dist/libs/ckeditor/plugins/codesnippet/lib/highlight/highlight.pack.js'], true) ?>
        @include('home.js_scripts')
        @if (isset($showPrintView) and $showPrintView == true)
            <script>
                window.print();
            </script>
        @endif
        <script>
            hljs.initHighlightingOnLoad();
        </script>
        <script type="text/javascript">
            function changePanelHeight() {
                var docsPanelHeight = window.innerHeight,
                    $navbarTogglerIcon = $('.lw-sidebar-toggler .navbar-toggler-icon');
                if (docsPanelHeight == 50) {
                    $navbarTogglerIcon.css('display', 'none');
                } else if (docsPanelHeight == 500) {
                    $navbarTogglerIcon.css('display', 'block');
                }
            }
        </script>
    @else
        <div class="container-fluid lw-document">
            @if (__isEmpty($projectVersions))
                <div class="alert alert-info">
                    <?= __tr('Project version not available.') ?>
                </div>
            @elseif(__isEmpty($allArticleContents))
                <div class="alert alert-info">
                    <?= __tr('Project article not available.') ?>
                </div>
            @endif
        </div>
    @endif
</body>

</html>
