<?php
/*
*  Component  : Manage User
*  View       : Add User  
*  Engine     : ManageUserEngine  
*  File       : add-dialog.blade.php  
*  Controller : EditUserDialogController as EditUserDialogCtrl 
----------------------------------------------------------------------------- */ 
?>
<div>
    
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3 class="modal-title"><?=  __tr( 'Edit User' )  ?></h3>
    </div>
    <!-- /Modal Heading -->

    <!--  form action -->
    <form class="lw-form lw-ng-form" 
        name="EditUserDialogCtrl.[[ EditUserDialogCtrl.ngFormName ]]" 
        ng-submit="EditUserDialogCtrl.submit()" 
        novalidate>

        <!-- Modal Body -->
        <div class="modal-body">

            <div class="form-row">
                <!--  First Name  -->
                <div class="col">
                    <lw-form-field field-for="first_name" label="<?=  __tr( 'First Name' )  ?>"> 
                        <input type="text" 
                          class="lw-form-field form-control"
                          name="first_name"
                          ng-required="true"
                          ng-minlength="2"
                          ng-maxlength="45"
                          ng-model="EditUserDialogCtrl.userData.first_name" />
                    </lw-form-field>
                </div>
                <!--  /First Name  -->

                <!--  Last Name  -->
                <div class="col">
                    <lw-form-field field-for="last_name" label="<?=  __tr( 'Last Name' )  ?>"> 
                        <input type="text" 
                          class="lw-form-field form-control"
                          name="last_name"
                          ng-required="true" 
                          ng-minlength="2"
                          ng-maxlength="45"
                          ng-model="EditUserDialogCtrl.userData.last_name" />
                    </lw-form-field>
                </div>
                <!--  /Last Name  -->

                <!-- User Roles -->
                <div class="col">
                    <lw-form-field field-for="role" label="<?= __tr( 'Role' ) ?>"> 
                       <select class="form-control" 
                            name="role" ng-model="EditUserDialogCtrl.userData.role" ng-options="userRole.id as userRole.name for userRole in EditUserDialogCtrl.userRoles" ng-required="true">
                            <option value='' disabled selected><?=  __tr('-- Select Role --')  ?></option>
                        </select> 
                    </lw-form-field>
                </div>
                <!-- /User Roles-->
            </div>

			<div class="form-row">
	            <!--  Username  -->
                <div class="col">
    	            <lw-form-field field-for="username" label="<?=  __tr( 'Username' )  ?>"> 
    	                <input type="text" 
    	                    class="lw-form-field form-control"
    	                    name="username"
    	                    ng-required="true"
    	                    ng-maxlength="45" 
    	                    ng-minlength="2"
    	                    ng-model="EditUserDialogCtrl.userData.username" />
    	            </lw-form-field>
                </div>
	            <!--  /Username  -->
	            
	            <!--  Email  -->
                <div class="col">
    	            <lw-form-field field-for="email" label="<?=  __tr( 'Email' )  ?>"> 
    	                <input type="email" 
    	                    class="lw-form-field form-control"
    	                    name="email"
    	                    ng-maxlength="255"
    	                    ng-required="true"
    	                    ng-model="EditUserDialogCtrl.userData.email" />
    	            </lw-form-field>
                </div>
	            <!--  /Email  -->
			</div>
            
            <!-- Locked -->
            <lw-form-checkbox-field field-for="is_active" label="<?= __tr( 'Active' ) ?>" advance="true" lw-toggle-label="true" v-label="<?= __tr( 'Active' ) ?>" off-label="<?= __tr( 'Inactive' ) ?>">
                <input type="checkbox" 
                    class="lw-form-field js-switch"
                    name="is_active"
                    ng-model="EditUserDialogCtrl.userData.is_active"
                    ui-switch="" />
            </lw-form-checkbox-field>
            <!-- /Locked -->

        </div>
        <!-- /Modal Body -->

        <!-- /Modal footer -->
        <div class="modal-footer">
            <!--  add button -->
            <button type="submit" class="btn btn-primary" title="<?=  __tr('Update')  ?>"><?=  __tr('Update')  ?> <span></span></button>
            <!--  /add button -->

            <!--  close button -->
            <button type="button" ng-click="EditUserDialogCtrl.closeDialog()" class="btn btn-default" title="<?=  __tr('Cancel')  ?>"><?=  __tr('Cancel')  ?></button>
            <!--  close button -->
        </div>
        <!-- /Modal footer -->
    </form>
    <!-- /form action -->

</div>