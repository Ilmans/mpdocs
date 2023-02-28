<?php
/*
*  Component  : User
*  View       : Profile  
*  Engine     : CommonUserEngine.js  
*  File       : manage-profile.blade.php  
*  Controller : UserProfileController 
----------------------------------------------------------------------------- */ 
?>
<div ng-controller="UserProfileController as profileCtrl" class="col-md-8 col-xs-12 offset-md-2 lw-login-form-box">

    <!-- /error notification -->

    <div class="lw-section-heading-block">
        <!--  main heading  -->
        <h3 class="lw-section-heading">
            <div class="lw-heading"><?=  __tr( 'Profile' )  ?></div>
        </h3>
        <!--  /main heading  -->
    </div>

    <div ng-if="profileCtrl.request_completed" class="shadow p-4 border">

        <div class="media">
            <span class="avatar avatar-xxl mr-5">
                <div class="lw-thumb-logo">
                    <a href="[[ profileCtrl.existingProfilePictureURL ]]" lw-ng-colorbox
                        class="lw-thumb-logo"><img ng-src="[[ profileCtrl.existingProfilePictureURL ]]"
                            alt=""></a>
                </div>
            </span>
            <div class="media-body">
                <h4 class="m-0">[[ profileCtrl.profileData.first_name ]] [[
                    profileCtrl.profileData.last_name ]]</h4>
                <p class="text-muted mb-0">Role: [[ profileCtrl.profileData.userRole ]]</p>
                <p class="text-muted mb-0">Email: [[ profileCtrl.profileData.email ]]</p>
                <p class="mb-0">
                    [[ profileCtrl.profileData.address_line_1 ]]
                </p>
                <p class="mb-0">[[ profileCtrl.profileData.address_line_2 ]]</p>
                <p class="mb-0">[[ profileCtrl.profileData.country ]]</p>
            </div>
        </div>

        <!--  edit profile button  -->
        <div class="lw-page-footer">

            <a ui-sref="profileEdit" title="<?= __tr('Edit') ?>"
                class="btn btn-primary"><?=  __tr('Edit')  ?></a>
        </div>
        <!--  /edit profile button  -->

    </div>

</div>