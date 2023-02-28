<?php 
/*
*  Component  : Project
*  View       : Project Controller
*  Engine     : ProjectEngine  
*  File       : details-dialog.blade.php  
*  Controller : ProjectDetailsController as projectDetailsCtrl
----------------------------------------------------------------------------- */
?>
<div>
    
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3><?= __tr('Project Details') ?></h3>
    </div>
    <!-- /Modal Heading -->

        <!-- Modal Body -->
        <div class="modal-body">
        	<div class="table-responsive">
				<table class="table">
					<tbody>
						<tr>
							<td>Project Name: </td>
							<td ng-bind="projectDetailsCtrl.projectData.name"></td>
						</tr>
						<tr>
							<td>Added On: </td>
							<td ng-bind="projectDetailsCtrl.projectData.added_on"></td>
						</tr>
						<tr>
							<td>Status: </td>
							<td ng-bind="projectDetailsCtrl.projectData.status"></td>
						</tr>
						<tr>
							<td>Type: </td>
							<td ng-bind="projectDetailsCtrl.projectData.type"></td>
						</tr>
						<tr ng-if="projectDetailsCtrl.projectData.short_description">
							<td>Description: </td>
							<td ng-bind="projectDetailsCtrl.projectData.short_description"></td>
						</tr>
						<tr>
							<td>Languages: </td>
							<td>
								<span class="badge badge-light border mr-2" ng-repeat="language in projectDetailsCtrl.projectData.languages">[[language.name]]</span>
							</td>
						</tr>
					</tbody>
				</table>
        	</div>
        </div>
        <!-- /Modal Body -->

        <!-- Modal footer -->
        <div class="modal-footer">
            <button type="button" title="<?= __tr('Cancel') ?>" class="lw-btn btn btn-default" ng-click="projectDetailsCtrl.closeDialog()"><?= __tr('Cancel') ?></button>
        </div>
</div>