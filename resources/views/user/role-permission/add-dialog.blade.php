<?php 
/*
*  Component  : RolePermission
*  View       : Add New Role
*  Engine     : RolePermissionEngine  
*  File       : add-dialog.blade.php  
*  Controller : AddRoleController 
----------------------------------------------------------------------------- */
?>
<div>
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3 class="modal-title"><?= __tr('Add New User Role') ?></h3>
    </div>
    <!-- /Modal Heading -->

    <form role="form" 
        class="lw-ng-form" 
        name="addRoleCtrl.[[ addRoleCtrl.ngFormName ]]"
        ng-submit="addRoleCtrl.submit()"
        novalidate>
        <!-- Modal Body -->
        <div class="modal-body">

            <!--  Title  -->
            <lw-form-field field-for="title" label="<?=  __tr( 'Title' )  ?>"> 
                <input type="text" 
                  class="lw-form-field form-control"
                  name="title"
                  ng-required="true"
                  ng-minlength="2"
                  ng-maxlength="255"
                  ng-model="addRoleCtrl.roleData.title" />
            </lw-form-field>
            <!--  /Title  -->

            <!-- Preset Permissions -->
            <lw-form-field field-for="role_id" label="<?= __tr( 'Import Permission from' ) ?>"> 
               <select class="form-control" 
                    name="role_id" ng-model="addRoleCtrl.roleData.role_id" ng-options="userRole.id as userRole.name for userRole in addRoleCtrl.userRoles" ng-required="true" ng-change="addRoleCtrl.getPermissions(addRoleCtrl.roleData.role_id)">
                    <option value='' disabled selected><?=  __tr('-- Import Permission from --')  ?></option>
                </select> 
            </lw-form-field><br>
            <!-- /Preset Permissions-->
			<div class="table-responsive">
    			<table class="table small" width="100%">
    			    <tbody ng-repeat="parentpermission in addRoleCtrl.permissions" class="hover-permission">
    			        <tr>
    			            <td width="30%" ng-bind="parentpermission.title"></td>
    			            <td>
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr  ng-repeat="child in parentpermission.children">
                                            <td width="40%" >[[child.title]]</td>   
                                            <td>
                                                <label class="radio-inline lw-permission-label" ng-repeat="option in child.options">
                                                    <input type="radio" class="mr-1" ng-model="addRoleCtrl.checkedPermission[child.id]" ng-click="addRoleCtrl.checkPermission(child.id, option.status)"  id="[[child.id]]" value="[[option.status]]"  name="[[child.id]]">[[option.title]]
                                                </label>
                                              
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
    			 			</td>
    			        </tr>
    			        <tr ng-repeat="subchildpermission in parentpermission.children_permission_group" ng-if="parentpermission.children_permission_group">
    			            <td>&nbsp;&nbsp; &#8735;</i> [[subchildpermission.title]]</td>
    			            <td>
                                <table class="table table-hover lw-table-borderless" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr ng-repeat="childpermission in subchildpermission.children">
                                            <td width="40%" >[[childpermission.title]]</td>   
                                            <td>
                                                <label class="radio-inline lw-permission-label"   ng-repeat="option in childpermission.options" >
                                                    <input type="radio" class="mr-1" ng-model="addRoleCtrl.checkedPermission[childpermission.id]" ng-click="addRoleCtrl.checkPermission(childpermission.id, option.status)"  id="[[childpermission.id]]"  value="[[option.status]]"  name="[[childpermission.id]]">[[option.title]]
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

        <!-- /Modal footer -->
        <div class="modal-footer">
            <!--  add button -->
            <button type="submit" class="btn btn-primary" title="<?=  __tr('Submit')  ?>"><?=  __tr('Submit')  ?> </button>
            <!--  /add button -->

            <!--  close button -->
            <button type="button" ng-click="addRoleCtrl.closeDialog()" class="btn btn-default" title="<?=  __tr('Cancel')  ?>"><?=  __tr('Cancel')  ?></button>
            <!--  close button -->
        </div>
        <!-- /Modal footer -->
    </form>
</div>
