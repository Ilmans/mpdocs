<?php 
/*
*  Component  : Article
*  View       : Article Controller
*  Engine     : ArticleEngine  
*  File       : article-content-details-dialog.blade.php
*  Controller : ArticleContentDetailsController
----------------------------------------------------------------------------- */
?>
<div>
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3><?= __tr('Article Content Details') ?></h3>
    </div>
    <!-- /Modal Heading -->

    <!-- Modal Body -->
    <div class="modal-body">
​​​		<ul class="list-group list-group-flush">
			<li class="list-group-item">
				<strong>Content Title</strong> : 
				<span ng-bind="ArticleContentDetailsCtrl.article_content.title"></span>
			</li>
			<li class="list-group-item">
				<strong>Created At</strong> : 
				<span ng-bind="ArticleContentDetailsCtrl.article_content.created_at"></span>
			</li>
			<li class="list-group-item">
				<strong>Updated At</strong> : 
				<span ng-bind="ArticleContentDetailsCtrl.article_content.updated_at"></span>
			</li>
			<li class="list-group-item">
				<strong>Language</strong> : 
				<span ng-bind="ArticleContentDetailsCtrl.article_content.language_title"></span>
			</li>
			<li class="list-group-item">
				<strong>Description</strong> : 
				<p class="text-justify" ng-bind-html="ArticleContentDetailsCtrl.article_content.description"></p>
			</li>
		</ul>
    </div>
    <!-- /Modal Body -->

    <!-- Modal footer -->
    <div class="modal-footer">
        <button type="button" ng-click="ArticleContentDetailsCtrl.closeDialog()" class="lw-btn btn btn-default" title="<?= __tr('Close') ?>"><?= __tr('Close') ?></button>
    </div>
    <!-- /Modal footer -->
</div>