<?php
/*
*  Component  : Manage User
*  View       : User detail dialog  
*  Engine     : ManageUserEngine  
*  File       : user-detail-dialog.blade.php  
*  Controller : ManageUsersDetailController as manageUsersDetailCtrl 
----------------------------------------------------------------------------- */ 
?>
<div>
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3 class="modal-title"><?= __tr('User Details') ?></h3>
    </div>
    <!-- /Modal Heading -->

    <!-- Modal Body -->
    <div class="modal-body">
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <?= __tr('User Full Name') ?>
                <span class="float-right">[[ manageUsersDetailCtrl.userData.userFullName ]]</span>
            </li>
            <li class="list-group-item">
                <?= __tr('Username') ?>
                <span class="float-right">[[ manageUsersDetailCtrl.userData.userName ]]</span>
            </li>
            <li class="list-group-item" ng-if="manageUsersDetailCtrl.userData.email">
                <?= __tr('Email') ?>
                <span class="float-right">[[ manageUsersDetailCtrl.userData.email ]]</span>
            </li>
            <li class="list-group-item">
                <?= __tr('Created On') ?>
                <span class="float-right">[[ manageUsersDetailCtrl.userData.created_at ]]</span>
            </li>
            <li class="list-group-item">
                <?= __tr('Role') ?>
                <span class="float-right">[[ manageUsersDetailCtrl.userData.userRoleTitle ]]</span>
            </li>
            <li class="list-group-item">
                <?= __tr('Status') ?>
                <span class="float-right">[[ manageUsersDetailCtrl.userData.status ]]</span>
            </li>
            <li class="list-group-item" ng-if="manageUsersDetailCtrl.userData.address_line_1">
                <?= __tr('Address 1') ?>
                <span class="float-right">[[ manageUsersDetailCtrl.userData.address_line_1 ]]</span>
            </li>
            <li class="list-group-item" ng-if="manageUsersDetailCtrl.userData.address_line_2">
                <?= __tr('Address 2') ?>
                <span class="float-right">[[ manageUsersDetailCtrl.userData.address_line_2 ]]</span>
            </li>
            <li class="list-group-item" ng-if="manageUsersDetailCtrl.userData.country">
                <?= __tr('Country') ?>
                <span class="float-right">[[ manageUsersDetailCtrl.userData.country ]]</span>
            </li>
        </ul>
    </div>
    <!-- /Modal Body -->
    
    <!-- Modal footer -->
    <div class="modal-footer">
        <button type="button" title="<?= __tr('Close') ?>" class="btn btn-default" ng-click="manageUsersDetailCtrl.closeDialog()"><?= __tr('Close') ?></button>
    </div> 
    <!-- /Modal footer -->
</div>