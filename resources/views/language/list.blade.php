<?php
/*
*  Component  : Language
*  View       : Language Controller
*  Engine     : LanguageEngine
*  File       : Language.list.blade.php
*  Controller : LanguageListController
----------------------------------------------------------------------------- */
?>
<div>
    <div class="lw-section-heading-block">
        <!-- main heading -->
        <h3 class="lw-section-heading">
            <div class="lw-heading">
                <?= __tr('Manage Languages') ?>
            </div>
        </h3>

        <!-- button -->
        <div class="lw-section-right-content">
            <button title="<?= __tr('New Language') ?>" ng-if="canAccess('manage.language.write.create')" class="btn btn-sm btn-primary float-right" ng-click="languageListCtrl.openAddDialog()">
            <i class="fa fa-plus"></i> <?= __tr('New Language') ?> </button>
        </div>
        <!--/ button -->
    </div>
    <!-- /main heading -->

    <div class="shadow border p-4">

        <table class="table table-striped table-bordered" id="lwLanguageList" class="ui celled table" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?= __tr('Name') ?></th>
                    <th><?= __tr('Code') ?></th>
                    <th><?= __tr('Is RTL') ?></th>
                    <th><?= __tr('Action') ?></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div ui-view></div>

    </div>

    <!-- action template -->
    <script type="text/_template" id="languageActionColumnTemplate">
    <!-- button -->
    <div class="lw-section-right-content">

        <% if(__tData.canEditLanguage) { %>
            <button title="<?= __tr('Edit Language') ?>" class="lw-btn btn btn-sm btn-default" ng-click="languageListCtrl.openEditDialog('<%- __tData._id %>')"><i class="far fa-edit"></i> Edit</button>
        <% } %>
        <% if(__tData.canDeleteLanguage) { %>
            <button class="btn btn-danger btn-xs" title="<?= __tr('Delete') ?>"  ng-click="languageListCtrl.delete('<%- __tData._id %>')"><i class="far fa-trash-alt"></i> <?= __tr('Delete') ?></button>
        <% } %>
    </div>
    <!--/ button -->

    </script>
    <!-- /action template -->

    <input type="hidden" id="lwLanguageDelete" data-message="<?= __tr( 'You want to delete <b> __name__ </b> language', [ '__name__' => '[[ projectListCtrl.languagename ]]' ]) ?>" data-delete-button-text="<?= __tr('Yes, delete it') ?>", success-msg="<?= __tr('Language deleted successfully.') ?>">

</div>