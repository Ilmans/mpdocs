<?php
/*
*  Component  : Manage User
*  View       : User List  
*  Engine     : ManageUserEngine  
*  File       : list.blade.php  
*  Controller : ManageUsersController 
----------------------------------------------------------------------------- */ 
?>
<div>
    <div class="lw-section-heading-block">
  
        <!--  main heading  -->
        <h3 class="lw-section-heading"><div class="lw-heading"><?= __tr( 'Manage Users' ) ?></div></h3>
        <!--  /main heading  -->

        <div class="lw-section-right-content float-right">
            <!-- New User button -->
            <a title="<?=  __tr('New User')  ?>" ng-show="canAccess('manage.user.write.create')" class="btn btn-primary btn-sm" ng-click="manageUsersCtrl.showAddNewDialog()" href><i class="fa fa-plus"></i> <?=  __tr('New User')   ?></a>
            <!-- /New User button -->

            <!-- user Role button -->
            <a class="btn btn-sm btn-secondary" ng-show="canAccess('manage.user.role_permission.read.list')" title="<?= __tr( 'Go to User Roles' ) ?>" ui-sref="role_permission"><?= __tr( 'User Roles' ) ?></a>
            <!--/user Role button -->
        </div>
        
    </div>

    <div class="shadow border p-4">
    
        <!--  User Tabs  -->
        <ul class="nav nav-tabs lw-tabs mb-3" role="tablist" id="manageUsersTabs">
            <li role="presentation" class="nav-item active">
                <a href="#active" class="nav-link active" aria-controls="active" role="tab" data-toggle="tab" title="<?=  __tr('Active')  ?>"><i class="fa fa-check-circle-o" aria-hidden="true"></i> <?=  __tr('Active')  ?></a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="#inactive" class="nav-link" aria-controls="inactive" role="tab" data-toggle="tab" title="<?=  __tr('Inactive')  ?>"><i class="fa fa-ban" aria-hidden="true"></i> <?=  __tr('Inactive')  ?></a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="#deleted" class="nav-link" aria-controls="deleted" role="tab" data-toggle="tab" title="<?=  __tr('Deleted')  ?>">
                <i class="fa fa-trash-o" aria-hidden="true"></i> <?=  __tr('Deleted')  ?></a>
            </li>
        </ul>
        <!--  /User Tabs  -->

        <input type="hidden" id="lwUserDeleteConfirmTextMsg" data-message="<?= __tr( 'You want to delete __name__ user. This user placed in deleted tab', [
                            '__name__' => '[[ manageUsersCtrl.deletingUserName ]]'
                        ]) ?>" data-delete-button-text="<?= __tr('Yes, delete it') ?>", success-msg="<?= __tr('User deleted successfully. This user placed in deleted tab.') ?>">

        <input type="hidden" id="lwUserRestoreConfirmTextMsg" data-message="<?= __tr( 'You want to restore __name__ user.', [
                            '__name__' => '[[ manageUsersCtrl.restoringUserName ]]'
                        ]) ?>" data-restore-button-text="<?= __tr('Yes, restore it') ?>">

        <!--  Tab panes  -->
        <div class="tab-content lw-tab-content">

            <!--  Active Users Tab  -->
            <div role="tabpanel" class="tab-pane fade in show active" id="active">
                <!--  datatable container  -->
                
                    <table class="table table-striped table-bordered" id="activeUsersTabList" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th><?= __tr( 'Profile Picture' ) ?></th>
                                <th><?= __tr( 'Name' ) ?></th>
                                <th><?= __tr( 'Username') ?></th>
                                <th><?= __tr( 'Email' ) ?></th>
                                <th><?= __tr( 'Since' ) ?></th>
                                <th><?= __tr( 'Role' ) ?></th>
                                <th><?= __tr( 'Action' ) ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                <!--  /datatable container  -->
            </div>
            <!--  /Active Users Tab  -->

            <!--  Inactive Users Tab  -->
            <div role="tabpanel" class="tab-pane fade" id="inactive">
                <!--  datatable container  -->
                
                    <table class="table table-striped table-bordered" id="inactiveUsersTabList" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th><?= __tr( 'Profile Picture' ) ?></th>
                                <th><?= __tr( 'Name' ) ?></th>
                                <th><?= __tr( 'Username') ?></th>
                                <th><?= __tr( 'Email' ) ?></th>
                                <th><?= __tr( 'Since' ) ?></th>
                                <th><?= __tr( 'Role' ) ?></th>
                                <th><?= __tr( 'Action' ) ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                <!--  /datatable container  -->
            </div>
            <!--  /Inactive Users Tab  -->
            
            <!--  Deleted Users Tab  -->
            <div role="tabpanel" class="tab-pane fade" id="deleted">
                <!--  datatable container  -->
                    <table class="table table-striped table-bordered" id="deletedUsersTabList" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th><?= __tr( 'Profile Picture' ) ?></th>
                                <th><?= __tr( 'Name' ) ?></th>
                                <th><?= __tr( 'Username') ?></th>
                                <th><?= __tr( 'Email' ) ?></th>
                                <th><?= __tr( 'Deleted on' ) ?></th>
                                <th><?= __tr( 'Role' ) ?></th>
                                <th><?= __tr( 'Action' ) ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                <!--  /datatable container  -->
            </div>
            <!--  /Deleted Users Tab  -->
        </div>

    </div>
    
    <!--  user name column template  -->
    <script type="text/template" id="userNameColumnTemplate">
        <% if(__tData.canViewDetails) { %>
            <a href ng-click="manageUsersCtrl.openUserDetailsDialog('<%- __tData._uid %>')" title="User name"><%= __tData.name %></a>
        <% } else { %>
            <%= __tData.name %>
        <% } %>
    </script>
    <!--  user name column template  -->

    <!--  user updated date column template  -->
    <script type="text/template" id="userUpdatedDateColumnTemplate"> 
        <span title="<%= __tData.updated_date %>"><%= __tData.human_readable_updated_date %></span>
    </script>
    <!--  user updated date template  -->

    <!--  user action column template  -->
    <script type="text/template" id="userActionColumnTemplate" ng-cloak> 
        <% if(__tData.role !== 1) { %>
            
            <% if(__tData.status === 5 && __tData.can_delete) { %> <!--  5 (Deleted)  -->
                <button type="button" title="<?=  __tr('Restore')  ?>" ng-show="canAccess('manage.user.write.restore')" class="btn btn-secondary btn-xs" ng-click='manageUsersCtrl.restore("<%- __tData._id %>", "<%- __tData.name %>")'><b>&#8644;</b> <?= __tr('Restore') ?></button>

            <% } else if(__tData.status === 12) { %>
                
            <% } else { %>

                <% if(__tData.can_delete) { %>
                <button type="button" title="<?=  __tr('Delete')  ?>" class="btn btn-danger btn-xs delete-sw" ng-click='manageUsersCtrl.delete("<%- __tData._id %>","<%- escape(__tData.name) %>", 1)'><i class="fa fa-trash-alt"></i> <?=  __tr('Delete')  ?></button>
                <% } %>

                <% if(__tData.can_update) { %>
                    <button type="button" title="<?=  __tr('Permission')  ?>" class="btn btn-secondary btn-xs" href ng-click="manageUsersCtrl.usersPermissionDialog('<%- __tData._id %>', '<%- _.escape(__tData.name) %>')"><i class="fa fa-user-shield"></i> <?=  __tr('Permission')  ?></button>

                    <button type="button" title="<?=  __tr('Edit')  ?>" class="btn btn-secondary btn-xs" ng-click='manageUsersCtrl.editUserDialog("<%- __tData._id %>","<%- escape(__tData.name) %>")'><i class="fa fa-edit"></i> <?=  __tr('Edit')  ?></button>
                <% } %>

            <% } %>

            <% if(__tData.status != 4 && __tData.can_update) { %>
                <button type="button" title="<?=  __tr('Change Password')  ?>" ng-show="canAccess('manage.user.write.change_password.process')" class="btn btn-secondary btn-xs" ng-click='manageUsersCtrl.changePassword("<%- __tData._id %>","<%- escape(__tData.name) %>")'> <i class="fa fa-key"></i> <?= __tr('Change Password') ?></button>
                
            <% } %>

        <% } %>
    </script>
    <!--  /user action column template ui-sref="orders.active" -->

    <!-- profileImageColumnTemplate -->
    <script type="text/_template" id="profileImageColumnTemplate">

        <img class="lw-image-thumbnail" src="<%- __tData.profile_img_url %>">

    </script>
    <!-- /profileImageColumnTemplate -->

</div>
