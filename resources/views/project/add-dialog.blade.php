<?php 
/*
*  Component  : Project
*  View       : Project Controller
*  Engine     : ProjectEngine  
*  File       : add-dialog.blade.php  
*  Controller : ProjectAddController
----------------------------------------------------------------------------- */
?>
<div>
    
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3><?= __tr('Add Project') ?></h3>
    </div>
    <!-- /Modal Heading -->

    <!-- Add project dialog form -->
    <form class="ui form lw-form lw-ng-form" name="projectAddCtrl.[[projectAddCtrl.ngFormName]]" ng-submit="projectAddCtrl.submit()" 
    novalidate>
        <!-- Modal Body -->
        <div class="modal-body">

            <div class="form-row">

                <div class="col-sm-12-4 col-lg-4 mt-3 lw-btn-file" id="lwDragDropContainer">
                    <div class="lw-drag-drop-image-container p-4 mb-2" >

                        <div class="text-muted text-center" ng-if="!projectAddCtrl.logo_image_url">
                            <?= __tr('Drop logo here') ?>
                        </div>
                        <img class="rounded mx-auto d-block" ng-if="projectAddCtrl.logo_image_url != ''" ng-src="[[projectAddCtrl.logo_image_url]]">
                    </div>

                    <div class="lw-drag-drop-image-container p-4">

                        <div class="text-muted text-center" ng-if="!projectAddCtrl.favicon_image_url">
                            <?= __tr('Drop favicon here') ?>
                        </div>
                        <img class="rounded mx-auto d-block" ng-if="projectAddCtrl.favicon_image_url != ''" ng-src="[[projectAddCtrl.favicon_image_url]]">

                    </div>
                    <input id="lwFileupload" class="hide-till-load" ng-click="upload()" type="file" name="upload-file" title="Project Logo & Favicon">
                </div>

                <div class="col">
                    
                    <!-- Slug -->
                    <lw-form-field field-for="slug" label="<?= __tr('URL Slug') ?>">
                        <input type="text" 
                            class="lw-form-field form-control"
                            ng-model="projectAddCtrl.projectData.slug" 
                            name="slug"
                            ng-required="true"
                        />
                    </lw-form-field>
                    <!-- /Slug -->

                    <!-- Name -->
                    <lw-form-field field-for="name" label="<?= __tr('Name') ?>">
                        <input type="text" 
                            class="lw-form-field form-control" 
                            ng-model="projectAddCtrl.projectData.name" name="name"
                            ng-required="true"/>
                    </lw-form-field>
                    <!-- /Name -->

                    <div class="form-row">

                        <div class="col-6">
                            <!-- Status -->
                            <lw-form-field field-for="status" label="<?= __tr('Status') ?>">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" ng-model="projectAddCtrl.projectData.status" 
                                        ng-true-value="1" ng-false-value="2" class="custom-control-input" id="status">
                                    <label class="custom-control-label" for="status">
                                        <span ng-if="projectAddCtrl.projectData.status == 1"><?= __tr('Active') ?></span>
                                        <span ng-if="projectAddCtrl.projectData.status == 2"><?= __tr('Inactive') ?></span>
                                    </label>
                                </div>
                            </lw-form-field>
                            <!-- /Status -->
                        </div>

                        <div class="col-6">
                            <!-- /Type -->
                            <lw-form-field field-for="type" label="<?= __tr('Type') ?>">
                                <a href ng-if="projectAddCtrl.projectData.type == 1" class="lw-popover-help" lw-popover message="<?= __tr('Any user can read these project articles without logged in.') ?>"><i class="fa fa-question-circle"></i></a>
                                 <a href ng-if="projectAddCtrl.projectData.type == 2" class="lw-popover-help" lw-popover message="<?= __tr('Only logged in user read these project articles.') ?>"><i class="fa fa-question-circle"></i></a>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" ng-model="projectAddCtrl.projectData.type" 
                                        ng-true-value="1" ng-false-value="2" class="custom-control-input" id="type">
                                    <label class="custom-control-label" for="type">
                                        <span ng-if="projectAddCtrl.projectData.type == 1"><?= __tr('Public') ?></span>
                                        <span ng-if="projectAddCtrl.projectData.type == 2"><?= __tr('Private') ?></span>
                                    </label>
                                </div>
                            </lw-form-field>
                            <!-- /Type -->
                        </div>

                    </div>

                </div>

            </div>
            
            <div class="form-row">

                <div class="col">
                    <!-- Language -->
                    <lw-form-field field-for="project_languages"  label="<?= __tr('Languages') ?>">
                        <div class="input-group">
                            <selectize 
                                config="projectAddCtrl.languageSelectize"
                                class="lw-form-field form-control lw-addon-selectize" 
                                id="project_languages" 
                                ng-model="projectAddCtrl.projectData.project_languages" 
                                name="project_languages"
                                ng-required="true"
                                options="projectAddCtrl.languages"
                                placeholder="Select Languages OR ">
                            </selectize>
                            <div class="input-group-append">
                                <button class="btn btn-secondary lw-selectize-addon-btn" ng-show="canAccess('manage.language.write.create')" type="button" ng-click="projectAddCtrl.addNewLanguage()"><i class="fa fa-plus"></i> <?= __tr('Add New Language') ?></button>
                            </div>
                        </div>
                    </lw-form-field>
                    <!-- /Language -->
                </div>

                <div class="col">
                    <!-- Language -->
                    <lw-form-field field-for="primary_language" label="<?= __tr('Primary Language') ?>">
                        <small><?= __tr('(You can not change it later.)') ?></small>
                        <selectize 
                            config="projectAddCtrl.primaryLanguageSelectize"
                            class="lw-form-field form-control" 
                            id="primary_language" 
                            ng-model="projectAddCtrl.projectData.primary_language" 
                            name="primary_language"   
                            ng-required="true"
                            options="projectAddCtrl.primarylanguages">
                        </selectize>
                    </lw-form-field>
                    <!-- /Language -->
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="form-row" ng-if="projectAddCtrl.recent_languages.length > 0">
	        	<div class="col-lg-12">
	        		<?= __tr('Recent Languages : ') ?>
	        		<span class="badge badge-light h6 badge-pointer border mr-1" 
	                	ng-repeat="recent in projectAddCtrl.recent_languages" 
	                	ng-click="projectAddCtrl.selectRecentLanguage(recent.id)" 
	                	ng-bind="recent.name"></span>
	        	</div>
	        	
	        </div>
            
            <!-- Short_Description -->
            <lw-form-field field-for="short_description" label="<?= __tr('Description') ?>">
                <textarea 
                    ng-model="projectAddCtrl.projectData.short_description"
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

        <!-- Modal footer -->
        <div class="modal-footer">
        
            <button type="submit" class="lw-btn btn btn-primary" title="<?= __tr('Add') ?>"><?= __tr('Add') ?></button>

            <button type="button" title="<?= __tr('Cancel') ?>" class="lw-btn btn btn-default" ng-click="projectAddCtrl.closeDialog()"><?= __tr('Cancel') ?></button>
        </div>
        <!-- /Modal footer -->
    </form>
</div>