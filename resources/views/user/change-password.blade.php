<?php
/*
*  Component  : Change Password
*  View       : Change Password View  
*  Engine     : CommonUserEngine  
*  File       : change-password.blade.php  
*  Controller : UserChangePasswordController 
----------------------------------------------------------------------------- */ 
?>
<div ng-controller="UserChangePasswordController as updatePasswordCtrl">
    
    <!-- /error notification -->
    <div class="col-md-8 col-xs-12 offset-md-2 lw-login-form-box">
        <div class="lw-section-heading-block">
        <!--  main heading  -->
            <h3 class="lw-section-heading"> 
            <div class="lw-heading"><?=  __tr( 'Change Password' )  ?></div></h3>
        <!--  /main heading  -->
    </div>

    <div class="shadow p-4 border">

        <form class="lw-form lw-ng-form" 
            name="updatePasswordCtrl.[[ updatePasswordCtrl.ngFormName ]]" 
            ng-submit="updatePasswordCtrl.submit()" 
            novalidate>

            <div class="lw-form-body">

                <!--  Current Password -->
                <lw-form-field field-for="current_password" label="<?=  __tr( 'Current Password' )  ?>"> 
                    <input type="password" 
                        class="lw-form-field form-control"
                        name="current_password"
                        ng-minlength="6"
                        ng-maxlength="30"
                        ng-required="true" 
                        autofocus
                        ng-model="updatePasswordCtrl.userData.current_password" />
                </lw-form-field>
                <!--  /Current Password -->

                <div class="form-row">

                    <div class="col">
                        <!--  New Password -->
                        <lw-form-field field-for="new_password" label="<?=  __tr( 'New Password' )  ?>"> 
                            <input type="password" 
                                class="lw-form-field form-control"
                                name="new_password"
                                ng-minlength="6"
                                ng-maxlength="30"
                                ng-required="true" 
                                ng-model="updatePasswordCtrl.userData.new_password" />
                        </lw-form-field>
                        <!--  /New Password -->
                    </div>

                    <div class="col">

                        <!--  New Password Confirmation -->
                        <lw-form-field field-for="new_password_confirmation" label="<?=  __tr( 'New Password Confirmation' )  ?>">
                            <input type="password" 
                                class="lw-form-field form-control"
                                name="new_password_confirmation"
                                ng-minlength="6"
                                ng-maxlength="30"
                                ng-required="true" 
                                ng-model="updatePasswordCtrl.userData.new_password_confirmation" />
                        </lw-form-field>
                        <!--  /New Password Confirmation -->

                    </div>

                </div>

            </div>

            <!--  update button  -->
            <div class="lw-form-footer">
                <button type="submit" class="lw-btn btn btn-primary" title="<?=  __tr('Update Password')  ?>"><?=  __tr('Update Password')  ?></button>
            </div>
            <!--  /update button  -->

        </form>

    </div>

</div>