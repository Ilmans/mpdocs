<!DOCTYPE html>
<html lang="en">
    
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e( getConfigurationSettings('name') ) ?></title>
        <!-- LOADING STYLESHEETS -->
        @include('includes.public-head-content')
    </head>
    
    <body class="d-flex flex-column h-100 mt-4">

        <div class="container">
            <!-- MAIN CONTAINER -->
            <main role="main lw-main-container">
                @if(isset($pageRequested))
                    <?php echo $pageRequested ; ?>
                @endif
            </main>
            <!-- / MAIN CONTAINER -->
        </div>

        <!-- LOADING MAIN JAVASCRIPT -->
        <?= __yesset(['dist/js/vendorlibs-public.js'], true) ?>
          
<!-- container -->
<script type="text/javascript">
    /* COMMENTS LAST BORDER REMOVAL */

    $(function() {

        var comments = $('div.article-comment-top');

        var last = comments.last();

        last.css({ borderBottom : 'none' });

        $('body').on('click', '.pagination a', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');  
            getData(url);
            window.history.pushState("", "", url);
        });

        function getData(url) {

            $.ajax({
                url : url,
                type: "get",
                datatype: "html"
            }).done(function (data) {
                $('.lw-paginated-content-section').html(data);  
            }).fail(function () {
                // alert('Page could not be loaded.');
            });
        }
    });

</script>


@stack('appScripts')

        <!-- END LOADING MAIN JAVASCRIPT -->
        

    </body>

</html>