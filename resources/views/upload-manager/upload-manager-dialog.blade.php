<div ng-controller="UploadManagerDialogController as uploadDialogCtrl" class="lw-dialog">
	<!-- main heading -->
    <div class="lw-section-heading-block">
        <h3 class=""> <?= __tr("Upload Manager") ?></h3>
    </div>
    <!-- /main heading -->
    <div class="alert alert-info"><strong><?= __tr(" Note :") ?> </strong><?= __tr("Please double click on image/document for selection.") ?></div>
    <div class="lw-upload-manager-files-container table-responsive" ng-if="uploadDialogCtrl.files.length > 0">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><?=  __tr('Preview')  ?></th>
                    <th><?=  __tr('File Name')  ?></th>
                    <th><?=  __tr('Action')  ?></th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="file in uploadDialogCtrl.files track by $index">
                    <td>
                        <img ng-if="file.is_image" title="Select Image" ng-dblclick="uploadDialogCtrl.selectImage(file.url)" ng-src="[[file.url]]" alt="[[file.name]]"/>
                        <a ng-if="!file.is_image" title="Select Document" href="" ng-dblclick="uploadDialogCtrl.selectDocument(file.url, file.name)"><span class="fa fa-trash-o fa-lg"></span></a>
                    </td>
                    <td class="longtext">[[ file.name ]]</td>
                    <td>
                        <a href="" ng-click="uploadDialogCtrl.delete(file.name)" title="<?=  __tr('Delete')  ?>"><i class="fa fa-trash-alt fa-1x"></i></a>
                    </td>
                </tr>
            </tbody>

        </table>
    </div>

    <!-- File Uploader -->
    <div class="form-group">
        <label class="control-label"><?=  __tr("Uploads")  ?></label>
        <div>
        	<span class="btn btn-primary btn-sm lw-btn-file">
            	<i class="fa fa-upload"></i> 
						<?=   __tr('Browse')   ?>
                <input type="file" nv-file-select="" uploader="uploadDialogCtrl.uploader" multiple/>
            </span>&nbsp;
        </div>
    </div>
    <!-- /File Uploader -->
</div>