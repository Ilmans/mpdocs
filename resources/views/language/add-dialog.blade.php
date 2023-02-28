<?php 
/*
*  Component  : Language
*  View       : Language Controller
*  Engine     : LanguageEngine  
*  File       : add-dialog.blade.php  
*  Controller : LanguageAddController
----------------------------------------------------------------------------- */
?>
<div>
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3><?= __tr('Add Language') ?></h3>
    </div>
    <!-- /Modal Heading -->

    <!-- Add Language dialog form -->
    <form class="ui form lw-form lw-ng-form" name="languageAddCtrl.[[languageAddCtrl.ngFormName]]" ng-submit="languageAddCtrl.submit()" novalidate >

        <!-- Modal Body -->
        <div class="modal-body">

        	<div class="alert alert-primary small">
	 			<strong>Note: </strong> For Language Code, click
	 			<a target="__blank" href="https://en.wikipedia.org/wiki/List_of_ISO_639-2_codes">here</a>
			</div>

        	<div class="form-row">
        		<div class="col-lg-6">
        			<!-- Name -->
		            <lw-form-field field-for="name" label="<?= __tr('Name') ?>">
		                <input type="text" 
		                    class="lw-form-field form-control" 
		                    ng-model="languageAddCtrl.languageData.name" name="name"
		                    ng-required="true"/>
		            </lw-form-field>
		            <!-- /Name -->
        		</div>
        		<div class="col-lg-6">	
		            <!-- Code -->
		            <lw-form-field field-for="code" label="<?= __tr('Code') ?>">
		                <input type="text" 
		                    class="lw-form-field form-control" 
		                    ng-model="languageAddCtrl.languageData.code" name="code"
		                    ng-required="true"/>
		            </lw-form-field>
		            <!-- /Code -->
        		</div>
        	</div>

        	<div class="form-row">
        		<div class="col-lg-6">
		            <!-- /Is RTL -->
		            <lw-form-field field-for="is_rtl" label="<?= __tr('Is RTL') ?>">
						<div class="custom-control custom-switch">
							<input type="checkbox" ng-model="languageAddCtrl.languageData.is_rtl" 
								ng-true-value="1" ng-false-value="2" class="custom-control-input" id="is_rtl">
							<label class="custom-control-label" for="is_rtl">
								<span ng-if="languageAddCtrl.languageData.is_rtl == 1">Yes</span>
								<span ng-if="languageAddCtrl.languageData.is_rtl == 2">No</span>
							</label>
						</div>
		            </lw-form-field>
		            <!-- /Is RTL -->
				</div>
		    </div>

        </div>
        <!-- /Modal Body -->

        <!-- Modal footer -->
        <div class="modal-footer">
        
            <button type="submit" class="lw-btn btn btn-primary" title="<?= __tr('Add') ?>"><?= __tr('Add') ?></button>

            <button type="button" title="<?= __tr('Cancel') ?>" class="lw-btn btn btn-default" ng-click="languageAddCtrl.closeDialog()"><?= __tr('Cancel') ?></button>
        </div>
        <!-- /Modal footer -->
    </form>
</div>