<?php
/*
*  Component  : Version
*  View       : Version Controller
*  Engine     : VersionEngine  
*  File       : version.list.blade.php  
*  Controller : VersionListController 
----------------------------------------------------------------------------- */
?>

<div>
    <!-- Heading Section -->
    <div class="lw-section-heading-block">
        <!-- Parent heading -->
        <!-- <div class="lw-breadcrumb">
            <a ui-sref="project">Manage Projects</a> &raquo; <a ui-sref="project" ng-bind="versionListCtrl.projectInfo.name"></a> &raquo;
        </div> -->
        <!-- Parent heading -->
        <ul class="lw-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><a ui-sref="project"><?= __tr('Manage Projects') ?>
                </a></li>
            <li class="breadcrumb-item"><a ng-bind="versionListCtrl.projectInfo.name"></a></li>
        </ul>
        <!-- /Parent heading -->

        <!-- main heading -->
        <h3 class="lw-section-heading">
            Versions/Groups
        </h3>
        <!-- /main heading -->

        <div class="lw-section-right-content" ng-if="canAccess('manage.project.version.write.create')">

            <a ng-if="canAccess('manage.project.version.write.create')" class="btn btn-sm btn-primary lw-btn" href ng-click="versionListCtrl.openAddDialog(versionListCtrl.projectIdOrUid)"><i class="fas fa-plus"></i> <?= __tr(' New Version / Group') ?></a>

            <a ui-sref="project" title="<?= __tr('Back') ?>" class="lw-btn btn-sm btn btn-secondary"><i class="fas fa-arrow-alt-circle-left"></i> <?= __tr('Back') ?></a>

        </div>

    </div>
    <!-- /Heading Section -->
    <div class="shadow border p-4">

        <div class="table-responsive">
            <!-- Body Section -->
            <table class="table table-bordered" width="100%">
                <thead>
                    <tr>
                        <th scope="col">Version / Group</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-show="versionListCtrl.versions.length > 0" ng-repeat="version in versionListCtrl.versions">
                        <th scope="row">

                            <span ng-if="version.canManageArticles">
                                <a title="<?= __tr('Articles') ?>" ui-sref="project_articles({ 'projectUid' : versionListCtrl.projectInfo._uid, versionUid : version._uid  })" ng-bind="version.version"><i class="far fa-newspaper"></i> <?= __tr('Articles') ?></a>
                            </span>
                            <span ng-if="!version.canManageArticles" ng-bind="version.version"></span>
                            <a ng-if="version.status == 1" title="[[version.detailUrl]]" target="_blank" href="[[version.detailUrl]]"><i class="fas fa-external-link-alt"></i> </a>

                            <span class="h6 float-right">
                                <span class="badge badge-primary" ng-show="version.is_primary == 1">Primary</span>
                            </span>
                        </th>
                        <td ng-bind="version.f_status"></td>
                        <td ng-bind="version.created_at"></td>
                        <td>
                            <button ng-if="canAccess('manage.project.version.read.list')" class="btn btn-sm btn-lite" title="<?= __tr('Embed') ?>" ng-click="versionListCtrl.showEmbedScriptDialog(version.projectSlug, version.versionSlug)"><span class="fas fa-code"></span> Embed </button>

                            <button ng-if="version.canEdit" class="btn btn-secondary" ng-click="versionListCtrl.openEditDialog(version._uid)"><i class="fas fa-edit"></i> Edit</button>

                            <button class="btn btn-danger" ng-if="version.canDelete" ng-click="versionListCtrl.delete(version._uid, version.version)"><i class="fas fa-trash-alt"></i> Delete</button>

                            <a ng-if="version.canDownload" ng-show="version.canGeneratePdf == 1" href="[[version.download_url]]" class="btn btn-secondary"><i class="fas fa-download"></i> Download as PDF</a>

                        </td>
                    </tr>
                    <tr ng-show="versionListCtrl.versions.length == 0">
                        <td colspan="4" class="text-center">There are no [[::versionListCtrl.projectInfo.name]] versions</td>
                    </tr>
                </tbody>
            </table>
            <!-- /Body Section -->
        </div>

    </div>

    <input type="hidden" id="lwVersionDeleteConfirm" data-message="<?= __tr('You want to delete <strong>__name__</strong> project version, Related information will be delete.', [
                                                                        '__name__' => '[[ versionListCtrl.deletingTagName ]]'
                                                                    ]) ?>" data-delete-button-text="<?= __tr('Yes, delete it') ?>" , success-msg="<?= __tr('Version deleted successfully.') ?>"></div>