<?php  
/*
*  Component  : Version
*  View       : Version Controller
*  Engine     : VersionEngine 
*  File       : edit.blade.php  
*  Controller : VersionEditController 
----------------------------------------------------------------------------- */
?> 
<div>
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3><?= __tr('Edit Version / Group') ?></h3>
    </div>
    <!-- /Modal Heading -->

    <!-- Add version dialog form -->
    <form class="ui form lw-form lw-ng-form" name="versionEditCtrl.[[versionEditCtrl.ngFormName]]" ng-submit="versionEditCtrl.submit()" novalidate >

        <!-- Modal Body -->
        <div class="modal-body">
            <!-- Slug -->
            <lw-form-field field-for="slug" label="<?= __tr('URL Slug') ?>">
                <input type="text" 
                    class="lw-form-field form-control"
                    ng-model="versionEditCtrl.versionData.slug" 
                    name="slug"
                    ng-required="true"
                />
            </lw-form-field>
            <!-- /Slug -->

        	<!-- Version -->
            <lw-form-field field-for="version" label="<?= __tr('Version / Group') ?>">
                <input type="text"
                class="lw-form-field form-control"
                ng-model="versionEditCtrl.versionData.version" name="version"
                ng-required="true"
                ng-maxlength="10"
            />
            </lw-form-field>
            <!-- /Version -->

        	<div class="form-row">
            	<div class="col">
            		<lw-form-field field-for="status" v-label="">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" id="markAsPrimary" ng-model="versionEditCtrl.versionData.is_primary" ng-true-value="1" ng-false-value="2" ng-change="versionEditCtrl.changeStatus(versionEditCtrl.versionData.is_primary)">
							<label class="custom-control-label" for="markAsPrimary">
								<?= __tr('Mark as primary') ?>
							</label>
						</div>
					</lw-form-field>
            	</div>
            	<div class="col">
					<!-- Status -->
                    <lw-form-field field-for="status" label="<?= __tr('Status ') ?>">

                        <div class="custom-control custom-switch custom-control-inline">
                            <input type="checkbox" ng-model="versionEditCtrl.versionData.status" 
                                ng-true-value="1" ng-false-value="2" class="custom-control-input" id="status" ng-disabled="versionEditCtrl.versionData.is_primary == 1">
                            <label class="custom-control-label" for="status">
                                <span ng-if="versionEditCtrl.versionData.status == 1">Active</span>
                                <span ng-if="versionEditCtrl.versionData.status == 2">Inactive</span>
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
            
            <button type="submit" class="lw-btn btn btn-primary" title="<?= __tr('Update') ?>"><?= __tr('Update') ?></button>
            
            <!-- Back  -->
            <button ng-click="versionEditCtrl.closeDialog()" title="<?= __tr('Close') ?>" class="lw-btn btn btn-default"><?= __tr('Close') ?></button>
            <!-- /Back  -->
        </div>
        <!-- /action -->
    </form>
</div>