<script>
    $(function() {
        /* Start Typeahead Plugins JS */
        $.typeahead({
            input: '#lwSearchArticles',
            minLength: 1,
            order: "asc",
            dynamic: true,
            delay: 500,
            hint: true,
            emptyTemplate: "no result found",
            source: {
                searchData: {
                    display: ["title", "description"],
                    ajax: function(query) {

                        return {
                            type: "GET",
                            path: "searchData",
                            url: '{{ route("public.search.read") }}?search_term=' + query + '&lang={{$lang}}' + '&ver={{$versionSlug}}' + '&project={{$projectSlug}}',
                            callback: {
                                done: function(responseData) {
                                    return responseData.data;
                                }
                            }
                        }
                    },
                }
            },
            template: function(query, item) {

                //item.href = item.searchContentRoute + '#' + item.slug + '-' + "{{$lang}}";
                item.href = item.searchContentRoute + '?lang=' + "{{$lang}}" + '#' + item.slug;
                // console.log(item.searchContentRoute + '?lang='+ "{{$lang}}" +'#' + item.slug + '-' + "{{$lang}}");
                return item.title;

            },
            filter: function(item, displayKey) {
                return displayKey;
            },
            callback: {
                onClick: function(node, a, item, event) {

                    event.stopPropagation();

                    // $('.typeahead__cancel-button').triggerHandler('click', true);
                    window.location = item.href;
                },
                onSendRequest: function(node, query) {
                    //console.log('request is sent')
                },
                onReceiveRequest: function(node, query) {
                    // console.log('request is received')
                }
            },
            debug: true
        });
        /* End Typeahead Plugins JS */

        /* Start Back top */
        $('main').append('<div id="lwBackToTop" class="btn btn-danger btn-sm"><span class="fa fa-arrow-up"></span> Back to Top</div>');
        $(window).scroll(function() {
            if ($(this).scrollTop() != 0) {
                $('#lwBackToTop').fadeIn();
            } else {
                $('#lwBackToTop').fadeOut();
            }
        });

        $('#lwBackToTop').click(function() {
            $("html, body").animate({
                scrollTop: 0
            }, 800);
            return false;
        });
        /* End Back top */

        $('.lw-sidebar-toggler').click(function() {
            var $html = $('html');
            if ($html.hasClass('lw-sidebar-opened')) {
                $html.removeClass('lw-sidebar-opened');
            } else {
                $html.addClass('lw-sidebar-opened');
            }

            return true;
        });

        $('.lw-document').click(function(e) {
            var $html = $('html'),
                $this = $(e.target);
            if ($html.hasClass('lw-sidebar-opened') &&
                (!$this.hasClass('lw-search_articles')) &&
                (!$this.parents().hasClass('dropdown'))) {
                $html.removeClass('lw-sidebar-opened');
            }
            return true;
        });

        function makeid(length) {
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        }

        var articleTableOfContent = '';

        var tocNewLine,
            $contentElement,
            contentTitle,
            contentLink,
            dynamicClass,
            isFirstDone = false;

        var childLevel = 0,
            paddingRate = 12;

        $("#lwIndexContent h1, #lwIndexContent h2, #lwIndexContent h3, .lw-document-sub-heading").each(function() {

            $contentElement = $(this);
            if (!isFirstDone) {
                isFirstDone = true;
                return true;
            }

            contentTitle = $contentElement.text();
            contentLink = makeid(contentTitle.length); //slugText(contentTitle);
            $contentElement.attr('id', contentLink);
            var currentTag = $contentElement.prop("tagName");
            if (currentTag == 'H1') {
                dynamicClass = 'lw-h1-text';
            } else if (currentTag == 'H2') {
                dynamicClass = 'lw-h2-text';
            } else if (currentTag == 'H3') {
                dynamicClass = 'lw-h3-text';
            } else {
                dynamicClass = 'lw-sub-page-text lw-article-link';
            }

            tocNewLine = '';

            if (contentTitle.trim() !== '') {

                if ($contentElement.hasClass('lw-document-sub-heading')) {
                    childLevel = $contentElement.data('child-level') - 1;

                    tocNewLine += "<li class='" + dynamicClass + "' style='margin-left:" + (childLevel * paddingRate) + "px'>" +
                        "<a class='lw-toc-link' href='#" + contentLink + "'>" +
                        contentTitle + "</a></li>";

                } else {

                    tocNewLine += "<li class='" + dynamicClass + "' style='margin-left:" + (childLevel * paddingRate + paddingRate) + "px'>" +
                        "<a class='lw-toc-link' href='#" + contentLink + "'>" +
                        contentTitle + "</a></li>";
                }

                articleTableOfContent += tocNewLine;
            }
        });

        $(".lw-table-of-content .lw-toc-index").prepend(articleTableOfContent);
        $(".lw-menu-parent-<?= $articleSlug ?>>ul").append(articleTableOfContent);
        $('.lw-index-loader-spinner').hide();
        if ($('.lw-toc-index').children().length > 1) {
            $(".lw-table-of-content").fadeIn();
        }


        $('body .lw-table-of-content .lw-toc-index,body .lw-side-links .lw-menu-parent-item').on('click', "a.lw-toc-link", function(e) {
            e.preventDefault();
            var position = $($(e.target).attr("href")).offset().top;
            $('.lw-toc-link').removeClass('lw-active-article');
            $(e.target).addClass('lw-active-article');
            $('html, body').animate({
                scrollTop: position - ($('.lw-main-navbar').height() + 20)
            } /* , speed */ );
        });

        if (window.innerHeight == 50) {
            $('.lw-sidebar-toggler .navbar-toggler-icon').css('display', 'none');
        }
    });
</script>