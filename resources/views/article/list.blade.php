<?php
/*
*  Component  : Article
*  View       : Article Controller
*  Engine     : ArticleEngine
*  File       : article.list.blade.php
*  Controller : ArticleListController
----------------------------------------------------------------------------- */
?>
<div>

    <div class="lw-section-heading-block">

        <!-- Parent heading -->
        <ul class="lw-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a ui-sref="project"><?= __tr('Manage Projects') ?>
                </a></li>
            <li class="breadcrumb-item"><a ui-sref="project_versions({ 'projectIdOrUid' : articleListCtrl.projectUid })" ng-bind="articleListCtrl.project_info.name"></a></li>
            <li class="breadcrumb-item"><a ng-bind="articleListCtrl.version_info.version"></a></li>
        </ul>
        <!-- /Parent heading -->

        <!-- main heading -->
        <h3 class="lw-section-heading">
            <?= __tr('Articles') ?>
        </h3>
        <!-- /main heading -->

        <!-- New Role Button -->
        <div class="lw-section-right-content">

            <a title="<?= __tr('Add New Article') ?>" ng-show="canAccess('manage.article.write.create')" class="btn btn-sm btn-primary" ui-sref="project_article_add({ 'projectUid' : articleListCtrl.projectUid, 'versionUid' : articleListCtrl.version_info._uid, 'requestType' : 1 })">
                <i class="fa fa-plus"></i> <?= __tr('New Article') ?> </a>

            <a ui-sref="project_versions({ 'projectIdOrUid' : articleListCtrl.projectUid })" title="<?= __tr('Back') ?>" class="lw-btn btn-sm btn btn-secondary"><i class="fas fa-arrow-alt-circle-left"></i> <?= __tr('Back') ?></a>

        </div>
        <!-- New Role Button -->

    </div>
    <!-- /main heading -->

    <div ng-show="articleListCtrl.articles.length == 0" class="alert alert-info">
        <?= __tr('There are no articles') ?>
    </div>

    <!-- Article Tree -->

    <!-- Nested node template -->
    <script type="text/ng-template" id="nodes_renderer.html">

        <div class="tree-node">
            
            <div ng-if="canAccess('manage.article.write.update_parent')" class="float-left tree-handle angular-ui-tree-handle" ui-tree-handle>
                <span class="fas fa-arrows-alt"></span>
            </div>

            <div ng-if="!canAccess('manage.article.write.update_parent')" class="float-left tree-handle angular-ui-tree-handle">
                <span class="fas fa-arrows-alt"></span>
            </div>

            <div class="tree-node-content">
                <span ng-if="!canAccess('manage.article.write.update')" ng-bind="node.title"></span>
                <a ng-if="canAccess('manage.article.write.update')" ui-sref="project_article_edit({ 'articleIdOrUid' : node._uid, 'projectUid' : articleListCtrl.projectUid, 'versionUid' : articleListCtrl.version_info._uid})" ng-bind="node.title"></a> 
                <a href="[[ node.detailUrl ]]" target="_blank"><i class="fas fa-external-link-alt"></i></a>
                <span class="text-muted small"> 
                    <small>( Modified  [[ node.formated_updated_at ]])</small>
                </span> 
                <span>
                <span class="badge" ng-class="{
                    'badge-warning' : node.status == 3,
                    'badge-info' : node.status == 2,
                    'badge-success' : node.status == 1,
                }">[[ node.formatted_status ]]</span>
                </span>

                <span class="float-right mt-o">

                    <div class="mt-2">
                        <a class="btn btn-sm btn-outline-primary" ng-show="canAccess('manage.article.write.create')" title="Add Sub Article" ui-sref="project_subarticle_add({'projectUid' : articleListCtrl.projectUid, 'versionUid' : articleListCtrl.version_info._uid, 'prevArticle' : node._id, 'requestType' : 2 })"> <i class="fas fa-plus"></i> <?= __tr('Add Sub Article') ?>
                        </a>
                        
                        <a ng-if="canAccess('manage.article.write.update')" title="<?= __tr('Edit Article') ?>" class="btn btn-sm btn-outline-secondary" ui-sref="project_article_edit({ 'articleIdOrUid' : node._uid, 'projectUid' : articleListCtrl.projectUid, 'versionUid' : articleListCtrl.version_info._uid  })"> <i class="far fa-edit"></i> <?= __tr('Edit') ?>
                        </a>

                        <button ng-if="canAccess('manage.article.write.delete')" class="btn btn-sm btn-outline-danger" title="<?= __tr('Delete') ?>" ng-click="articleListCtrl.delete(node._uid, node.title)"><i class="far fa-trash-alt"></i> <?= __tr('Delete') ?>
                        </button>

                        <a href ng-if="canAccess('manage.article.read.list')" title="<?= __tr('Embed') ?>" class="btn btn-sm btn-outline-secondary" ng-click="articleListCtrl.showArticleEmbedScriptDialog(articleListCtrl.projectSlug, articleListCtrl.versionSlug, node.slug, articleListCtrl.languages__id)"> <i class="fas fa-code"></i> <?= __tr('Embed') ?>
                        </a>

                        <!-- <a href ng-if="canAccess('manage.article.write.update') && node.isParent" ng-href="[[ node.printUrl ]]" target="_blank" title="<?= __tr('Print') ?>" class="btn btn-sm btn-outline-secondary"> <i class="fa fa-print"></i></a> -->

                        <button ng-if="node.article_languages.length > 0" type="button" title="<?= __tr('Languages') ?>" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>

                        <div ng-if="node.article_languages.length > 0" class="dropdown-menu dropdown-menu-right lw-tree-dropdown-menu">

                            <table class="table mb-0">
                                <tbody>
                                    <tr ng-repeat="lang in node.article_languages">
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" ng-click="articleListCtrl.showArticleContentDetails(lang._uid, node._uid)" class="btn-sm btn btn-lite">[[lang.name]]</button>
                                            </div>
                                        </td>

                                        <td>
                                            <button class="btn btn-sm btn-lite" title="<?= __tr('Embed') ?>" ng-click="articleListCtrl.showArticleEmbedScriptDialog(articleListCtrl.projectSlug, articleListCtrl.versionSlug, article.title, lang.languages__id, node._uid)"><small class="fas fa-code"></small> <?= __tr('Embed') ?></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
            
                    </div>

                </span>            

            </div>

        </div>

        <ol ui-tree-nodes="" ng-model="node.children" ng-class="{hidden: collapsed}">
            <li ng-repeat="node in node.children" ui-tree-node ng-include="'nodes_renderer.html'">
            </li>
        </ol>

    </script>
    <!-- / Nested node template -->

    <div ng-show="articleListCtrl.articles.length > 0">
        <div data-ui-tree="treeOptions" id="tree-root">
            <ol data-ui-tree-nodes ng-model="articleListCtrl.articles">
                <li data-ng-repeat="node in articleListCtrl.articles" data-ui-tree-node ng-include="'nodes_renderer.html'"></li>
            </ol>
        </div>
    </div>
    <!-- /Article Tree -->

    <input type="hidden" id="lwArticleDeleteConfirmTextMsg" data-message="<?= __tr('You want to delete <b> __name__ </b> Article', ['__name__' => '[[ articleListCtrl.articleName ]]']) ?>" data-delete-button-text="<?= __tr('Yes, delete it') ?>" , success-msg="<?= __tr('Article deleted successfully.') ?>">

</div>