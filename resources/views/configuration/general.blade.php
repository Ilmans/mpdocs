<?php
/*
*  Component  : Configuration
*  View       : General Dialog
*  Engine     : ConfigurationEngine  
*  File       : general.blade.php  
*  Controller : GeneralDialogController 
----------------------------------------------------------------------------- */ 
?>

<div class="col-md-12 col-xs-12 lw-login-form-box">

    <div class="lw-section-heading-block">
        <!--  main heading  -->
        <h3 class="lw-section-heading">
            <div class="lw-heading"><?=  __tr( 'General Settings' )  ?></div>
        </h3>
        <!--  /main heading  -->
    </div>

    <!-- Loading (remove the following to stop the loading)-->
    <div class="overlay" ng-if="generalCtrl.pageStatus == false">
        <div class="loader"></div>
    </div>
    <!-- end loading -->

    <div ng-include src="'lw-settings-update-reload-button-template.html'"></div>

    <input type="hidden" id="lwGeneralSettingTxtMsg" loading-text="<?= __tr( 'Upload in Process') ?>" logo-empty-file-text="<?= __tr('Only PNG images are expected') ?>"
        file-uploaded-text="<?= __tr('File Uploaded') ?>">

    <div class="shadow p-4 border">

        <!--  form action  -->
        <form class="lw-form lw-ng-form" name="generalCtrl.[[ generalCtrl.ngFormName ]]" ng-submit="generalCtrl.submit()"
            novalidate>

            <div class="lw-form-body">
                
                    <!-- Name -->
                    <lw-form-field field-for="name" label="<?= __tr( 'Website Name' ) ?>">
                        <input type="text" class="lw-form-field form-control" autofocus name="name" ng-required="true" min="2"
                            max="30" ng-model="generalCtrl.editData.name" />
                    </lw-form-field>
                    <!-- Name -->
            
                    <!-- Select logo_image -->
                    <div class="form-group">
                        <fieldset class="lw-fieldset-2">
                            <legend class="lw-fieldset-legend-font">
                                <?= __tr('Favicon & Logo') ?>
                            </legend>
            
                            <!-- Upload image -->
                            <div class="text-center">
                                @include('media.upload-button')
                            </div>
                            <!-- / Upload image -->
            
                            <div class="alert alert-info mt-2">
                                <strong>Note : </strong>Recommended height for Logo is 50 pixels.
                            </div>
            
                            <div class="form-row">
                                <div class="col">
                                    <!-- New Favicon image -->
                                    <lw-form-selectize-field field-for="favicon_image" label="<?= __tr( 'New Favicon' ) ?>"
                                        class="lw-selectize"><span class="badge lw-badge">[[ generalCtrl.faviconFilesCount ]]</span>
                                        <selectize config='generalCtrl.imagesSelectConfig' class="lw-form-field"
                                            name="favicon_image" ng-model="generalCtrl.editData.favicon_image"
                                            options='generalCtrl.faviconFiles'
                                            placeholder="<?= __tr( 'Only ICO images are allowed' ) ?>"></selectize>
                                    </lw-form-selectize-field>
                                    <!-- New Favicon image -->
                                </div>
            
                                <div class="col">
                                    <!-- New Logo image -->
                                    <lw-form-selectize-field field-for="logo_image" label="<?= __tr( 'New Logo' ) ?>"
                                        class="lw-selectize"><span class="badge lw-badge">[[ generalCtrl.logoFilesCount ]]</span>
                                        <selectize config='generalCtrl.imagesSelectConfig' class="lw-form-field" name="logo_image"
                                            ng-change="generalCtrl.checkLogo(1)" ng-model="generalCtrl.editData.logo_image"
                                            options='generalCtrl.logoFiles'
                                            placeholder="<?= __tr( 'Only PNG images are allowed' ) ?>"></selectize>
            
                                    </lw-form-selectize-field>
                                    <!-- New Logo image -->
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <!--/ Select logo_image -->
            
                    <div class="form-row">
            
                        <div class="col">
                            <!-- System Email -->
                            <lw-form-field field-for="business_email" title="<?= __tr('Will be used for the from email.') ?>"
                                label="<?= __tr( 'System Email' ) ?>">
                                <input type="email" class="lw-form-field form-control" name="business_email" ng-required="true"
                                    ng-model="generalCtrl.editData.business_email" />
                            </lw-form-field>
                            <!-- System Email -->
                        </div>
            
                        <div class="col">
                            <!-- contect Email -->
                            <lw-form-field field-for="contact_email" title="<?= __tr('Will be used for contact purpuse.') ?>"
                                label="<?= __tr( 'Contact Email' ) ?>">
                                <input type="email" class="lw-form-field form-control" name="contact_email" ng-required="true"
                                    ng-model="generalCtrl.editData.contact_email" />
                            </lw-form-field>
                            <!-- contect Email -->
                        </div>
                    </div>
            
                    <fieldset class="lw-fieldset-2">
            
                        <legend class="lw-fieldset-legend-font"><?= __tr('Login Settings') ?></legend>
            
                        <!-- Enable LivelyWorks Credit Info -->
                        <lw-form-checkbox-field field-for="enable_login_attempt" label="<?= __tr( 'Enable Login Attempts Restriction' ) ?>"
                            advance="true">
                            <input type="checkbox" class="lw-form-field js-switch" name="enable_login_attempt"
                                ng-model="generalCtrl.editData.enable_login_attempt" ui-switch="" />
                        </lw-form-checkbox-field>
                        <!-- /Enable LivelyWorks Credit Info -->
            
                        <div ng-if="generalCtrl.editData.enable_login_attempt == true">
            
                            <div class="form-group">
                                <!-- Show Captcha after login attempt -->
                                <lw-form-field field-for="show_captcha" label="<?= __tr( 'Show captcha after' ) ?>">
                                    <div class="input-group">
                                        <input type="number" class="lw-form-field form-control" autofocus name="show_captcha"
                                            ng-required="true" min="1" ng-model="generalCtrl.editData.show_captcha" />
                                        <div class="input-group-append">
                                            <span class="input-group-text"
                                                id="basic-addon2"><?= __tr(' failed login attempts') ?></span>
                                        </div>
                                    </div>
            
                                </lw-form-field>
                                <!-- /Show Captcha after login attempt -->
                            </div>
                        </div>
            
                        <div class="form-row">
                            <div class="col">
                                <!-- Google Recaptcha Site Key -->
                                <lw-form-field field-for="recaptcha_site_key" label="<?= __tr( 'Google Recaptcha Site Key' ) ?>"
                                    ng-if="generalCtrl.editData.enable_login_attempt == true">
                                    <input type="text" class="lw-form-field form-control" name="recaptcha_site_key"
                                        ng-required="true" ng-model="generalCtrl.editData.recaptcha_site_key" />
                                </lw-form-field>
                                <!-- Google Recaptcha Site Key -->
                            </div>
            
                            <div class="col">
                                <!-- Google Recaptcha Secret Key -->
                                <lw-form-field field-for="recaptcha_secret_key" label="<?= __tr( 'Google Recaptcha Secret Key' ) ?>"
                                    ng-if="generalCtrl.editData.enable_login_attempt == true">
                                    <input type="text" class="lw-form-field form-control" name="recaptcha_secret_key"
                                        ng-required="true" ng-model="generalCtrl.editData.recaptcha_secret_key" />
                                </lw-form-field>
                                <!-- Google Recaptcha Secret Key -->
                            </div>
                        </div>
                    </fieldset>
            
                    <div class="form-row">
                        <div class="col">
                            <!-- Enable LivelyWorks Credit Info -->
                            <lw-form-checkbox-field field-for="enable_credit_info" label="<?= __tr( 'Enable Credit Info' ) ?>"
                                advance="true">
                                <input type="checkbox" class="lw-form-field js-switch" name="enable_credit_info"
                                    ng-model="generalCtrl.editData.enable_credit_info" ui-switch="" />
                            </lw-form-checkbox-field>
                            <!-- /Enable LivelyWorks Credit Info -->

                            <div class="alert alert-info">
                                <span ng-if="generalCtrl.editData.enable_credit_info">
                                    Show
                                </span>
                                <span ng-if="!generalCtrl.editData.enable_credit_info">
                                    Hide
                                </span>
                                Software Development Credit Information in Footer.
                                <span ng-if="generalCtrl.editData.enable_credit_info">
                                    Thank You. <i class="fa fa-smile-o fa-2x"></i>
                                </span>
                                <span ng-if="!generalCtrl.editData.enable_credit_info">
                                    anyway, Thank You. <i class="fa fa-frown-o fa-2x"></i>
                                </span>
                            </div>
                        </div>
            
                        <div class="col">
                            <lw-form-checkbox-field field-for="restrict_user_email_update"
                                label="<?= __tr( 'Restrict Users From Updating Email' ) ?>">
                                <input type="checkbox" class="lw-form-field js-switch" name="restrict_user_email_update"
                                    ng-model="generalCtrl.editData.restrict_user_email_update" ui-switch="" />
                            </lw-form-checkbox-field>
                        </div>
                    </div>
            
                    <!-- Addition Footer Text After Name -->
                    <lw-form-field field-for="footer_text" label="<?= __tr( 'Additional Footer Text After Name & Copyright' ) ?>">
                        <input type="footer_text" class="lw-form-field form-control" autofocus name="footer_text"
                            ng-model="generalCtrl.editData.footer_text" />
                    </lw-form-field>
                    <!-- /Addition Footer Text After Name -->
            </div>

            <div class="lw-form-footer">
                <button type="submit" class="btn btn-primary" title="<?= __tr('Update') ?>">
                    <?= __tr('Update') ?></button>
            </div>

        </form>
        <!--  /form action  -->
        
    </div>

    <!-- New logo drop down list item template -->
    <script type="text/_template" id="imageListItemTemplate">
        <div>
            <span class="lw-selectize-item lw-selectize-item-selected"><img src="<%= __tData.path %>"/> </span> <span class="lw-selectize-item-label"><%= __tData.name%></span>
        </div>
    </script>
    <!-- /New logo drop down list item template -->

    <!-- New logo drop down list options template -->
    <script type="text/_template" id="imageListOptionTemplate">
        <div class="lw-selectize-item">
            <span class="lw-selectize-item-thumb"><img src="<%= __tData.path %>"/> </span> <span class="lw-selectize-item-label"><%= __tData.name%></span>
        </div>
    </script>
    <!-- /New logo drop down list options template -->

</div>