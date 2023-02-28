<?php
/*
*  Component  : Article
*  View       : Article Controller
*  Engine     : ArticleEngine  
*  File       : add.blade.php  
*  Controller : ArticleAddController
----------------------------------------------------------------------------- */
?>
<div>
    <div class="lw-section-heading-block">

        <!-- Parent heading -->
        <ul class="lw-breadcrumb breadcrumb">
            <li class="breadcrumb-item">
            <a class="breadcrumb-item" ui-sref="project"><?= __tr('Manage Projects') ?>
            </a>
            </li>
            <li class="breadcrumb-item">
                 <a ui-sref="project" ng-bind="ArticleAddCtrl.projectName"></a>
            </li>
            <li class="breadcrumb-item"> <a ui-sref="project_versions({ 'projectIdOrUid' : ArticleAddCtrl.projectUid })" ng-bind="ArticleAddCtrl.version_info.version"></a></li>
            <li class="breadcrumb-item"><a ui-sref="project_articles({ 'projectUid' : ArticleAddCtrl.projectUid, 'versionUid' : ArticleAddCtrl.version_info._uid })">Articles</a></li>
        </ul>
        <!-- /Parent heading -->

        <!-- main heading -->
        <h3 class="lw-section-heading">
            Add New Article
        </h3>
        <!-- /main heading -->

        <div class="lw-section-right-content">

            <a ui-sref="project_articles({ 'projectUid' : ArticleAddCtrl.projectUid, 'versionUid' : ArticleAddCtrl.version_info._uid })" title="<?= __tr('Back') ?>" class="lw-btn btn-sm btn btn-secondary"><i class="fas fa-arrow-alt-circle-left"></i> <?= __tr('Back') ?></a>

        </div>

    </div>
    <!-- /main heading -->
    <!-- Add article dialog form -->
    <form class="lw-form lw-ng-form" name="ArticleAddCtrl.[[ArticleAddCtrl.ngFormName]]" novalidate>
        <div class="row m-0">
            <div class="shadow border p-4 col-lg-9 col-md-12">

                <!-- Modal Body -->
                <div class="lw-form-body">



                    <!-- Previous Articles ID -->
                    <lw-form-field ng-if="ArticleAddCtrl.requestType == 2" field-for="previous_articles__id" label="<?= __tr('Choose Parent Article') ?>" class="lw-selectize">
                        <selectize config="ArticleAddCtrl.prevArticle" class="lw-form-field form-control lw-parent-article-dropdown" id="article_languages" ng-model="ArticleAddCtrl.articleData.previous_articles__id" name="previous_articles__id" options="ArticleAddCtrl.articlesList">
                        </selectize>
                    </lw-form-field>
                    <!-- /Previous Articles ID -->

                    <div class="">
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist" id="manageLanguagetabs">
                            <li class="nav-item" ng-repeat="article in ArticleAddCtrl.articleData.articles_content">
                                <a class="nav-link" ng-class="{'active': $first}" id="[[article.nav_link_id]]" ng-click="ArticleAddCtrl.selectedTab($event, ArticleAddCtrl.primaryLanguage, article.language_id)" data-toggle="tab" href="#[[article.tab_id]]" role="tab" aria-controls="[[article.tab_id]]" aria-selected="true">[[article.tab_name]] <i class="fas small fa-star text-primary" ng-if="ArticleAddCtrl.primaryLanguage === article.language_id"></i></a>
                            </li>
                        </ul>

                        <div>
                            <!-- Tab panes -->
                            <div class="py-3" ng-if="ArticleAddCtrl.articleData.articles_content.length > 0">
                                <div class="tab-content">
                                    <div id="[[article.tab_id]]" ng-class="{'active': $first}" class="tab-pane" aria-labelledby="[[article.nav_link_id]]" ng-repeat="article in ArticleAddCtrl.articleData.articles_content">

                                        <div class="alert alert-warning" role="alert" ng-if="ArticleAddCtrl.primaryLanguage !== article.language_id && !ArticleAddCtrl.primaryArticleExist">
                                            <?= __tr('You have to add primary language article first.') ?>
                                        </div>
                                        
                                        <!-- Title -->
                                        <lw-form-field field-for="articles_content.[[$index]].title" label="<?= __tr('Title') ?>">
                                            <input type="text" class="lw-form-field form-control" ng-model="article.title" name="articles_content.[[$index]].title" ng-required="ArticleAddCtrl.selectTabId === article.language_id" ng-minlength="2" ng-maxlength="255" />
                                        </lw-form-field>
                                        <!-- /Title -->


                                        <lw-form-field field-for="articles_content.[[$index]].description" label="<?= __tr('Description') ?>">
                                            <textarea ng-model="article.description" cols="10" rows="3" class="lw-form-field form-control" name="articles_content.[[$index]].description" ng-required="ArticleAddCtrl.primaryLanguage === article.language_id" ng-minlength="1" lw-editor></textarea>
                                        </lw-form-field>

                                        <div class="form-row" ng-show="ArticleAddCtrl.primaryLanguage !== article.language_id">
                                            <div class="col-lg-3">

                                                <!-- Status -->
                                                <lw-form-field field-for="status" label="<?= __tr('Language Status') ?>">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" ng-model="article.status" ng-true-value="1" ng-false-value="2" class="custom-control-input" id="status__[[$index]]">
                                                        <label class="custom-control-label" for="status__[[$index]]">
                                                            <span ng-show="article.status == 1">Active</span>
                                                            <span ng-show="article.status == 2">Inactive</span>
                                                        </label>
                                                    </div>
                                                </lw-form-field>
                                                <!-- /Status -->

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="py-3" ng-if="ArticleAddCtrl.articleData.articles_content.length == 0">
                                <div class="text-center">
                                    No Content Added
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" ng-model="ArticleAddCtrl.articleData.status" name="status">
                </div>
                <!-- /Modal Body -->


            </div>
            <div class="shadow border p-4 col-lg-3 col-md-12 lw-article-action-side-box">
                <!-- Slug -->
                <lw-form-field field-for="slug" label="<?= __tr('URL Slug') ?>">
                    <input type="text" class="lw-form-field form-control" ng-model="ArticleAddCtrl.articleData.slug" name="slug" ng-required="true" />
                </lw-form-field>
                <!-- /Slug -->
                <!-- select article type radio option -->
                <div class="form-group">
                    <lw-form-field field-for="articleStatus" label="<?= __tr('Mark as') ?>">
                        <br>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="drafArticle" name="articleStatus" class="custom-control-input" ng-model="ArticleAddCtrl.articleData.article_status" ng-value="2" ng-required="true">
                            <label class="custom-control-label" for="drafArticle">Draft</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="publishArticle" name="articleStatus" class="custom-control-input" ng-model="ArticleAddCtrl.articleData.article_status" ng-value="1" ng-required="true">
                            <label class="custom-control-label" for="publishArticle">Publish</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="unPublishArticle" name="articleStatus" class="custom-control-input" ng-model="ArticleAddCtrl.articleData.article_status" ng-value="3" ng-required="true">
                            <label class="custom-control-label" for="unPublishArticle">Unpublish</label>
                        </div>
                    </lw-form-field>
                </div>
                <!-- /select article type radio option -->
                <div class="lw-form-footer border-top mt-5">

                    <button ui-sref="project_articles({ 'projectUid' : ArticleAddCtrl.projectUid, 'versionUid' : ArticleAddCtrl.version_info._uid })" title="<?= __tr('Cancel') ?>" class="lw-btn btn btn-default"><?= __tr('Cancel') ?></button>

                    <button type="submit" class="lw-btn btn btn-primary" ng-click="ArticleAddCtrl.submit(ArticleAddCtrl.articleData.article_status)" title="<?= __tr('Save') ?>"><?= __tr('Save') ?></button>
                </div>
            </div>
        </div>
    </form>
</div>