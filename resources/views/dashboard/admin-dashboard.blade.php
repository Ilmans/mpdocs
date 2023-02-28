<?php
    /*
    *  Component  : Dashboard
    *  View       : Admin Dashboard  
    *  Engine     : DashboardEngine  
    *  File       : admin-dashboard.blade.php  
    *  Controller : AdminDashboardController  as AdminDashboardCtrl
    ----------------------------------------------------------------------------- */ 
?>
<div>
    <!-- main heading -->
    <div class="lw-section-heading-block">
        <h3 class="lw-section-heading">
            <div class="lw-heading">
				<i class="fa fa-tachometer"></i> <?= __tr('Dashboard') ?>
			</div>
        </h3>
    </div>
    <div class="row">
    	<div class="col-lg-4 mb-4" ng-if="canAccess('manage.project.read.list')">
    		<div class="card">
    			<div class="card-body text-center">
    				<h1 class="text-warning" ng-bind="AdminDashboardCtrl.dashboardData.projectsCount"></h1>
    				<h5>Active Projects</h5>
    			</div>
    		</div>
    	</div>
    </div>
</div>