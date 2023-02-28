<?php
/*
*  Component  : User
*  View       : Profile  
*  Engine     : CommonUserEngine.js  
*  File       : profile-edit.blade.php  
*  Controller : UserProfileEditController 
----------------------------------------------------------------------------- */ 
?>


<div ng-controller="UserProfileEditController as profileEditCtrl"
    class="col-md-8 col-xs-12 offset-md-2 lw-login-form-box">

    <!-- lw-section-heading-block -->
    <div class="lw-section-heading-block">
        <!--  main heading  -->
        <h3 class="lw-section-heading">
            <div class="lw-heading"><?=  __tr( 'Profile Update' )  ?></div>
        </h3>
        <!--  /main heading  -->
    </div>
    <!-- / lw-section-heading-block -->

    <div class="shadow p-4 border">

        <form class="lw-form lw-ng-form" name="profileEditCtrl.[[ profileEditCtrl.ngFormName ]]"
            ng-submit="profileEditCtrl.submit()" novalidate>
 
            <div class="lw-form-body">

                <input type="hidden" id="lwUserProfileEditTxtMsg" loading-text="<?= __tr( 'Upload in Process') ?>"
                    file-uploaded-text="<?= __tr('File Uploaded') ?>">

                <!-- thumbnail -->
                <div class="form-group">
                    <div class="lw-thumb-logo">
                        <a href="[[ profileEditCtrl.existingProfilePictureURL ]]" lw-ng-colorbox
                            class="lw-thumb-logo"><img ng-src="[[ profileEditCtrl.existingProfilePictureURL ]]"
                                alt=""></a>
                    </div>
                </div>
                <!-- /thumbnail -->

                <!-- Profile Picture -->
                <lw-form-selectize-field field-for="profile_picture" label="<?= __tr( 'Profile Picture' ) ?>"
                    class="lw-selectize">
                    <span class="badge lw-badge">[[ profileEditCtrl.profileFilesCount ]]</span>
                    <selectize config='profileEditCtrl.imagesSelectConfig' class="lw-form-field" name="profile_picture"
                        ng-model="profileEditCtrl.profileData.profile_picture" options='profileEditCtrl.profileFiles'
                        placeholder="<?= __tr( 'Select Uploaded Image' ) ?>"></selectize>
                    <!-- Upload Images -->

                    <!-- Upload image -->
                    @include('media.upload-button')
                    <!-- / Upload image -->

                </lw-form-selectize-field>
                <!-- /Profile Picture -->

                <div class="form-row">
                    <!--  First Name  -->
                    <div class="col">
                        <lw-form-field field-for="first_name" label="<?=  __tr( 'First Name' )  ?>">
                            <input type="text" class="lw-form-field form-control" name="first_name" ng-required="true"
                                ng-model="profileEditCtrl.profileData.first_name" />
                        </lw-form-field>
                    </div>
                    <!--  First Name  -->

                    <!--  Last Name  -->
                    <div class="col">
                        <lw-form-field field-for="last_name" label="<?=  __tr( 'Last Name' )  ?>">
                            <input type="text" class="lw-form-field form-control" name="last_name" ng-required="true"
                                ng-model="profileEditCtrl.profileData.last_name" />
                        </lw-form-field>
                    </div>
                    <!--  Last Name  -->

                    <!--  User Role  -->
                    <div class="col">
                        <lw-form-field field-for="userRole" label="<?=  __tr( 'User Role' )  ?>">
                            <input type="text" class="lw-form-field form-control" name="userRole" ng-required="true"
                                readonly="true" ng-model="profileEditCtrl.profileData.userRole" />
                        </lw-form-field>
                    </div>
                    <!--  User Role  -->
                </div>

                <div class="form-row">

                    <!-- Address 1-->
                    <div class="col-lg-4">
                        <lw-form-field field-for="address_line_1" label="{!! __tr('Address Line 1') !!}">
                            <input type="text" class=" form-control lw-form-field" name="address_line_1"
                                ng-model="profileEditCtrl.profileData.address_line_1" />
                        </lw-form-field>
                    </div>
                    <!-- /Address 1-->

                    <!-- Address 2 -->
                    <div class="col-lg-4">
                        <lw-form-field field-for="address_line_2" label="{!! __tr('Address Line 2') !!}">
                            <input type="text" class=" form-control lw-form-field" name="address_line_2"
                                ng-model="profileEditCtrl.profileData.address_line_2" />
                        </lw-form-field>
                    </div>
                    <!-- /Address 2 -->

                	
                	<div class="col-lg-4">
                		<lw-form-field field-for="country" label="<?= __tr( 'Country' ) ?>">
	                        <selectize 
	                            config='profileEditCtrl.countrySelectConfig'
	                            class="lw-form-field form-control" 
	                            name="country"
	                            ng-required="true"
	                            ng-model="profileEditCtrl.profileData.country"
	                            options='profileEditCtrl.countries'>
	                        </selectize>
	                    </lw-form-field>
                	</div>
                </div>

            </div>

            <div class="lw-form-footer">


                <a ui-sref="profile" title="<?= __tr('Back') ?>" class="btn btn-secondary">Back</a>

                <button type="submit" class="lw-btn btn btn-primary"
                    title="Update">Update
                </button>

            </div>

        </form>

    </div>
    <!-- image path and name -->
    <script type="text/_template" id="imageListItemTemplate">
        <div class="lw-selectize-item lw-selectize-item-selected">
            <span class="lw-selectize-item-thumb">
               <img src="<%= __tData.path %>"/> 
            </span> 
            <span class="lw-selectize-item-label"><%= __tData.name%></span>
        </div>
    </script>
    <!-- /image path and name -->

    <!-- image path and name -->
    <script type="text/_template" id="imageListOptionTemplate">

        <div class="lw-selectize-item">
            <span class="lw-selectize-item-thumb">
                <img src="<%= __tData.path %>" />
            </span> 
        <span class="lw-selectize-item-label"><%= __tData.name%></span></div>
    </script>
    <!-- /image path and name -->

</div>