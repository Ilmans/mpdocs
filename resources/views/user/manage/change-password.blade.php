<?php
/*
*  Component  : Manage User
*  View       : Change Password  
*  Engine     : ManageUserEngine  
*  File       : change-password.blade.php  
*  Controller : ManageUserChangePasswordController 
----------------------------------------------------------------------------- */ 
?>
<div>
	
	<!-- Modal Heading -->
    <div class="modal-header">
        <h3 class="modal-title"><?= __tr('Change Password of __fullName__', [
                '__fullName__' => '[[ userChangePassword.title ]]'
            ]) ?></h3>
       </div>
    <!-- /Modal Heading -->

    <!--  form action -->
    <form class="lw-form lw-ng-form" 
        name="userChangePassword.[[ userChangePassword.ngFormName ]]" 
        ng-submit="userChangePassword.submit()" 
        novalidate>

        <!-- Modal Body -->
        <div class="modal-body">
            <!--  New Password -->
            <lw-form-field field-for="new_password" label="<?=  __tr( 'New Password' )  ?>"> 
                <input type="password" 
                      class="lw-form-field form-control"
                      name="new_password"
                      ng-minlength="6"
                      ng-maxlength="255"
                      ng-required="true" 
                      ng-model="userChangePassword.changePasswordData.new_password" />
            </lw-form-field>
            <!--  /New Password -->

            <!--  New Password Confirmation -->
            <lw-form-field field-for="new_password_confirmation" label="<?=  __tr( 'New Password Confirmation' )  ?>">
                <input type="password" 
                      class="lw-form-field form-control"
                      name="new_password_confirmation"
                      ng-minlength="6"
                      ng-maxlength="255"
                      ng-required="true" 
                      ng-model="userChangePassword.changePasswordData.new_password_confirmation" />
            </lw-form-field>
            <!--  /New Password Confirmation -->
        </div>
        <!-- /Modal Body -->

        <!-- /Modal footer -->
        <div class="modal-footer">
        	<!--  update password button -->
            <button type="submit" class="btn btn-primary" title="<?=  __tr('Update Password')  ?>"><?=  __tr('Update Password')  ?> <span></span></button>
			<!--  /update password button -->

			<!--  close button -->
            <button type="button" ng-click="userChangePassword.closeDialog()" class="btn btn-default" title="<?=  __tr('Cancel')  ?>"><?=  __tr('Cancel')  ?></button>
            <!--  close button -->
        </div>
		<!-- /Modal footer -->

    </form>
	<!--  /form action -->

</div>