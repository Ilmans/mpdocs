<?php 
/*
*  Component  : Language
*  View       : Language Controller
*  Engine     : LanguageEngine
*  File       : edit-dialog.blade.php  
*  Controller : LanguageEditController
----------------------------------------------------------------------------- */
?> 
<div>
    <!-- Loading (remove the following to stop the loading)-->
    <div class="overlay" ng-show="languageEditCtrl.showLoader">
       <div class="loader"></div>
    </div>
    <!-- end loading -->
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3><?= __tr('Edit Language') ?></h3>
    </div>
    <!-- /Modal Heading -->

    <!-- Add Language dialog form -->
    <form class="ui form lw-form lw-ng-form" name="languageEditCtrl.[[languageEditCtrl.ngFormName]]" ng-submit="languageEditCtrl.submit()" novalidate >

        <!-- Modal Body -->
        <div class="modal-body">			
        	<div class="form-row">
        		<div class="col-lg-12">
		            <!-- Name -->
		            <lw-form-field field-for="name" label="<?= __tr('Name') ?>">
		                <input type="text" 
		                    class="lw-form-field form-control" 
		                    ng-model="languageEditCtrl.languageData.name" name="name"   
		                ng-required="true" />
		            </lw-form-field>
		            <!-- /Name -->
		        </div>
		    </div>

		    <div class="form-row">
        		<div class="col-lg-6">
		            <!-- /Is RTL -->
		            <lw-form-field field-for="is_rtl" label="<?= __tr('Is RTL') ?>">
						<div class="custom-control custom-switch">
							<input type="checkbox" ng-model="languageEditCtrl.languageData.is_rtl" 
								ng-true-value="1" ng-false-value="2" class="custom-control-input" id="is_rtl">
							<label class="custom-control-label" for="is_rtl">
								<span ng-if="languageEditCtrl.languageData.is_rtl == 1">Yes</span>
								<span ng-if="languageEditCtrl.languageData.is_rtl == 2">No</span>
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
            <button type="submit" class="lw-btn btn btn-primary" title="<?= __tr('Update') ?>"><?= __tr('Update') ?></button>
            
            <button type="button" title="<?= __tr('Cancel') ?>" class="lw-btn btn btn-default" ng-click="languageEditCtrl.closeDialog()"><?= __tr('Cancel') ?></button>
        </div>
        <!-- /Modal footer -->
    </form>
</div>