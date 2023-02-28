
<div>

    <!-- HEADING -->
    <div class="lw-page-heading">
        <?= $project['name'] ?> Project
    </div>
    <!-- HEADING -->

    <!-- PROJECT DETAILS  -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?= $project['name'] ?></h5>
            <p class="card-text"> <?= $project['short_description'] ?>
            @section('description', str_limit(strip_tags($project['short_description']), 50))</p>
            <p>
                <h6 class="card-subtitle mb-2 text-muted">Related Articles</h6>
                <hr>
                    <!-- ARTICLES -->
                    @foreach($articles as $article)
                        <p>
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="<?= route('public.article.read.details_view', [
                                            'articleContentUid' => $article['artilce_content_uid']
                                        ]) ?>"><i class="fa fa-pencil-square-o"></i> <?= $article['title'] ?></a>
                                    </h5>
                                </div>
                            </div>
                        </p>
                    @endforeach
                    <!-- END ARTICLES -->
                </p>
            </p>
        </div>
    </div>
    <!-- END PROJECT DETAILS --> 
</div>