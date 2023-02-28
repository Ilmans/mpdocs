<?php
/*
*  Component  : Article
*  View       : Article Controller
*  Engine     : ArticleEngine
*  File       : edit.blade.php  
*  Controller : ArticleEditController
----------------------------------------------------------------------------- */
?>
<div>
    <div class="lw-section-heading-block">
        <!-- Parent heading -->
        <ul class="lw-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a ui-sref="project"><?= __tr('Manage Projects') ?>
            </a></li>
            <li class="breadcrumb-item"><a ui-sref="project" ng-bind="articleEditCtrl.projectName"></a></li>
            <li class="breadcrumb-item"><a ui-sref="project_versions({ 'projectIdOrUid' : articleEditCtrl.projectUid })" ng-bind="articleEditCtrl.version_info.version"></a></li>
           <li class="breadcrumb-item"> <a ui-sref="project_articles({ 'projectUid' : articleEditCtrl.projectUid, 'versionUid' : articleEditCtrl.version_info._uid })">Articles</a></li>

        </ul>

        <!-- /Parent heading -->

        <!-- main heading -->
        <h3 class="lw-section-heading">
            Article Edit
        </h3>
        <!-- /main heading -->

        <div class="lw-section-right-content">

            <a ui-sref="project_articles({ 'projectUid' : articleEditCtrl.projectUid, 'versionUid' : articleEditCtrl.version_info._uid })" title="<?= __tr('Back') ?>" class="lw-btn btn-sm btn btn-secondary"><i class="fas fa-arrow-alt-circle-left"></i> <?= __tr('Back') ?></a>

        </div>

    </div>
    <!-- /main heading -->
    <form class="ui form lw-form lw-ng-form" name="articleEditCtrl.[[articleEditCtrl.ngFormName]]" novalidate>
        <div class="row m-0">
            <div class="shadow border p-4 col-lg-9 col-md-12">

                <!-- Add article dialog form -->


                <!-- Modal Body -->
                <div class="lw-form-body">

                    <!-- Parent Articles ID -->
                    <lw-form-field ng-if="articleEditCtrl.requestType == 2" field-for="previous_articles__id" label="<?= __tr('Choose Parent Article') ?>" class="lw-selectize">
                        <selectize config="articleEditCtrl.prevArticle" class="lw-form-field form-control lw-parent-article-dropdown" id="article_languages" ng-model="articleEditCtrl.articleData.previous_articles__id" name="previous_articles__id" options="articleEditCtrl.articlesList">
                        </selectize>
                    </lw-form-field>
                    <!-- /Parent Articles ID -->
                    <div>
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist" id="manageLanguagetabs">
                            <li class="nav-item" ng-repeat="article in articleEditCtrl.articleData.articles_content">
                                <a class="nav-link" ng-class="{'active': $first}" id="[[article.nav_link_id]]" ng-click="articleEditCtrl.selectedTab($event, article.language_id)" data-toggle="tab" href="#[[article.tab_id]]" role="tab" aria-controls="[[article.tab_id]]" aria-selected="true">[[article.tab_name]] <i class="fas small fa-star text-primary" ng-if="articleEditCtrl.primaryLanguage === article.language_id"></i></a>
                            </li>
                        </ul>

                        <div class="">
                            <!-- Tab panes -->
                            <div class="py-3" ng-if="articleEditCtrl.articleData.articles_content.length > 0">
                                <div class="tab-content">
                                    <div id="[[article.tab_id]]" ng-class="{'active': $first}" class="tab-pane" aria-labelledby="[[article.nav_link_id]]" ng-repeat="article in articleEditCtrl.articleData.articles_content">

                                        <!-- Title -->
                                        <lw-form-field field-for="articles_content.[[$index]].title" label="<?= __tr('Title') ?>">

                                            <input type="text" class="lw-form-field form-control" ng-model="article.title" name="articles_content.[[$index]].title" ng-required="articleEditCtrl.selectTabId === article.language_id" ng-minlength="2" ng-maxlength="255" ng-change="articleEditCtrl.generateSlug(article.title, article.language_id, articleEditCtrl.primaryLanguage)" />
                                        </lw-form-field>
                                        <!-- /Title -->

                                        <!-- Description -->
                                        <!-- <lw-form-field field-for="articles_content.[[$index]].description" label="<?= __tr('Description') ?>">

                                <lw-editor 
                                    ng-model="article.description"
                                    class="lw-form-field"
                                    name="articles_content.[[$index]].description"
                                    name="articles_content.[[$index]].description"
                                    ng-required="articleEditCtrl.primaryLanguage === article.language_id" ng-minlength="1"  
                                    ng-minlength="1"  
                                >
                                </lw-editor>

                            </lw-form-field> -->


                                        <lw-form-field field-for="articles_content.[[$index]].description" label="<?= __tr('Description') ?>">
                                            <textarea ng-model="article.description" cols="10" rows="3" lw-editor class="lw-form-field form-control" name="articles_content.[[$index]].description" ng-required="articleEditCtrl.primaryLanguage === article.language_id" ng-minlength="1" lw-editor></textarea>
                                        </lw-form-field>
                                        <!-- /Description -->

                                        <div class="form-row" ng-show="articleEditCtrl.primaryLanguage !== article.language_id">
                                            <div class="col-lg-3">
                                                <!-- Status -->
                                                <lw-form-field field-for="status" label="<?= __tr('Language Status') ?>">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" ng-model="article.status" ng-true-value="1" ng-false-value="2" class="custom-control-input" id="status__[[$index]]">
                                                        <label class="custom-control-label" for="status__[[$index]]">
                                                            <span ng-if="article.status == 1">Active</span>
                                                            <span ng-if="article.status == 2">Inactive</span>
                                                        </label>
                                                    </div>
                                                </lw-form-field>
                                                <!-- /Status -->
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="py-3" ng-if="articleEditCtrl.articleData.articles_content.length == 0">
                                <div class="text-center">
                                    No content here
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Modal Body -->


            </div>
            <div class="shadow border p-4 col-lg-3 col-md-12 lw-article-action-side-box">
                <!-- Slug -->
                <lw-form-field field-for="slug" label="<?= __tr('URL Slug') ?>">
                    <input type="text" class="lw-form-field form-control" ng-model="articleEditCtrl.articleData.slug" name="slug" ng-required="true" />
                </lw-form-field>
                <!-- /Slug -->
                <!-- select article type radio option -->
                <div class="form-group">
                    <lw-form-field field-for="articleStatus" label="<?= __tr('Mark as') ?>">
                        <br>
                        <div class="custom-control custom-radio custom-control-inline active">
                            <input type="radio" id="drafArticle" name="article_status" class="custom-control-input active" ng-model="articleEditCtrl.articleData.article_status" ng-value="2" ng-required="true" checked>
                            <label class="custom-control-label active" for="drafArticle">Draft</label>
                        </div>

                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="publishArticle" name="article_status" class="custom-control-input" ng-model="articleEditCtrl.articleData.article_status" ng-value="1" ng-required="true">
                            <label class="custom-control-label" for="publishArticle"><?= __tr('Publish') ?>
                            </label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="unPublishArticle" name="article_status" class="custom-control-input" ng-model="articleEditCtrl.articleData.article_status" ng-value="3" ng-required="true">
                            <label class="custom-control-label" for="unPublishArticle"><?= __tr('Unpublish') ?>
                            </label>
                        </div>
                    </lw-form-field>
                </div>
                <!-- /select article type radio option -->
                <div class="lw-form-footer border-top mt-5">

                    <button ui-sref="project_articles({ 'projectUid' : articleEditCtrl.projectUid, 'versionUid' : articleEditCtrl.version_info._uid })" title="<?= __tr('Back') ?>" type="button" class="lw-btn btn btn-default"><?= __tr('Back') ?></button>

                    <button type="submit" class="lw-btn btn btn-primary" ng-click="articleEditCtrl.submit(articleEditCtrl.articleData.article_status)" title="<?= __tr('Save') ?>"><?= __tr('Save') ?></button>

                </div>
            </div>
            <!-- /Modal footer -->
        </div>
    </form>
</div>