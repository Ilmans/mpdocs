<?php
/*
*  Component  : User
*  View       : Login
*  Engine     : UserEngine.js
*  File       : login.blade.php
*  Controller : UserLoginController
----------------------------------------------------------------------------- */
?>
<div ng-controller="UserLoginController as loginCtrl" class="col-md-8 col-xs-12 offset-md-2 lw-login-form-box" ng-show="loginCtrl.request_completed == true">

    <div class="lw-section-heading-block">
        <!--  main heading  -->
        <h3 class="lw-section-heading">@section('page-title', __tr( 'Account Access' ))
            <div class="lw-heading"><?=  __tr( 'Account Access' )  ?></div>
        </h3>
        <!--  /main heading  -->
    </div>

    <!-- /error notification -->
    <div class="shadow p-4 border">

        <!-- error notification -->
        <div ng-show="loginCtrl.errorMessage && loginCtrl.accountDeleted" class="alert alert-danger" role="alert">
            <div class="ui bottom error message animated fadeIn">
                <i class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp;<span
                    ng-bind="loginCtrl.errorMessage"></span>
            </div>
        
        </div>
        
        <!--  form action  -->
        <form class="lw-form lw-ng-form form-horizontal omb_login" name="loginCtrl.[[ loginCtrl.ngFormName ]]"
            ng-submit="loginCtrl.submit()" novalidate>
            <div class="alert bg-info text-white">Your activity login and read is saved in our system, If your account is detected sharing access to other people, we will disable it.</div>
            <div class="lw-form-body">

                <!--  Email  -->
                <lw-form-field field-for="emailOrUsername" label="<?=  __tr( 'Username / Email' )  ?>">
                    <input type="text" class="lw-form-field form-control" name="emailOrUsername"
                        placeholder="<?= __tr('Enter username / email') ?>" ng-model="loginCtrl.loginData.emailOrUsername"
                        ng-required="true" />
                </lw-form-field>
                <!--  Email  -->

                <!--  Password  -->
                <lw-form-field field-for="password" label="<?=  __tr( 'Password' )  ?>">
                    <div class="input-group">
                        <input type="password" class="lw-form-field form-control" name="password" ng-minlength="6"
                            ng-maxlength="30" autocomplete="password" ng-required="true" placeholder="<?= __tr('Enter password') ?>"
                            ng-model="loginCtrl.loginData.password" />
                        <div class="input-group-append">
                            <a class="input-group-text" ui-sref="forgot_password"
                                title="<?=  __tr('Forgot Password?')  ?>"><?=  __tr('Forgot Password?')  ?></a>
                        </div>
                    </div>
                </lw-form-field>
                <!--  Password  -->

                <div ng-if="loginCtrl.show_captcha == true">
                    <lw-form-field class="lw-recaptcha" field-for="recaptcha" v-label="Captcha"
                        label="<?= __tr('Verify you are not robot') ?>">
                        <lw-recaptcha class="lw-form-field g-recaptcha" ng-model='loginCtrl.loginData.recaptcha'
                            name="recaptcha" sitekey="[[loginCtrl.site_key]]" ng-required="loginCtrl.show_captcha == true">
                        </lw-recaptcha>
                    </lw-form-field>
                </div>
            </div>

            <div class="lw-form-footer">
                <button type="submit" class="lw-btn btn btn-primary lw-responsive-btn"
                    title="<?=  __tr('Login')  ?>"><?=  __tr('Let me in')  ?> <i class="fa fa-sign-in-alt"></i></button>
            </div>

        </form>
        <!--  /form action  -->

    </div>

</div>