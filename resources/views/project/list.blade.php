<?php 
/*
*  Component  : Project
*  View       : Project Controller
*  Engine     : ProjectEngine
*  File       : project.list.blade.php
*  Controller : ProjectListController
----------------------------------------------------------------------------- */
?>
<div class="">

    <div class="lw-section-heading-block">
    
        <!-- main heading -->
        <h3 class="lw-section-heading">
            <div class="lw-heading">
                <?= __tr('Manage Projects') ?>
            </div>
        </h3>

     	<!-- button -->
        <div class="lw-section-right-content">
            <button title="<?= __tr('Add New Project') ?>" ng-if="canAccess('manage.project.write.create')" class="btn btn-sm btn-primary float-right" ng-click="projectListCtrl.addProject()"> <i class="fa fa-plus"></i> <?= __tr('Add New Project') ?>
            </button>
        </div>
        <!--/ button -->

    </div>
    <!-- /main heading -->

    <div class="shadow border p-4">

        <table class="table table-striped table-bordered" id="lwprojectList" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?= __tr('Name') ?></th>
                    <th><?= __tr('Created At') ?></th>
                    <th><?= __tr('Updated At') ?></th>
                    <th><?= __tr('Status') ?></th>
                    <th><?= __tr('Type') ?></th>
                    <th><?= __tr('Action') ?></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div ui-view></div>

    </div>

    <!-- action template -->
    <script type="text/_template" id="projectActionColumnTemplate">
    <!-- button -->
    <div class="lw-section-right-content">
    	<% if(__tData.canEditProject) { %>
        	<button title="<?= __tr('Edit Project') ?>" class="lw-btn btn btn-sm btn-default" ng-click="projectListCtrl.editProject('<%- __tData._uid %>')"><i class="far fa-edit"></i> Edit</button>
        <% } %>

        <% if(__tData.canViewProject) { %>
            <button class="btn btn-sm btn-lite" title="<?= __tr('Embed') ?>" ng-click="projectListCtrl.showEmbedScriptDialog('<%- __tData._uid %>')"><span class="fas fa-code"></span> Embed </button>
        <% } %>

    	<% if(__tData.canDeleteProject) { %>
    		<button class="btn btn-danger btn-xs" title="<?= __tr('Delete') ?>" ng-click="projectListCtrl.delete('<%- __tData._uid %>', '<%- __tData.name %>')"><i class="far fa-trash-alt"></i> <?= __tr('Delete') ?></button>
    	<% } %>

        <a href class="btn btn-default" ng-click="projectListCtrl.showProjectDetails('<%- __tData._uid %>')"> <i class="fas fa-eye"></i> Details </a>
    </div>
    <!--/ button -->
    </script>
    <!-- /action template -->

    <script type="text/_template" id="projectDetailsTemplate">
		<% if(__tData.canManageProjectVersions) { %>
			<a ui-sref="project_versions({ 'projectIdOrUid' : '<%- __tData._uid %>' })"><%- __tData.name %></a>
		<% } else { %>
			<%- __tData.name %>
		<% } %>
        <a href="<%- __tData.externalDetailUrl %>" target="_blank"><i class="fas fa-external-link-alt"></i></a>
        
    </script>

    <script type="text/_template" id="projectCreatedAtTemplate">
        <%= __tData.f_created_at %>
    </script>

    <input type="hidden" id="lwProjectDeleteConfirmTextMsg" data-message="<?= __tr( 'You want to delete <strong> __name__ </strong> project.<br> <strong>All the related information will be deleted. </strong>', [ '__name__' => '[[ projectListCtrl.projectName ]]' ]) ?>" data-delete-button-text="<?= __tr('Yes, delete it') ?>", success-msg="<?= __tr('Project deleted successfully.') ?>">
</div>