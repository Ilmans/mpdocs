<?php	
/*
*  Component  : Version
*  View       : Version Controller
*  Engine     : VersionEngine  
*  File       : version.list.blade.php  
*  Controller : VersionAddController 
----------------------------------------------------------------------------- */ 
?>
<div>

    <!-- Modal Heading -->
    <div class="modal-header">
        <h3><?= __tr('Create New Version / Group') ?></h3>
    </div>
    <!-- /Modal Heading -->

    <!-- Add version dialog form -->
    <form class="ui form lw-form lw-ng-form" name="versionAddCtrl.[[versionAddCtrl.ngFormName]]" ng-submit="versionAddCtrl.submit()" novalidate >

        <!-- Modal Body -->
        <div class="modal-body">
            <div class="form-row">
                <div class="col">
                    <!-- Slug -->
                    <lw-form-field field-for="slug" label="<?= __tr('URL Slug') ?>">
                        <input type="text" 
                            class="lw-form-field form-control"
                            ng-model="versionAddCtrl.versionData.slug" 
                            name="slug"
                            ng-required="true"
                        />
                    </lw-form-field>
                    <!-- /Slug -->
                </div>
            </div>
        	<div class="form-row">
        		<div class="col" ng-show="versionAddCtrl.existingVersions.length > 0">
        			<lw-form-field field-for="copy_of_version" label="<?= __tr('Extends From') ?>">
	        			<select class="form-control" ng-model="versionAddCtrl.versionData.copy_of_version">
	        				<option value="">-- select Version / Group --</option>
							<option ng-repeat="ver in versionAddCtrl.existingVersions" value="[[ver.id]]">[[ver.version]]</option>
						</select>
					</lw-form-field>
                </div>
                <div class="col">
            		<!-- Version -->
		            <lw-form-field field-for="version" label="<?= __tr('Version / Group') ?>" v-label="<?= __tr('Version / Group ') ?>">
		                <input type="text" 
		                    class="lw-form-field form-control"
		                    ng-model="versionAddCtrl.versionData.version" 
		                    name="version"   
		                    ng-required="true"          
		                    ng-maxlength="10"
		                    />
		            </lw-form-field>
		            <!-- /Version -->
            	</div>
        	</div>

            <div class="form-row">
            	<div class="col-6">
            		<lw-form-field field-for="status" v-label="">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" id="markAsPrimary" ng-model="versionAddCtrl.versionData.is_primary" ng-true-value="1" ng-false-value="2" ng-change="versionAddCtrl.changeStatus(versionAddCtrl.versionData.is_primary)">
							<label class="custom-control-label" for="markAsPrimary"><?= __tr('Mark as primary') ?></label>
						</div>
					</lw-form-field>
            	</div>

            	<div class="col-6">
					<!-- Status -->
                    <lw-form-field field-for="status" label="<?= __tr('Status ') ?>">
                        <div class="custom-control custom-switch custom-control-inline">
                            <input type="checkbox" ng-model="versionAddCtrl.versionData.status" 
                                ng-true-value="1" ng-false-value="2" class="custom-control-input" id="status" ng-disabled="versionAddCtrl.versionData.is_primary == 1" ng-required="true">
                            <label class="custom-control-label" for="status">
                                <span ng-if="versionAddCtrl.versionData.status == 1">Active</span>
                                <span ng-if="versionAddCtrl.versionData.status != 1">Inactive</span>
                            </label>
                        </div>
                    </lw-form-field>
                    <!-- /Status -->
            	</div>
            </div>

        </div>
        <!-- /Modal Body -->

        <!-- Modal footer -->
        <div class="modal-footer">

            <button type="submit" class="lw-btn btn btn-primary" title="<?= __tr('Create') ?>">
            <?= __tr('Create') ?></button>
            
            <!-- Back  -->
            <button ng-click="versionAddCtrl.closeDialog()" title="<?= __tr('Close') ?>" class="lw-btn btn btn-default"><?= __tr('Close') ?></button>
            <!-- /Back  -->

        </div>
        <!-- /action -->

    </form>

</div>