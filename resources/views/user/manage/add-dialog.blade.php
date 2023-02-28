<?php
/*
*  Component  : Manage User
*  View       : Add User  
*  Engine     : ManageUserEngine  
*  File       : add-dialog.blade.php  
*  Controller : AddUserDialogController 
----------------------------------------------------------------------------- */ 
?>
<div ng-controller="AddUserDialogController as AddUserDialogCtrl" class="lw-dialog">
	
	<!-- Modal Heading -->
    <div class="modal-header">
        <h3 class="modal-title"><?=  __tr( 'Add New User' )  ?></h3>
    </div>
    <!-- /Modal Heading -->

    <!--  form action -->
    <form class="lw-form lw-ng-form" 
        name="AddUserDialogCtrl.[[ AddUserDialogCtrl.ngFormName ]]" 
        ng-submit="AddUserDialogCtrl.submit()" 
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
                          ng-model="AddUserDialogCtrl.userData.first_name" />
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
                          ng-model="AddUserDialogCtrl.userData.last_name" />
                    </lw-form-field>
                </div>
                <!--  /Last Name  -->

                <!-- User Roles -->
                <div class="col">
                    <lw-form-field field-for="role" label="<?= __tr( 'Role' ) ?>" ng-if="AddUserDialogCtrl.showRoleSelectBox == true"> 
                       <select class="form-control" 
                            name="role" ng-model="AddUserDialogCtrl.userData.role" ng-options="userRole.id as userRole.name for userRole in AddUserDialogCtrl.userRoles" ng-required="true">
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
    		                ng-model="AddUserDialogCtrl.userData.username" />
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
    						ng-model="AddUserDialogCtrl.userData.email" />
    	            </lw-form-field>
                </div>
                <!--  /Email  -->
			</div>

			<div class="form-row">
	            <!--  Password  -->
                <div class="col">
    	            <lw-form-field field-for="password" label="<?=  __tr( 'Password' )  ?>"> 
    	                <input type="password" 
    	                  class="lw-form-field form-control"
    	                  name="password"
    	                  ng-minlength="6"
    	                  ng-maxlength="255"
    	                  ng-required="true" 
    	                  ng-model="AddUserDialogCtrl.userData.password" />
    	            </lw-form-field>
                </div>
                <!--  /Password  -->

	            <!--  Password Confirmation  -->
                <div class="col">
    	            <lw-form-field field-for="password_confirmation" label="<?=  __tr( 'Password Confirmation' )  ?>"> 
    	                <input type="password" 
    	                   class="lw-form-field form-control"
    	                   name="password_confirmation"
    	                   ng-minlength="6"
    	                   ng-maxlength="255"
    	                   ng-required="true" 
    	                   ng-model="AddUserDialogCtrl.userData.password_confirmation" />
    	            </lw-form-field>
                </div>
	            <!--  /Password Confirmation  -->
			</div>

		</div>
        <!-- /Modal Body -->

        <!-- /Modal footer -->
        <div class="modal-footer">
        	<!--  add button -->
            <button type="submit" class="btn btn-primary" title="<?=  __tr('Add')  ?>"><?=  __tr('Add')  ?> <span></span></button>
			<!--  /add button -->

			<!--  close button -->
            <button type="button" ng-click="AddUserDialogCtrl.closeDialog()" class="btn btn-default" title="<?=  __tr('Cancel')  ?>"><?=  __tr('Cancel')  ?></button>
            <!--  close button -->
        </div>
        <!-- /Modal footer -->
    </form>
	<!-- /form action -->

</div>