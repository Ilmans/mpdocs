<?php
/*
*  Component  : User
*  View       : Change Email  
*  Engine     : CommonUserEngine.js  
*  File       : change-email.blade.php  
*  Controller : UserChangeEmailController 
----------------------------------------------------------------------------- */ 
?>
<div ng-controller="UserChangeEmailController as changeEmailCtrl">


    <!-- /error notification -->
    <div class="col-md-8 col-xs-12 offset-md-2 lw-login-form-box">
        
        <div class="lw-section-heading-block">
            <!--  main heading  -->
            <h3 class="lw-section-heading">
                <div class="lw-heading"><?=  __tr( 'Update Email' )  ?></div>
            </h3>
            <!--  /main heading  -->
        </div>

        <div ng-if="!changeEmailCtrl.activationRequired && changeEmailCtrl.requestSuccess">
            <div class="alert alert-success" role="alert"><strong><?=  __tr( 'Well done!' )  ?></strong>
                [[ changeEmailCtrl.successMessage ]]</div>
        </div>

        <div ng-if="publicCtrl.isEmptyUserEmail" class="lw-form alert alert-info">
            <?= __tr("You didn't set your email address") ?>
        </div>

        <!--  /note  -->

        <div class="shadow p-4 border">
            <!--  form action  -->
            <form class="lw-form lw-ng-form" name="changeEmailCtrl.[[ changeEmailCtrl.ngFormName ]]"
                ng-submit="changeEmailCtrl.submit()" novalidate>

                <div class="lw-form-body">

                    <div class="form-row">

                        <div class="col">
                            <div class="form-group ">
                                <label class="control-label"> <?=  __tr( 'Current Email' )  ?></label>
                                <input readonly="" type="text" class="form-control" ng-model="changeEmailCtrl.current_email">
                            </div>
                            <!--  /Current Email  -->
                        </div>

                        <div class="col">

                            <!--  Current Password  -->
                            <lw-form-field field-for="current_password" label="<?=  __tr( 'Current Password' )  ?>">
                                <input type="password" class="lw-form-field form-control" name="current_password" min-length="6"
                                    max-length="30" ng-required="true" autofocus
                                    ng-model="changeEmailCtrl.userData.current_password" autofocus />
                            </lw-form-field>
                            <!--  /Current Password  -->

                        </div>

                    </div>

                    <!--  New Email  -->
                    <lw-form-field field-for="new_email" label="<?=  __tr( 'New Email' )  ?>">
                        <input type="email" class="lw-form-field form-control" name="new_email" ng-required="true"
                            ng-model="changeEmailCtrl.userData.new_email" />
                    </lw-form-field>
                    <!--  /New Email  -->

                </div>

                <!--  update button  -->
                <div class="lw-form-footer">
                    <button type="submit" class="lw-btn btn btn-primary"
                        title="<?=  __tr('Update Request')  ?>"><?=  __tr('Update Email')  ?> </button>
                </div>
                <!--  /update button  -->
            </form>
            <!--  /form action  -->

        </div>

    </div>