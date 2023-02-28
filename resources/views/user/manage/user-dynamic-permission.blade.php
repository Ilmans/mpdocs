<?php
/*
*  Component  : Manage User
*  View       : User Dynamic Permission
*  Engine     : ManageUserEngine  
*  File       : user-dynamic-permission.blade.php  
*  Controller : ManageUsersDynamicPermissionController 
----------------------------------------------------------------------------- */ 
?>
<div>
    
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3 class="modal-title">
            <?= __tr( 'Manage Permission of __fullName__', [
                '__fullName__' => '<strong>[[ manageUsersDynamicPermissionCtrl.fullName ]]</strong>'
            ]) ?>
        </h3>
    </div>

    <!-- form start -->
    <form role="form" 
        class="lw-ng-form" 
        name="manageUsersDynamicPermissionCtrl.[[ manageUsersDynamicPermissionCtrl.ngFormName ]]"
        ng-submit="manageUsersDynamicPermissionCtrl.submit()"
        novalidate>
        <!-- Modal Body -->
        <div class="modal-body">
            <div class="form-group">
               <input type="text" class="form-control" ng-model="searchText" placeholder="<?= __tr('Type to Filter') ?>">
            </div>
            <div class="table-responsive">
                <table class="table small" width="100%">
                    <tbody ng-repeat="parentpermission in manageUsersDynamicPermissionCtrl.permissions | filter:searchText" class="hover-permission">
                        <tr>
                            <td width="30%" ng-bind="parentpermission.title"></td>
                            <td>
                                <table class="table table-hover table-borderless lw-table-borderless" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr ng-repeat="child in parentpermission.children">
                                            <td width="35%" >[[child.title]]</td>
                                            <td>
                                                <label class="radio-inline lw-permission-label"  ng-repeat="option in child.options">
                                                <input type="radio" class="mr-1" ng-model="manageUsersDynamicPermissionCtrl.checkedPermission[child.id]" 
                                                ng-click="manageUsersDynamicPermissionCtrl.checkPermission(child.id, option.status)"  value="[[option.status]]"
                                                id="[[child.id]]" name="[[child.id]]" >[[option.title]]
                                            </label>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr ng-repeat="subchildpermission in parentpermission.children_permission_group" ng-if="parentpermission.children_permission_group">
                            <td width="30%">&nbsp;&nbsp; &#8735;</i> [[subchildpermission.title]]</td>
                            <td>
                                <table class="table table-hover lw-table-borderless" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr ng-repeat="childpermission in subchildpermission.children">
                                            <td width="35%"> [[childpermission.title]]</td>
                                            <td>
                                               <label class="radio-inline lw-permission-label"  ng-repeat="option in childpermission.options">
                                                    <input type="radio" ng-model="manageUsersDynamicPermissionCtrl.checkedPermission[childpermission.id]" ng-click="manageUsersDynamicPermissionCtrl.checkPermission(childpermission.id, option.status)"  id="[[childpermission.id]]" value="[[option.status]]"  name="[[childpermission.id]]">[[option.title]]
                                                </label>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
        </div>
        <!-- /Modal Body -->

        <!-- /Modal footer -->
        <div class="modal-footer">
            <!--  add button -->
            <button type="submit" class="btn btn-primary" title="<?=  __tr('Update')  ?>"><?=  __tr('Update')  ?> </button>
            <!--  /add button -->

            <!--  close button -->
            <button type="button" ng-click="manageUsersDynamicPermissionCtrl.closeDialog()" class="btn btn-default" title="<?=  __tr('Cancel')  ?>"><?=  __tr('Cancel')  ?></button>
            <!--  close button -->
        </div>
        <!-- /Modal footer -->
    </form>
    <!-- /End Form -->
</div>