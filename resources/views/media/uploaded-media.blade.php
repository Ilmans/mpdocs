<?php
/*
*  Component  : User
*  View       : User Profile Edit 
*  Engine     : UserEngine  
*  File       : user-profile-edit.blade.php  
*  Controller : uploadedFileDialogController 
----------------------------------------------------------------------------- */ 
?>
<div ng-controller='uploadedFileDialogController as uploadedFileDialogCtrl'>
    
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3 class="modal-title"><?= __tr('Uploaded Files') ?></h3>
    </div>
    <!-- /Modal Heading -->

    <!-- Modal Body -->
    <div class="modal-body">

        <!-- Loading (remove the following to stop the loading)-->
        <div ng-show="uploadedFileDialogCtrl.showLoader">
            <div class="loader"></div>
        </div>
        <!-- end loading -->
		
        <div class="table-responsive" ng-hide="uploadedFileDialogCtrl.showLoader">
            <table class="table table-bordred">
                <thead>
                    <th ng-if="uploadedFileDialogCtrl.uploadedFiles.length > 0">
                        <lw-select-all-checkbox 
                            checkboxes="uploadedFileDialogCtrl.uploadedFiles" 
                            all-selected="uploadedFileDialogCtrl.allSelectedItems"
                            id="selectedStatus"
                            all-clear="uploadedFileDialogCtrl.noSelectedItems"
                            >
                        </lw-select-all-checkbox>
                    </th>
                    <th><?= __tr('Thumbnail')?></th>
                    <th><?= __tr('Name') ?></th>
                    <th><?= __tr('Action') ?></th>
                </thead>
                <tbody >
                    <tr ng-if="uploadedFileDialogCtrl.uploadedFiles.length > 0" ng-repeat="uploadedFile in uploadedFileDialogCtrl.uploadedFiles">
                        <td>
                            <input  type="checkbox" id="lw_[[uploadedFile.name]]" ng-model="uploadedFile.isSelected"/><label for="lw_[[uploadedFile.name]]"></label>
                        </td>
                        <td>
                            <img class="image-responsive thumbnail" height="50" ng-src="[[uploadedFile.path]]">
                        </td>
                        <td ng-bind="uploadedFile.name"></td>
                        <td>
                            <button class="btn btn-danger btn-sm" ng-click="uploadedFileDialogCtrl.delete(uploadedFile.name)">
                                <i class="fa fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    <tr ng-if="uploadedFileDialogCtrl.uploadedFiles.length > 0" >
                        <td colspan="4">
                            <button class="btn btn-danger btn-sm" ng-click="uploadedFileDialogCtrl.deleteMultipleFiles()">
                                <i class="fa fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    <tr ng-if="uploadedFileDialogCtrl.uploadedFiles.length == 0">
                        <td colspan="4"><?= __tr('There are no uploaded files.') ?></td>
                    </tr>
                </tbody>
            </table>
    </div>
    </div>
    <!-- /Modal Body -->

    <!-- Modal footer -->
    <div class="modal-footer">
        <button class="lw-btn btn btn-default" title="<?= __tr( 'Close' ) ?>" ng-click="uploadedFileDialogCtrl.closeDialog()"><?= __tr( 'Close' ) ?></button>
    </div>
    <!-- /Modal footer -->
</div>