<?php 
/*
*  Component  : RolePermission
*  View       : Role Permission Controller
*  Engine     : RolePermissionEngine  
*  File       : rolePermission.list.blade.php  
*  Controller : RolePermissionListController 
----------------------------------------------------------------------------- */
?>
<div>
    <div class="lw-section-heading-block">
        <!-- main heading -->
        <h3 class="lw-section-heading">
            <div class="lw-heading">
                <?= __tr('Manage User Roles') ?>
            </div>
        </h3>

        <!-- New Role Button -->
        <div class="lw-section-right-content">
            <a title="<?=  __tr('New User Role')  ?>" ng-show="canAccess('manage.user.role_permission.write.role.create')" class="lw-btn btn btn-primary btn-sm float-right" ng-click="rolePermissionListCtrl.showAddNewDialog()" href><i class="fa fa-plus"></i> <?=  __tr('New User Role')   ?></a>
        </div>
        <!-- New Role Button -->
     </div>
    <!-- /main heading -->

    <input type="hidden" id="lwRolePermissionDeleteTextMsg" data-message="<?= __tr( 'you want to delete __name__ user role') ?>" data-delete-button-text="<?= __tr('Yes, delete it') ?>" data-success-text="<?= __tr( 'Deleted!') ?>" data-error-text="<?= __tr( 'Unable to Delete!') ?>">
    
    <div class="shadow border p-4">


        <table class="table table-striped table-bordered" id="lwrolePermissionList" class="ui celled table" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?= __tr('Title') ?></th>
                    <th><?= __tr('Action') ?></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div ui-view></div>

    </div>

    <!-- action template -->
    <script type="text/_template" id="rolePermissionActionColumnTemplate">
    <% if(__tData._id !== 1) { %>

        <% if((__tData.can_delete) && __tData._id !== 2) { %>
            <button class="btn btn-danger btn-xs" title="<?= __tr('Delete') ?>" ng-click="rolePermissionListCtrl.delete('<%- __tData._uid %>', '<%- _.escape(__tData.title) %>')"><i class="fa fa-trash-alt"></i> <?= __tr('Delete') ?></button>
        <% } %>

        <% if(__tData.can_manage_permission) { %>
            <button title="<?=  __tr('Permissions')  ?>" class="btn btn-secondary btn-xs" ng-click="rolePermissionListCtrl.rolePermissionDialog('<%- __tData._id %>', '<%- _.escape(__tData.title) %>')"><i class="fa fa-shield-alt"></i> <?=  __tr('Permissions')  ?></button>
        <% } %>
    <% } %>

    <% if((__tData._id == 1) || (__tData._id == 2)) { %>
        <% if(__tData.can_delete) { %>
            <button ng-disabled="true" class="lw-btn btn btn-danger btn-xs" title="<?= __tr('As this is system role you cannot delete this.') ?>" href="" disabled="true"><i class="fa fa-trash-alt fa-lg"></i> <?= __tr('Delete') ?></button>
        <% } %>
    <% } %>
    </script>
    <!-- /action template -->

    <!-- Role Permission delete dialog template -->
    <script type="text/ng-template" id="lw-role-permission-delete-dialog.ngtemplate">
        <p><?= __tr('Are you sure you want to delete this Role Permission?') ?></p>
        <div class="ngdialog-buttons">
            <button type="button" title="<?= __tr('Yes') ?>" class="btn btn-primary btn-sm" ng-click="confirm()"><i class="trash icon"></i> <?= __tr('Yes') ?>            </button>
            <button type="button" title="<?= __tr('No') ?>" class="btn btn-default btn-sm" ng-click="closeThisDialog()"><?= __tr('No') ?></button>
        </div>
    </script>
    <!-- /Role Permission delete dialog template -->
            
</div> 