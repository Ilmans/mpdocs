<div>
    
    

    <!-- ARTICLE  -->
    @if(!__isEmpty($article['languages']) or isset($article['prev_article_url']))
    <nav class="navbar navbar-expand-lg navbar-light bg-light rounded">
    
        <div class="collapse navbar-collapse">

            <ul class="navbar-nav mr-auto"> 
                @if(isset($article['prev_article_url']))
                    <li class="nav-item active">
                        <a class="nav-link"  href="<?= $article['prev_article_url'] ?>"><span class="fas fa-angle-left"></span> Prev Article</a>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
    @endif

    <!-- ARTICLE DETAILS -->
    <div class="card">

        <div class="card-body">

            
            <!-- HEADING -->
		    <div class="lw-page-heading">
		        {{ $article['title'] }}

		        <div class=" float-right">
	                @if($article['languages'])
	                    <!-- language dropdown -->
	                    <div class=" ">
	                        <button type="button" class="btn btn-default dropdown-toggle float-right" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                        	<span class="fas fa-language"></span> Available in
	                        </button>
	                        <div class="dropdown-menu">
		                        @foreach($article['languages'] as $lang)
		                       		<a class="dropdown-item" href="<?= $lang['content_url'] ?>"><?= $lang['content_language'] ?></a>
		                        @endforeach
	                        </div>
	                    </div>
	                    <!-- / language dropdown -->
	                @endif
	            </div>
		    </div>
		    <!-- HEADING -->

            <h6 class="card-subtitle mb-2 text-muted">

                @if(!__isEmpty($article['published_at']))
                    <span class="p-1"><strong>Published :</strong> <?= formatDateTime($article['published_at'], 'l jS F Y ') ?></span> 
                @endif

            </h6>

            
            <p class="card-text">
                <?= $article['description'] ?>
                @section('description', strip_tags(getUniqueWords($article['title'], null)))
                @section('keyword', strip_tags(getUniqueWords($article['title'], null, ',')))
                @section('keywordDescription', strip_tags(getUniqueWords($article['title'], null, ',')))
                @section('page-title', 'Article Details')
            </p>
        </div>
    </div>
    <!-- END ARTICLE DETAILS -->
</div>