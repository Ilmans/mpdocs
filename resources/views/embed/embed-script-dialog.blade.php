
<?php 
/*
*  Component  : Article
*  View       : Embed Script Dialog
*  Engine     : Article  
*  File       : embed-script-dialog.blade.php
*  Controller : EmbedScriptDialogController
----------------------------------------------------------------------------- */
?>
<div>
    <!-- Modal Heading -->
    <div class="modal-header">
        <h3><?= __tr('Include following scripts') ?></h3>
    </div>
    <!-- /Modal Heading -->

    <!-- Modal Body -->
    <div class="modal-body small" id="load_iframe_script">
    	<div class='form-group' id="loadableScript">
    		<button type="button" class="btn btn-primary btn-sm float-right copy-to-clipboard">Copy</button>
    		<textarea class='form-control' rows='8' disabled><?= embedIframe() ?>[[ EmbedScriptDialogCtrl.loadArticleScript() ]]
            </textarea>
    	</div>
        <div class="alert alert-info">
            <?= __tr('If you want the custom button to open embedded doc then your button should have the id of "docsyardButton" and add this button above script tag.') ?>
        </div>
    </div>
    <!-- /Modal Body -->

    <!-- Modal footer -->
    <div class="modal-footer">
        <button type="button" title="<?= __tr('Cancel') ?>" class="lw-btn btn btn-default" ng-click="EmbedScriptDialogCtrl.closeDialog()"><?= __tr('Cancel') ?></button>
    </div>
    <!-- /Modal footer -->
</div>


<script type="text/javascript">
	$('#load_iframe_script #loadableScript .copy-to-clipboard').on('click', function() {
		$(this).siblings('textarea').removeAttr('disabled');
		$(this).siblings('textarea').select();
		document.execCommand("copy");
		$(this).siblings('textarea').attr('disabled', 'disabled');
	});
</script>
