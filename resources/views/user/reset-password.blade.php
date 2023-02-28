<?php
/*
*  Component  : User
*  View       : Reset Password  
*  Engine     : UserEngine.js  
*  File       : reset-password.blade.php  
*  Controller : UserResetPasswordController 
----------------------------------------------------------------------------- */ 
?>
<div ng-controller="UserResetPasswordController as resetPasswordCtrl" class="col-md-8 col-xs-12 offset-md-2 lw-login-form-box">
    
    <div class="lw-section-heading-block">
        <!--  main heading  -->
        <h3 class="lw-section-heading"><div class="lw-heading"><?=  __tr( 'Reset Password' )  ?></div></h3>
        <!--  /main heading  -->
    </div>

    <div class="shadow p-4 border">
        <!--  form action  -->
        <form class="lw-form lw-ng-form" 
            name="resetPasswordCtrl.[[ resetPasswordCtrl.ngFormName ]]" 
            ng-submit="resetPasswordCtrl.submit()" 
            novalidate>

            <div class="lw-form-body">

                <!--  Email  -->
                <lw-form-field field-for="email" label="<?=  __tr( 'Email' )  ?>"> 
                    <input type="email" 
                    class="lw-form-field form-control"
                    name="email"
                    ng-required="true" 
                    ng-model="resetPasswordCtrl.userData.email" />
                </lw-form-field>
                <!--  /Email  -->

                <div class="form-row">

                    <div class="col">

                        <!--  Password  -->
                        <lw-form-field field-for="password" label="<?=  __tr( 'Password' )  ?>"> 
                            <input type="password" 
                                class="lw-form-field form-control"
                                name="password"
                                ng-minlength="6"
                                ng-maxlength="30"
                                ng-required="true" 
                                ng-model="resetPasswordCtrl.userData.password" />
                        </lw-form-field>
                        <!--  /Password  -->

                    </div>

                    <div class="col">

                        <!--  Password Confirmation  -->
                        <lw-form-field field-for="password_confirmation" label="<?=  __tr( 'Password Confirmation' )  ?>"> 
                            <input type="password" 
                                class="lw-form-field form-control"
                                name="password_confirmation"
                                ng-minlength="6"
                                ng-maxlength="30"
                                ng-required="true" 
                                ng-model="resetPasswordCtrl.userData.password_confirmation" />
                        </lw-form-field>
                        <!--  /Password Confirmation  -->

                    </div>

                </div>

            </div>
            
            <!--  submit button  -->
            <div class="lw-form-footer">
                <button type="submit" class="lw-btn btn btn-primary" title="<?=  __tr('Reset Password')  ?>"><?=  __tr('Reset Password')  ?></button>
            </div>
            <!--  /submit button  -->

        </form>
        <!--  /form action  -->
    </div>
    
</div>