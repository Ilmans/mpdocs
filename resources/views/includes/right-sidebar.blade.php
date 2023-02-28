
    <div class="row mb-2">
        <div class="col-lg-12 ">
            
            <div class="card mb-3">
                <div class="card-body lw-support-container ">
                    <h5 class="card-title">Need more Support?</h5>
                    <p class="card-text">If you cannot find an answer in the <?= e( getConfigurationSettings('name') ) ?>, you can <a href="<?= route('public.contact.view') ?>">contact us</a> for
                further help.</p>
                </div>
            </div>
        </div>
    </div>


    <div class="row margin-top-20">
        <div class="col-lg-12">
            <div class="lw-fb-heading-small">Latest Articles</div>
            <hr class="style-three">
            <div class="fat-content-small pl-1">
                <ul>
                    @if(!__isEmpty($viewComposer['latest_articles']))

                        @foreach($viewComposer['latest_articles'] as $ar)
                        <li> 
                            <a href="<?= route('public.article.read.details_view', [
                                'articleContentUid' => $ar['content_uid']
                            ]) ?>"><i class="fa fa-pencil-square-o"></i> <?= str_limit($ar['title'], 35) ?></a> 
                        </li>
                        @endforeach

                    @else   
                        There are no latest articles.
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!-- POPULAR TAGS (SHOW MAX 20 TAGS) -->
    <div class="row mt-2">
        <div class="col-lg-12">
            <div class="lw-fb-heading-small">Tags</div>
            <hr class="style-three">
            <div class="fat-content-tags pl-3"> 
                @include('tag.public.list')
            </div>
        </div>
    </div>
    <!-- END POPULAR TAGS (SHOW MAX 20 TAGS) -->
