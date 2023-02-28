<?php 
/*
*  Component  : RolePermission
*  View       : Dynamic Role Permission
*  Engine     : RolePermissionEngine  
*  File       : dynamic-role-permissions.blade.php  
*  Controller : DynamicRolePermissionController 
----------------------------------------------------------------------------- */
?>
<div>
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3 class="modal-title">
        <?= __tr('Permissions of __title__ User Role', [
            '__title__' => '<strong>[[ DynamicRolePermissionCtrl.title ]]</strong>'
        ]) ?></h3>
    </div>
    <!-- /Modal Heading -->
	
    <form role="form" 
        class="lw-ng-form" 
        name="DynamicRolePermissionCtrl.[[ DynamicRolePermissionCtrl.ngFormName ]]"
        ng-submit="DynamicRolePermissionCtrl.submit()"
        novalidate>
        <!-- Modal Body -->
        <div class="modal-body">
            <div class="form-group">
               <input type="text" class="form-control" ng-model="searchText" placeholder="<?= __tr('Type to Filter') ?>">
            </div>
            <div class="table-responsive">
    		 	<table class="table small" width="100%">
    			    <tbody ng-repeat="parentpermission in DynamicRolePermissionCtrl.permissions | filter:searchText" class="hover-permission">
    			        <tr>
    			            <td width="30%" ng-bind="parentpermission.title"></td>
    			            <td>
                                <table class="table table-hover lw-table-borderless table-borderless " width="100%">
                                    <tbody>
                                        <tr ng-repeat="child in parentpermission.children">
                                            <td width="40%" >[[child.title]]</td>
                                            <td>
                                                <label class="radio-inline lw-permission-label" ng-repeat="option in child.options">
                                                    <input type="radio" class="mr-1" value="[[option.status]]" ng-model="DynamicRolePermissionCtrl.checkedPermission[child.id]" ng-click="DynamicRolePermissionCtrl.checkPermission(child.id, option.status)" 
                                                    id="[[child.id]]" name="[[child.id]]"  >[[option.title]]
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
                                            <td width="40%" >[[childpermission.title]]</td>
                                            <td>
                                                <label class="radio-inline lw-permission-label" ng-repeat="option in childpermission.options">
                                                    <input type="radio" ng-model="DynamicRolePermissionCtrl.checkedPermission[childpermission.id]" ng-click="DynamicRolePermissionCtrl.checkPermission(childpermission.id, option.status)"  id="[[childpermission.id]]" value="[[option.status]]"  name="[[childpermission.id]]">[[option.title]]
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
            <button type="button" ng-click="DynamicRolePermissionCtrl.closeDialog()" class="btn btn-default" title="<?=  __tr('Cancel')  ?>"><?=  __tr('Cancel')  ?></button>
            <!--  close button -->
        </div>
        <!-- /Modal footer -->
    </form>
    <!-- /End Form -->
</div>

 