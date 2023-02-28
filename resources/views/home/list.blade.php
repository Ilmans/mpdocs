<?php
/*
    *  Component  : Home
    *  View       : Home Controller
    *  Engine     : HomeEngine.php
    *  File       : home/list.blade.php  
    ----------------------------------------------------------------------------- */
?>
<div class="row card-deck pt-5 m-auto">
    @if(isset($projects) && !__isEmpty($projects))
    @foreach($projects as $project)
    @foreach($project as $proj)
    @if(!__isEmpty($proj['primary_version']))
    <div class="col-lg-3 col-md-4 col-sm-6 lw-project-item">
        <a href="{{ route('doc.view', [
                                            'projectSlug' => $proj['slug'],
                                            'versionSlug' => $proj['primary_version']['slug'],
                                        ]) }}">
            <div class="card lw-project-list-card text-center mb-4">
                <div class="card-body">
                    @if($proj['is_private'])
                    <i class="fa fa-user-lock float-right fa-2x text-secondary" title="<?= __tr('Private') ?>"></i>
                    @endif
                    <h5 class="card-title"><?= $proj['name'] ?> </h5>
                    @if($proj['logo_url'])
                    <img src="<?= $proj['logo_url'] ?? '' ?>" class="card-img-top p-2" alt="<?= $proj['name'] ?>">
                    @else
                    <i class="fa fa-6x text-primary fa-book"></i>
                    @endif
                    <p class="card-text"><?= $proj['description'] ?? '' ?></p>

                </div>
                <div class="card-footer bg-white">
                    <a class="btn btn-link text-secondary  mt-1 lw-details-link-btn" href="{{ route('doc.view', [ 'projectSlug' => $proj['slug'],
                                            'versionSlug' => $proj['primary_version']['slug'],
                                        ]) }}"> <span class="fas fa-eye"></span> <?= __tr('View Docs') ?></a>
                </div>
            </div>
        </a>
    </div>
    @endif
    @endforeach
    @endforeach
    @else
    <div class="alert alert-info">
        <?= __tr('There are no information') ?>
    </div>
    @endif
</div>