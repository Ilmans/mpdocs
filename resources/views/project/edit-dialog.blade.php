<?php 
/*
*  Component  : Project
*  View       : Project Controller
*  Engine     : ProjectEngine
*  File       : edit-dialog.blade.php  
*  Controller : ProjectEditController
----------------------------------------------------------------------------- */
?>
<div>

    <!-- Loading (remove the following to stop the loading)-->
    <div class="overlay" ng-show="projectEditCtrl.showLoader">
       <div class="loader"></div>
    </div>
    <!-- end loading -->

    <!-- Modal Heading -->
    <div class="modal-header">
        <h3><?= __tr('Edit Project') ?></h3>
    </div>
    <!-- /Modal Heading -->

    <!-- Add project dialog form -->
    <form class="ui form lw-form lw-ng-form" name="projectEditCtrl.[[projectEditCtrl.ngFormName]]" ng-submit="projectEditCtrl.submit()" novalidate >

        <!-- Modal Body -->
        <div class="modal-body">
            
            <div class="form-row">

                <div class="col-sm-12-4 col-lg-4 mt-3">
                    <div class="lw-btn-file" id="lwDragDropContainer">
                        <div class="lw-drag-drop-image-container p-4 mb-2" >

                            <div class="text-muted text-center" ng-if="!projectEditCtrl.logo_image_url">
                               <?= __tr('Drop logo here') ?>
                            </div>
                            <img class="rounded mx-auto d-block" ng-if="projectEditCtrl.logo_image_url != ''" ng-src="[[projectEditCtrl.logo_image_url]]">
                        </div>

                        <div class="lw-drag-drop-image-container p-4">

                            <div class="text-muted text-center" ng-if="!projectEditCtrl.favicon_image_url">
                                <?= __tr('Drop favicon here') ?>
                            </div>
                            <img class="rounded mx-auto d-block" ng-if="projectEditCtrl.favicon_image_url != ''" ng-src="[[projectEditCtrl.favicon_image_url]]">
                        </div>

                        <input id="lwFileupload" class="hide-till-load" ng-click="upload()" type="file" name="upload-file" title="Project Logo & Favicon">
                    </div>
                    <button type="button" ng-if="projectEditCtrl.logoImageExists" class="lw-btn btn btn-default mt-2" ng-click="projectEditCtrl.deleteMedia(1)"><?= __tr('Delete Logo') ?></button>
                    <button type="button" ng-if="projectEditCtrl.faviconImageExist" class="lw-btn btn btn-default mt-2" ng-click="projectEditCtrl.deleteMedia(2)"><?= __tr('Delete Favicon') ?></button>
                </div>

                <div class="col">

                    <!-- Slug -->
                    <lw-form-field field-for="slug" label="<?= __tr('URL Slug') ?>">
                        <input type="text" 
                            class="lw-form-field form-control"
                            ng-model="projectEditCtrl.projectData.slug" 
                            name="slug"
                            ng-required="true"
                        />
                    </lw-form-field>
                    <!-- /Slug -->
                    
                    <!-- Name -->
                    <lw-form-field field-for="name" label="<?= __tr('Name') ?>">
                        <input type="text" 
                            class="lw-form-field form-control" 
                            ng-model="projectEditCtrl.projectData.name" name="name"
                            ng-required="true"/>
                    </lw-form-field>
                    <!-- /Name -->

                    <div class="form-row">

                        <div class="col-6">
                            <!-- Status -->
                            <lw-form-field field-for="status" label="<?= __tr('Status') ?>">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" ng-model="projectEditCtrl.projectData.status" 
                                        ng-true-value="1" ng-false-value="2" class="custom-control-input" id="status">
                                    <label class="custom-control-label" for="status">
                                        <span ng-if="projectEditCtrl.projectData.status == 1"><?= __tr('Active') ?></span>
                                        <span ng-if="projectEditCtrl.projectData.status == 2"><?= __tr('Inactive') ?></span>
                                    </label>
                                </div>
                            </lw-form-field>
                            <!-- /Status -->
                        </div>

                        <div class="col-6">
                            <!-- /Type -->
                            <lw-form-field field-for="type" label="<?= __tr('Type') ?>">
                                <a href ng-if="projectEditCtrl.projectData.type == 1" class="lw-popover-help" lw-popover message="<?= __tr('Any user can read these project articles without logged in.') ?>"><i class="fa fa-question-circle"></i></a>
                                <a href ng-if="projectEditCtrl.projectData.type == 2" class="lw-popover-help" lw-popover message="<?= __tr('Only logged in user read these project articles.') ?>"><i class="fa fa-question-circle"></i></a>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" ng-model="projectEditCtrl.projectData.type" 
                                        ng-true-value="1" ng-false-value="2" class="custom-control-input" id="type">
                                    <label class="custom-control-label" for="type">
                                        <span ng-if="projectEditCtrl.projectData.type == 1"><?= __tr('Public') ?></span>
                                        <span ng-if="projectEditCtrl.projectData.type == 2"><?= __tr('Private') ?></span>
                                    </label>
                                </div>
                            </lw-form-field>
                            <!-- /Type -->
                        </div>

                    </div>

                </div>

            </div>


            <div class="form-row">
            	<div class="col-6">
                    <!-- Language -->
                    <lw-form-field field-for="project_languages" label="<?= __tr('Additional Languages') ?>">
                        <selectize 
                            config="projectEditCtrl.languageSelectize"
                            class="lw-form-field form-control" 
                            id="project_languages"
                            ng-model="projectEditCtrl.projectData.project_languages" 
                            name="project_languages"
                            options="projectEditCtrl.languages">
                        </selectize>
                    </lw-form-field>
                    <!-- /Language -->
                </div>

                 <div class="col-6">
                    <!-- Language -->
                    <lw-form-field field-for="primary_language_text" label="<?= __tr('Primary Language') ?>">
                        <input type="text"  
                            class="lw-form-field form-control" 
                            ng-model="projectEditCtrl.projectData.primary_language_text" 
                            name="primary_language_text"
                            ng-disabled="true"   
                            ng-required="true">
                    </lw-form-field>
                    <!-- /Language -->
                </div>
            </div>


            <!-- Short Description -->
            <lw-form-field field-for="short_description" label="<?= __tr('Description') ?>">
                <textarea
                    ng-model="projectEditCtrl.projectData.short_description"
                    cols="10" 
                    rows="3"
                    class="lw-form-field form-control"
                    name="short_description" 
                    ng-minlength="10"    
                    ng-maxlength="500" 
                ></textarea>
            </lw-form-field>
            <!-- /Short_Description -->

        </div>
        <!-- /Modal Body -->

        <input type="hidden" id="lwLanguageDeleteConfirm" data-message="<?= __tr( 'All the article related to this language will be deleted as well.') ?>" data-delete-button-text="<?= __tr('Yes, delete it') ?>", success-msg="<?= __tr('Language deleted successfully.') ?>">
        
        <!-- Modal footer -->
        <div class="modal-footer">
            <button type="submit" class="lw-btn btn btn-primary" title="<?= __tr('Update') ?>"><?= __tr('Update') ?></button>
            
            <button type="button" title="<?= __tr('Cancel') ?>" class="lw-btn btn btn-default" ng-click="projectEditCtrl.closeDialog()"><?= __tr('Cancel') ?></button>
        </div>
        <!-- /Modal footer -->
    </form>
</div>