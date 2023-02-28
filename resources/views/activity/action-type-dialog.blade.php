<?php 
/*
*  Component  : Activity
*  View       : ActivityLog Controller
*  Engine     : ActivityEngine  
*  File       : action-type-dialog.blade.php  
*  Controller : ActionTypeController as actionTypeCtrl
----------------------------------------------------------------------------- */
?>
<!-- Modal Heading -->
<div class="modal-header">
    <h3 class="modal-title"><?= __tr('Action Details') ?></h3>
</div>
<!-- /Modal Heading -->
<div class="modal-body">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">	
				
				<!-- company details -->
				<div class="panel-body">
					<div>
						<strong><?= __tr('Id') ?> :</strong>
			            <span class="text-primary" ng-bind="actionTypeCtrl.actionTypeData.entityID"></span>
			        </div>
					<hr>
 			        <div>
			        	<strong><?= __tr('Title') ?></strong>
			           	<div  ng-bind="actionTypeCtrl.actionTypeData.itemTitle"></div>
			        </div>
					<hr>
			        <div ng-show="actionTypeCtrl.actionTypeData.ProjectTitle">
			        	<strong><?= __tr('Project') ?></strong>
						<div ng-bind="actionTypeCtrl.actionTypeData.ProjectTitle"></div>
			        </div>
					<hr ng-show="actionTypeCtrl.actionTypeData.ProjectTitle">
					<div>
			            <h5 class="list-group-item-heading"><?= __tr('Activity :') ?></h5>
			            <address ng-bind-html ="actionTypeCtrl.actionTypeData.activity"></address>
			        </div>
				</div>
				 
			</div>
		</div>
	</div>
</div>

 <!-- Modal footer -->
<div class="modal-footer">
	<button type="button" title="Close" class="lw-btn btn btn-default" ng-click="actionTypeCtrl.closeDialog()"> Close </button>
</div>
<!-- /Modal footer -->